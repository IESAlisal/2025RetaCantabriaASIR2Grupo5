<?php
// ============================================================================
// ARCHIVO: php/alumno/ver_mis_asignaturas.php
// DESCRIPCIÓN: Alumno ve sus asignaturas matriculadas
// ACCESO: Solo para ALUMNOS
// ============================================================================

// Iniciar sesión
session_start();

// Verificar que existe sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login/login.php");
    exit();
}

// Incluir funciones y constantes
require_once "../../funciones/funciones.php";
require_once "../constantes/constantes.php";

// Verificar que es ALUMNO
$rol = $_SESSION['rol'] ?? obtenerRolUsuario($_SESSION['usuario']);
if ($rol !== 'ALUMNO') {
    header("Location: ../panel/panel.php");
    exit();
}

// Obtener datos del alumno
$usuario_actual = $_SESSION['usuario'];
$conexion = getConexionPDO();

if (!$conexion) {
    die("Error de conexión a la base de datos");
}

// Obtener ID del alumno
$sql_alumno = "SELECT a.id_alumno 
               FROM alumno a 
               JOIN usuarios u ON a.id_usuario = u.id_usuario 
               JOIN login l ON u.id_usuario = l.id_usuario 
               WHERE l.usuario = :usuario";
$stmt_alumno = $conexion->prepare($sql_alumno);
$stmt_alumno->execute([':usuario' => $usuario_actual]);
$alumno = $stmt_alumno->fetch(PDO::FETCH_ASSOC);

if (!$alumno) {
    die("No se encontró información del alumno");
}

$id_alumno = $alumno['id_alumno'];

// Obtener asignaturas matriculadas
$sql_matriculas = "SELECT 
                    m.codigo_matricula,
                    a.nombre_asignatura,
                    a.codigo_asignatura,
                    a.horas_semanales,
                    m.fecha_matricula,
                    m.fecha_inicio_curso,
                    m.fecha_fin_curso,
                    m.calificacion,
                    m.estado,
                    CONCAT(u.nombre, ' ', u.apellido) as profesor_nombre,
                    u.correo as profesor_email
                   FROM matricula m
                   JOIN asignaturas a ON m.id_asignatura = a.id_asignatura
                   JOIN profesor p ON a.id_profesor = p.id_profesor
                   JOIN usuarios u ON p.id_usuario = u.id_usuario
                   WHERE m.id_alumno = :id_alumno
                   ORDER BY m.fecha_inicio_curso DESC, m.estado";
$stmt_matriculas = $conexion->prepare($sql_matriculas);
$stmt_matriculas->execute([':id_alumno' => $id_alumno]);
$matriculas = $stmt_matriculas;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Asignaturas - Academia de Pintura</title>
    <link rel="stylesheet" href="../../css/estilos_unificados.css">
</head>
<body>
    <div class="container">
        <!-- Enlace para volver -->
        <a href="../panel/panel.php" class="back-link">← Volver al Panel</a>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Mis Asignaturas Matriculadas</h2>
            </div>
            
            <?php if ($matriculas->rowCount() > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Asignatura</th>
                        <th>Código</th>
                        <th>Profesor</th>
                        <th>Periodo</th>
                        <th>Estado</th>
                        <th>Calificación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($matricula = $matriculas->fetch(PDO::FETCH_ASSOC)): 
                        $estado_clase = '';
                        switch($matricula['estado']) {
                            case 'Activa': $estado_clase = 'badge-activa'; break;
                            case 'Completada': $estado_clase = 'badge-completada'; break;
                            case 'Retirada': $estado_clase = 'badge-retirada'; break;
                        }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($matricula['nombre_asignatura']); ?></td>
                        <td><?php echo htmlspecialchars($matricula['codigo_asignatura']); ?></td>
                        <td><?php echo htmlspecialchars($matricula['profesor_nombre']); ?></td>
                        <td>
                            <?php 
                            echo date('d/m/Y', strtotime($matricula['fecha_inicio_curso'])) . ' - ' .
                                 date('d/m/Y', strtotime($matricula['fecha_fin_curso']));
                            ?>
                        </td>
                        <td>
                            <span class="badge <?php echo $estado_clase; ?>">
                                <?php echo htmlspecialchars($matricula['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!is_null($matricula['calificacion'])): ?>
                                <strong><?php echo htmlspecialchars($matricula['calificacion']); ?></strong>
                            <?php else: ?>
                                <em>Sin calificar</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-data">
                <p>No estás matriculado en ninguna asignatura.</p>
                <p>Contacta con tu profesor para solicitar matrícula.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>