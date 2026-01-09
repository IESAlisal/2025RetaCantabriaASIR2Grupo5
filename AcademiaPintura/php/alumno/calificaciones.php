<?php
require_once '../../funciones/funciones.php';
requireRole(['ALUMNO']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$id_usuario = $_SESSION['usuario'] ?? null;
$calificaciones = [];
if ($id_usuario) {
    try {
        $conexion = getConexionPDO();
        // Obtener id_alumno a partir de id_usuario
        $stmt = $conexion->prepare('SELECT id_alumno FROM alumno WHERE id_usuario = ?');
        $stmt->execute([$id_usuario]);
        $id_alumno = $stmt->fetchColumn();
        if ($id_alumno) {
            // Obtener calificaciones de asignaturas actuales (matriculado)
            $sql = 'SELECT a.nombre_asignatura, m.calificacion
                    FROM matricula m
                    JOIN asignaturas a ON m.id_asignatura = a.id_asignatura
                    WHERE m.id_alumno = ?';
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$id_alumno]);
            $calificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        $error = 'Error al obtener las calificaciones: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calificaciones - Alumno</title>
    <link rel="stylesheet" href="../../css/estilos_unificados.css">
</head>
<body>
<?php include '../panel/panel.php'; ?>
<div class="container">
    <div class="welcome">
        <h2>Ver calificaciones</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (empty($calificaciones)): ?>
            <p>No tienes calificaciones registradas actualmente.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Asignatura</th>
                    <th>Calificación</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($calificaciones as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['nombre_asignatura']); ?></td>
                    <td><?php echo $item['calificacion'] !== null ? htmlspecialchars($item['calificacion']) : '-'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>