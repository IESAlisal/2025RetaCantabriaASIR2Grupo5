<?php
require_once '../../funciones/funciones.php';
requireRole(['ALUMNO']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$id_usuario = $_SESSION['usuario'] ?? null;

$cursos = [];
if ($id_usuario) {
    try {
        $conexion = getConexionPDO();
        // Obtener id_alumno a partir de id_usuario
        $stmt = $conexion->prepare('SELECT id_alumno FROM alumno WHERE id_usuario = ?');
        $stmt->execute([$id_usuario]);
        $id_alumno = $stmt->fetchColumn();
        if ($id_alumno) {
            // Obtener asignaturas en las que está matriculado actualmente
            $sql = 'SELECT a.nombre_asignatura, m.fecha_matricula, m.calificacion
                    FROM matricula m
                    JOIN asignaturas a ON m.id_asignatura = a.id_asignatura
                    WHERE m.id_alumno = ?';
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$id_alumno]);
            $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        $error = 'Error al obtener los cursos: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis cursos - Alumno</title>
    <link rel="stylesheet" href="../../css/estilos_unificados.css">
</head>
<body>
<?php include '../panel/panel.php'; ?>
<div class="container">
    <div class="welcome">
        <h2>Mis cursos</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (empty($cursos)): ?>
            <p>No estás matriculado en ningún curso actualmente.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>º
                    <th>Asignatura</th>
                    <th>Fecha de matrícula</th>
                    <th>Calificación (si hay)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cursos as $curso): ?>
                <tr>
                    <td><?php echo htmlspecialchars($curso['nombre_asignatura']); ?></td>
                    <td><?php echo htmlspecialchars($curso['fecha_matricula']); ?></td>
                    <td><?php echo $curso['calificacion'] !== null ? htmlspecialchars($curso['calificacion']) : '-'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>