<?php
require_once '../../funciones/funciones.php';
requireRole(['PROFESOR']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_usuario = $_SESSION['usuario'] ?? null;
$conexion = getConexionPDO();
$mensaje = '';
$error = '';
$asignaturas = [];
$alumnos = [];
$asignatura_seleccionada = $_POST['asignatura_id'] ?? null;

if ($id_usuario && $conexion) {
    // Buscar el id_usuario real si en sesión está el nombre de usuario (tabla login)
    $stmt = $conexion->prepare('SELECT id_usuario FROM login WHERE usuario = ?');
    $stmt->execute([$id_usuario]);
    $id_usuario_real = $stmt->fetchColumn();
    if ($id_usuario_real) {
        $id_usuario = $id_usuario_real;
    }
    // Obtener id_profesor
    $stmt = $conexion->prepare('SELECT id_profesor FROM profesor WHERE id_usuario = ?');
    $stmt->execute([$id_usuario]);
    $id_profesor = $stmt->fetchColumn();
    if ($id_profesor) {
        // Obtener asignaturas que imparte
        $stmt = $conexion->prepare('SELECT id_asignatura, nombre_asignatura FROM asignaturas WHERE id_profesor = ?');
        $stmt->execute([$id_profesor]);
        $asignaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Si seleccionó una asignatura, obtener alumnos matriculados
        if ($asignatura_seleccionada) {
            // Si se envió calificación, actualizarla
            if (isset($_POST['guardar']) && isset($_POST['calificacion']) && is_array($_POST['calificacion'])) {
                foreach ($_POST['calificacion'] as $id_matricula => $nota) {
                    $nota = is_numeric($nota) ? floatval($nota) : null;
                    if ($nota !== null && $nota >= 0 && $nota <= 10) {
                        $stmt = $conexion->prepare('UPDATE matricula SET calificacion = ? WHERE id_matricula = ?');
                        $stmt->execute([$nota, $id_matricula]);
                    }
                }
                $mensaje = 'Calificaciones actualizadas correctamente.';
            }
            // Obtener alumnos matriculados en la asignatura
            $sql = 'SELECT m.id_matricula, u.nombre, u.apellido, m.calificacion
                    FROM matricula m
                    JOIN alumno a ON m.id_alumno = a.id_alumno
                    JOIN usuarios u ON a.id_usuario = u.id_usuario
                    WHERE m.id_asignatura = ?';
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$asignatura_seleccionada]);
            $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Introducir calificaciones - Profesor</title>
    <link rel="stylesheet" href="../../css/estilos_unificados.css">
</head>
<body>
<?php include '../panel/panel.php'; ?>
<div class="container">
    <div class="welcome">
        <h2>Introducir calificaciones</h2>
        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post">
            <label for="asignatura_id"><b>Selecciona una asignatura:</b></label>
            <select name="asignatura_id" id="asignatura_id" required onchange="this.form.submit()">
                <option value="">-- Selecciona --</option>
                <?php foreach ($asignaturas as $asig): ?>
                    <option value="<?php echo $asig['id_asignatura']; ?>" <?php if ($asignatura_seleccionada == $asig['id_asignatura']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($asig['nombre_asignatura']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <?php if ($asignatura_seleccionada && !empty($alumnos)): ?>
            <form method="post">
                <input type="hidden" name="asignatura_id" value="<?php echo $asignatura_seleccionada; ?>">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Calificación</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($alumnos as $al): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($al['nombre'] . ' ' . $al['apellido']); ?></td>
                            <td>
                                <input type="number" name="calificacion[<?php echo $al['id_matricula']; ?>]" min="0" max="10" step="0.01" value="<?php echo htmlspecialchars($al['calificacion']); ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="guardar" class="btn btn-primary">Guardar calificaciones</button>
            </form>
        <?php elseif ($asignatura_seleccionada): ?>
            <p>No hay alumnos matriculados en esta asignatura.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>