<?php
require_once '../../funciones/funciones.php';
requireRole(['ALUMNO']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$id_usuario = $_SESSION['usuario'] ?? null;
$id_alumno = null;
$mensaje = '';
$error = '';

try {
    $conexion = getConexionPDO();
    // Si lo que hay en sesión es el nombre de usuario, buscar el id_usuario real
    if ($id_usuario) {
        $stmt = $conexion->prepare('SELECT id_usuario FROM usuarios WHERE usuario = ? OR correo = ? OR id_usuario = ?');
        $stmt->execute([$id_usuario, $id_usuario, $id_usuario]);
        $id_usuario_real = $stmt->fetchColumn();
        if ($id_usuario_real) {
            $id_usuario = $id_usuario_real;
        }
    }
    // Comprobación e inserción automática en tabla alumno
    if ($id_usuario) {
        $stmt = $conexion->prepare('SELECT id_alumno FROM alumno WHERE id_usuario = ?');
        $stmt->execute([$id_usuario]);
        $id_alumno = $stmt->fetchColumn();
        if (!$id_alumno) {
            // Insertar registro en alumno si no existe
            $stmt = $conexion->prepare('INSERT INTO alumno (id_usuario, fecha_ingreso, beca) VALUES (?, CURDATE(), "No")');
            $stmt->execute([$id_usuario]);
            // Volver a obtener el id_alumno
            $stmt = $conexion->prepare('SELECT id_alumno FROM alumno WHERE id_usuario = ?');
            $stmt->execute([$id_usuario]);
            $id_alumno = $stmt->fetchColumn();
        }
    }
} catch (Exception $e) {
    $error = 'Error de conexión a la base de datos: ' . $e->getMessage();
}

if ($id_usuario) {
    try {
        // Procesar inscripción
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['inscribir']) && isset($_POST['asignatura_id'])) {
                $id_asignatura = $_POST['asignatura_id'];
                // Comprobar si ya está matriculado
                $stmt = $conexion->prepare('SELECT COUNT(*) FROM matricula WHERE id_alumno = ? AND id_asignatura = ?');
                $stmt->execute([$id_alumno, $id_asignatura]);
                if ($stmt->fetchColumn() == 0) {
                    // Insertar matrícula con fechas obligatorias
                    $sql = 'INSERT INTO matricula (id_alumno, id_asignatura, fecha_matricula, fecha_inicio_curso, fecha_fin_curso) VALUES (?, ?, CURDATE(), CURDATE(), CURDATE())';
                    $stmt = $conexion->prepare($sql);
                    $stmt->execute([$id_alumno, $id_asignatura]);
                    $mensaje = 'Inscripción realizada correctamente.';
                } else {
                    $error = 'Ya estás matriculado en esa asignatura.';
                }
            }
            // Procesar anulación
            if (isset($_POST['anular']) && isset($_POST['asignatura_id'])) {
                $id_asignatura = $_POST['asignatura_id'];
                $sql = 'DELETE FROM matricula WHERE id_alumno = ? AND id_asignatura = ?';
                $stmt = $conexion->prepare($sql);
                $stmt->execute([$id_alumno, $id_asignatura]);
                $mensaje = 'Matrícula anulada correctamente.';
            }
        }
        // Obtener asignaturas disponibles (no matriculadas y activas)
        $sql = 'SELECT id_asignatura, nombre_asignatura FROM asignaturas WHERE estado = "Activa" AND id_asignatura NOT IN (SELECT id_asignatura FROM matricula WHERE id_alumno = ?)';

        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id_alumno]);
        $asignaturas_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Obtener asignaturas matriculadas
        $sql = 'SELECT a.id_asignatura, a.nombre_asignatura FROM matricula m JOIN asignaturas a ON m.id_asignatura = a.id_asignatura WHERE m.id_alumno = ?';
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id_alumno]);
        $asignaturas_matriculadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $error = 'Error al gestionar la matrícula: ' . $e->getMessage();
        $asignaturas_disponibles = [];
        $asignaturas_matriculadas = [];
    }
} else {
    $error = 'No se ha encontrado el usuario en sesión.';
    $asignaturas_disponibles = [];
    $asignaturas_matriculadas = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Matrícula - Alumno</title>
    <link rel="stylesheet" href="../../css/estilos_unificados.css">
</head>
<body>
<?php include '../panel/panel.php'; ?>
<div class="container">
    <div class="welcome">
        <h2>Inscribirme / Anular matrícula</h2>
        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <h3>Asignaturas disponibles para inscribirse</h3>
        <?php if (isset($error) && $error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (empty($asignaturas_disponibles) && empty($error)): ?>
            <?php
            // Comprobar si hay asignaturas activas en la base de datos
            $sql = 'SELECT COUNT(*) FROM asignaturas WHERE estado = "Activa"';
            $stmt = $conexion->query($sql);
            $total_activas = $stmt->fetchColumn();
            ?>
            <?php if ($total_activas == 0): ?>
                <p>No hay asignaturas activas en la base de datos.</p>
            <?php else: ?>
                <p>No hay asignaturas disponibles para inscribirse.</p>
            <?php endif; ?>
        <?php elseif (!empty($asignaturas_disponibles)): ?>
            <form method="post">
                <select name="asignatura_id" required>
                    <?php foreach ($asignaturas_disponibles as $asig): ?>
                        <option value="<?php echo $asig['id_asignatura']; ?>"><?php echo htmlspecialchars($asig['nombre_asignatura']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="inscribir" class="btn btn-primary">Inscribirme</button>
            </form>
        <?php endif; ?>
        <h3>Asignaturas en las que estás matriculado</h3>
        <?php if (empty($asignaturas_matriculadas)): ?>
            <p>No estás matriculado en ninguna asignatura.</p>
        <?php else: ?>
            <form method="post">
                <select name="asignatura_id" required>
                    <?php foreach ($asignaturas_matriculadas as $asig): ?>
                        <option value="<?php echo $asig['id_asignatura']; ?>"><?php echo htmlspecialchars($asig['nombre_asignatura']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="anular" class="btn btn-danger">Anular matrícula</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>