<?php
// ============================================================================
// ARCHIVO: php/profesor/matricular_alumno.php
// DESCRIPCIÓN: Formulario para que profesores matriculen alumnos
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

// Obtener ID del profesor - CORREGIDO
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

// Obtener alumnos disponibles - CORREGIDO
$sql_alumnos = "SELECT alu.id_alumno, u.nombre, u.apellido, u.correo 
                FROM alumno alu
                JOIN usuarios u ON alu.id_usuario = u.id_usuario 
                ORDER BY u.apellido, u.nombre";
$alumnos = $conexion->query($sql_alumnos);

// Obtener asignaturas del profesor
$sql_asignaturas = "SELECT id_asignatura, nombre_asignatura, codigo_asignatura 
                    FROM asignaturas 
                    WHERE id_profesor = :id_profesor 
                    AND estado = 'Activa'
                    ORDER BY nombre_asignatura";
$stmt_asignaturas = $conexion->prepare($sql_asignaturas);
$stmt_asignaturas->execute([':id_profesor' => $id_profesor]);
$asignaturas = $stmt_asignaturas;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matricular Alumno - Academia de Pintura</title>
    <link rel="stylesheet" href="../../css/estilos_unificados.css">
</head>
<body>
    <div class="container">
        <!-- Enlace para volver -->
        <a href="../panel/panel.php" class="back-link">← Volver al Panel</a>
        
        <h2>Matricular Alumno en Asignatura</h2>
        
        <?php
        // Mostrar mensajes de éxito/error desde sesión o GET
        $msg = null;
        $tipo = 'info';
        
        // Prioridad: primero sesión, luego GET
        if (isset($_SESSION['matricula_msg'])) {
            $msg = $_SESSION['matricula_msg'];
            $tipo = $_SESSION['matricula_tipo'] ?? 'info';
            unset($_SESSION['matricula_msg']);
            unset($_SESSION['matricula_tipo']);
        } elseif (isset($_GET['msg'])) {
            $msg = urldecode($_GET['msg']);
            $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'info';
        }
        
        if ($msg) {
            $clase = 'alert-box ';
            if ($tipo === 'success') {
                $clase .= 'alert-success';
                echo '<div class="' . $clase . '" id="alertBox">';
                echo nl2br(htmlspecialchars($msg));
                echo '</div>';
            } else {
                $clase .= 'alert-error';
                echo '<div class="' . $clase . '" id="alertBox">';
                echo '<strong>❌ Error:</strong> ' . htmlspecialchars($msg);
                echo '</div>';
            }
        }
        ?>
        
       
        <form method="POST" action="guardar_matricula.php" id="formMatricula" onsubmit="validarMatricula(event)">
            <div id="validationMessage" class="validation-hidden"></div>
            
            <div class="form-group">
                <label for="id_alumno">Seleccionar Alumno:</label>
                <select id="id_alumno" name="id_alumno" required>
                    <option value="">-- Seleccione un alumno --</option>
                    <?php 
                    if ($alumnos) {
                        while($alumno = $alumnos->fetch(PDO::FETCH_ASSOC)): 
                    ?>
                    <option value="<?php echo htmlspecialchars($alumno['id_alumno']); ?>">
                        <?php echo htmlspecialchars($alumno['apellido'] . ', ' . $alumno['nombre'] . ' (' . $alumno['correo'] . ')'); ?>
                    </option>
                    <?php 
                        endwhile;
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="id_asignatura">Seleccionar Asignatura:</label>
                <select id="id_asignatura" name="id_asignatura" required>
                    <option value="">-- Seleccione una asignatura --</option>
                    <?php 
                    if ($asignaturas) {
                        while($asignatura = $asignaturas->fetch(PDO::FETCH_ASSOC)): 
                    ?>
                    <option value="<?php echo htmlspecialchars($asignatura['id_asignatura']); ?>">
                        <?php echo htmlspecialchars($asignatura['nombre_asignatura'] . ' (' . $asignatura['codigo_asignatura'] . ')'); ?>
                    </option>
                    <?php 
                        endwhile;
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="fecha_inicio_curso">Fecha de Inicio del Curso:</label>
                <input type="date" id="fecha_inicio_curso" name="fecha_inicio_curso" required 
                       min="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="form-group">
                <label for="fecha_fin_curso">Fecha de Fin del Curso:</label>
                <input type="date" id="fecha_fin_curso" name="fecha_fin_curso" required>
            </div>
            
            <button type="submit" class="btn-primary">Matricular Alumno</button>
        </form>
    </div>

    <script>
        // Validación de fechas
        document.getElementById('fecha_inicio_curso').addEventListener('change', function() {
            var fechaInicio = this.value;
            var fechaFin = document.getElementById('fecha_fin_curso');
            fechaFin.min = fechaInicio;
            
            // Si la fecha fin es anterior a la nueva fecha inicio, limpiarla
            if (fechaFin.value && fechaFin.value < fechaInicio) {
                fechaFin.value = '';
            }
        });
        
        // Asegurar que el mensaje sea visible (scroll al top)
        var alertBox = document.getElementById('alertBox');
        if (alertBox) {
            alertBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        
        // Validación AJAX antes de enviar el formulario
        function validarMatricula(event) {
            event.preventDefault();
            
            const idAlumno = document.getElementById('id_alumno').value;
            const idAsignatura = document.getElementById('id_asignatura').value;
            const fechaInicio = document.getElementById('fecha_inicio_curso').value;
            const fechaFin = document.getElementById('fecha_fin_curso').value;
            const validationMessage = document.getElementById('validationMessage');
            
            // Limpiar mensaje anterior
            validationMessage.innerHTML = '';
            validationMessage.style.display = 'none';
            
            // Validaciones básicas
            if (!idAlumno || !idAsignatura || !fechaInicio || !fechaFin) {
                mostrarError('Todos los campos son obligatorios');
                return false;
            }
            
            if (new Date(fechaFin) <= new Date(fechaInicio)) {
                mostrarError('La fecha de fin debe ser posterior a la fecha de inicio');
                return false;
            }
            
            // Verificar si ya está matriculado con AJAX
            fetch('validar_matricula.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id_alumno=' + idAlumno + '&id_asignatura=' + idAsignatura
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    mostrarError(data.error);
                } else {
                    // Si no hay error, enviar el formulario
                    document.getElementById('formMatricula').submit();
                }
            })
            .catch(error => {
                mostrarError('Error al validar: ' + error);
            });
            
            return false;
        }
        
        function mostrarError(mensaje) {
            const validationMessage = document.getElementById('validationMessage');
            validationMessage.className = 'alert-box alert-error';
            validationMessage.innerHTML = '<strong>❌ Error:</strong> ' + mensaje;
            validationMessage.classList.remove('validation-hidden');
            validationMessage.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    </script>
</body>
</html>