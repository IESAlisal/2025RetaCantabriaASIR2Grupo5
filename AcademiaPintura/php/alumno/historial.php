<?php
require_once '../../funciones/funciones.php';
requireRole(['ALUMNO']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$id_usuario = $_SESSION['usuario'] ?? null;
$historial = [];
if ($id_usuario) {
    try {
        $conexion = getConexionPDO();
        // Obtener id_alumno a partir de id_usuario
        $stmt = $conexion->prepare('SELECT id_alumno FROM alumno WHERE id_usuario = ?');
        $stmt->execute([$id_usuario]);
        $id_alumno = $stmt->fetchColumn();
        if ($id_alumno) {
            // Obtener historial académico (todas las matrículas del alumno)
            $sql = 'SELECT a.nombre_asignatura, m.fecha_matricula, m.calificacion, m.fecha_inicio_curso, m.fecha_fin_curso
                    FROM matricula m
                    JOIN asignaturas a ON m.id_asignatura = a.id_asignatura
                    WHERE m.id_alumno = ?
                    ORDER BY m.fecha_matricula DESC';
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$id_alumno]);
            $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        $error = 'Error al obtener el historial académico: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial académico - Alumno</title>
    <link rel="stylesheet" href="../../css/estilos_unificados.css">
</head>
<body>
<?php include '../panel/panel.php'; ?>
<div class="container">
    <div class="welcome">
        <h2>Historial académico</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (empty($historial)): ?>
            <p>No hay historial académico disponible.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Asignatura</th>
                    <th>Fecha de matrícula</th>
                    <th>Calificación</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historial as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['nombre_asignatura']); ?></td>
                    <td><?php echo htmlspecialchars($item['fecha_matricula']); ?></td>
                    <td><?php echo $item['calificacion'] !== null ? htmlspecialchars($item['calificacion']) : '-'; ?></td>
                    <td><?php echo htmlspecialchars($item['fecha_inicio_curso']); ?></td>
                    <td><?php echo htmlspecialchars($item['fecha_fin_curso']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
