<?php
require_once '../../funciones/funciones.php';
requireRole(['PROFESOR']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener el id_usuario real del profesor
$id_usuario = $_SESSION['usuario'] ?? null;
$conexion = getConexionPDO();
$asignaturas = [];
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
        $stmt = $conexion->prepare('SELECT nombre_asignatura, descripcion, estado FROM asignaturas WHERE id_profesor = ?');
        $stmt->execute([$id_profesor]);
        $asignaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis asignaturas - Profesor</title>
    <link rel="stylesheet" href="../../css/estilos_unificados.css">
</head>
<body>
<?php include '../panel/panel.php'; ?>
<div class="container">
    <div class="welcome">
        <h2>Mis asignaturas</h2>
        <?php if (empty($asignaturas)): ?>
            <p>No tienes asignaturas asignadas.</p>
        <?php else: ?>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($asignaturas as $asig): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($asig['nombre_asignatura']); ?></td>
                        <td><?php echo htmlspecialchars($asig['descripcion']); ?></td>
                        <td><?php echo htmlspecialchars($asig['estado']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>