<?php
// ============================================================================
// ARCHIVO: php/profesor/alumnos_matriculados.php
// DESCRIPCIÓN: Muestra los alumnos matriculados en las asignaturas del profesor
// ACCESO: Solo para PROFESORES
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

// Verificar que es PROFESOR
$rol = $_SESSION['rol'] ?? obtenerRolUsuario($_SESSION['usuario']);
if ($rol !== 'PROFESOR') {
    header("Location: ../panel/panel.php");
    exit();
}

// Obtener ID del profesor actual
$usuario_actual = $_SESSION['usuario'];
$conexion = getConexionPDO();

if (!$conexion) {
    die("Error de conexión a la base de datos");
}

// Obtener ID del profesor
$sql_profesor = "SELECT p.id_profesor 
                 FROM profesor p 
                 JOIN usuarios u ON p.id_usuario = u.id_usuario 
                 JOIN login l ON u.id_usuario = l.id_usuario 
                 WHERE l.usuario = :usuario";
$stmt_profesor = $conexion->prepare($sql_profesor);
$stmt_profesor->execute([':usuario' => $usuario_actual]);
$profesor = $stmt_profesor->fetch(PDO::FETCH_ASSOC);

if (!$profesor) {
    die("No se encontró información del profesor");
}

$id_profesor = $profesor['id_profesor'];

// Obtener asignaturas del profesor con alumnos matriculados
$sql = "SELECT 
    a.id_asignatura,
    a.nombre_asignatura,
    a.codigo_asignatura,
    COUNT(DISTINCT m.id_alumno) as total_alumnos
FROM asignaturas a
LEFT JOIN matricula m ON a.id_asignatura = m.id_asignatura AND m.estado IN ('Activa', 'Pendiente')
WHERE a.id_profesor = :id_profesor
GROUP BY a.id_asignatura, a.nombre_asignatura, a.codigo_asignatura
ORDER BY a.nombre_asignatura";

$stmt = $conexion->prepare($sql);
$stmt->execute([':id_profesor' => $id_profesor]);
$asignaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumnos Matriculados - Academia de Pintura</title>
    <link rel="stylesheet" href="../../css/estilos_unificados.css">
    <style>
        .asignatura-card {
            margin-bottom: 20px;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            background: var(--bg-white);
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }
        
        .asignatura-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }
        
        .asignatura-header {
            background: var(--gradient-primary);
            color: white;
            padding: 18px 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .asignatura-header:hover {
            background: var(--primary-dark);
        }
        
        .asignatura-title {
            font-size: 18px;
            font-weight: 700;
        }
        
        .asignatura-codigo {
            font-size: 12px;
            opacity: 0.9;
            margin-top: 5px;
        }
        
        .alumno-count {
            background: rgba(255,255,255,0.25);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .alumnos-list {
            padding: 20px;
            display: none;
        }
        
        .alumnos-list.visible {
            display: block;
        }
        
        .alumno-item {
            padding: 16px;
            border-bottom: 1px solid var(--border);
            background: var(--bg-white);
            margin-bottom: 8px;
            border-radius: var(--radius-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .alumno-item:hover {
            background: var(--bg-light);
            transform: translateX(4px);
        }
        
        .alumno-item:last-child {
            border-bottom: none;
        }
        
        .alumno-info {
            flex: 1;
        }
        
        .alumno-nombre {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 6px;
        }
        
        .alumno-email {
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }
        
        .matricula-info {
            font-size: 12px;
            color: var(--text-light);
        }
        
        .estado-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }
        
        .estado-activa {
            background: linear-gradient(135deg, #d1fae5 0%, #ecfdf5 100%);
            color: #065f46;
        }
        
        .estado-pendiente {
            background: linear-gradient(135deg, #fef3c7 0%, #fffbeb 100%);
            color: #92400e;
        }
        
        .no-alumnos {
            padding: 40px 20px;
            text-align: center;
            color: var(--text-secondary);
            font-style: italic;
            background: var(--bg-light);
            border-radius: var(--radius-md);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Enlace para volver -->
        <a href="../panel/panel.php" class="back-link">← Volver al Panel</a>
        
        <h2>Alumnos Matriculados</h2>
        <p class="subtitle-info">Haz clic en una asignatura para ver los alumnos matriculados</p>
        
        <?php
        if (empty($asignaturas)) {
            echo '<p class="text-center empty-state">No tienes asignaturas registradas</p>';
        } else {
            foreach ($asignaturas as $asignatura):
                $id_asignatura = $asignatura['id_asignatura'];
                $nombre = $asignatura['nombre_asignatura'];
                $codigo = $asignatura['codigo_asignatura'];
                $total = $asignatura['total_alumnos'];
                
                // Obtener alumnos de esta asignatura
                $sql_alumnos = "SELECT 
                    al.id_alumno,
                    u.nombre,
                    u.apellido,
                    u.correo,
                    m.codigo_matricula,
                    m.fecha_matricula,
                    m.fecha_inicio_curso,
                    m.fecha_fin_curso,
                    m.estado,
                    m.id_matricula
                FROM matricula m
                JOIN alumno al ON m.id_alumno = al.id_alumno
                JOIN usuarios u ON al.id_usuario = u.id_usuario
                WHERE m.id_asignatura = :id_asignatura AND m.estado IN ('Activa', 'Pendiente')
                ORDER BY u.apellido, u.nombre";
                
                $stmt_alumnos = $conexion->prepare($sql_alumnos);
                $stmt_alumnos->execute([':id_asignatura' => $id_asignatura]);
                $alumnos = $stmt_alumnos->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <div class="asignatura-card">
            <div class="asignatura-header" onclick="toggleAlumnos(this)">
                <div>
                    <div class="asignatura-title"><?php echo htmlspecialchars($nombre); ?></div>
                    <div class="asignatura-codigo">Código: <?php echo htmlspecialchars($codigo); ?></div>
                </div>
                <div class="alumno-count"><?php echo $total; ?> alumno(s)</div>
            </div>
            
            <div class="alumnos-list">
                <?php
                if (empty($alumnos)) {
                    echo '<div class="no-alumnos">No hay alumnos matriculados en esta asignatura</div>';
                } else {
                    foreach ($alumnos as $alumno):
                ?>
                <div class="alumno-item">
                    <div class="alumno-info">
                        <div class="alumno-nombre">
                            <?php echo htmlspecialchars($alumno['apellido'] . ', ' . $alumno['nombre']); ?>
                        </div>
                        <div class="alumno-email">
                            <?php echo htmlspecialchars($alumno['correo']); ?>
                        </div>
                        <div class="matricula-info">
                            Matrícula: <?php echo htmlspecialchars($alumno['codigo_matricula']); ?> 
                            | Período: <?php echo date('d/m/Y', strtotime($alumno['fecha_inicio_curso'])); ?> - <?php echo date('d/m/Y', strtotime($alumno['fecha_fin_curso'])); ?>
                        </div>
                    </div>
                    <div class="estado-badge estado-<?php echo strtolower($alumno['estado']); ?>">
                        <?php echo htmlspecialchars($alumno['estado']); ?>
                    </div>
                </div>
                <?php
                    endforeach;
                }
                ?>
            </div>
        </div>
        
        <?php
            endforeach;
        }
        ?>
    </div>

    <script>
        function toggleAlumnos(header) {
            const list = header.nextElementSibling;
            list.classList.toggle('visible');
            header.style.borderBottomLeftRadius = list.classList.contains('visible') ? '0' : '8px';
            header.style.borderBottomRightRadius = list.classList.contains('visible') ? '0' : '8px';
        }
    </script>
</body>
</html>
