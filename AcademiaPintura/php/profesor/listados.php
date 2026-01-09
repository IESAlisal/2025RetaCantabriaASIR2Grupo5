<?php
require_once '../../funciones/funciones.php';
requireRole(['PROFESOR']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_usuario = $_SESSION['usuario'] ?? null;
$conexion = getConexionPDO();
$grupos = [];

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
        $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de grupos - Profesor</title>
    <link rel="stylesheet" href="../../css/estilos_unificados.css">
</head>
<body>
<?php include '../panel/panel.php'; ?>
<div class="container">
    <div class="welcome">
        <h2>Listado de grupos (asignaturas)</h2>
        <?php if (empty($grupos)): ?>
            <p>No tienes grupos/asignaturas asignados.</p>
        <?php else: ?>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Nombre de la asignatura</th>
                        <th>Alumnos matriculados</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($grupos as $grupo): ?>
                    <?php
                    // Contar alumnos matriculados en cada asignatura
                    $stmt = $conexion->prepare('SELECT COUNT(*) FROM matricula WHERE id_asignatura = ?');
                    $stmt->execute([$grupo['id_asignatura']]);
                    $num_alumnos = $stmt->fetchColumn();
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($grupo['nombre_asignatura']); ?></td>
                        <td><?php echo $num_alumnos; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
