<?php
// ============================================================================
// ARCHIVO: php/profesor/guardar_matricula.php
// DESCRIPCIÓN: Procesa el formulario de matrícula de alumnos
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

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Obtener datos del formulario
        $id_alumno = $_POST['id_alumno'] ?? '';
        $id_asignatura = $_POST['id_asignatura'] ?? '';
        $fecha_inicio = $_POST['fecha_inicio_curso'] ?? '';
        $fecha_fin = $_POST['fecha_fin_curso'] ?? '';
        
        // Validaciones básicas
        if (empty($id_alumno) || empty($id_asignatura) || empty($fecha_inicio) || empty($fecha_fin)) {
            throw new Exception("Todos los campos son obligatorios");
        }
        
        // Validar fechas
        if (strtotime($fecha_fin) <= strtotime($fecha_inicio)) {
            throw new Exception("La fecha de fin debe ser posterior a la fecha de inicio");
        }
        
        // Obtener conexión PDO
        $conexion = getConexionPDO();
        if (!$conexion) {
            throw new Exception("Error de conexión a la base de datos");
        }
        
        // ===== VALIDACIÓN 1: ¿Ya está matriculado en esta asignatura? =====
        // Verificar CUALQUIER matrícula activa (no retirada ni completada)
        $sql_check = "SELECT id_matricula FROM matricula 
                      WHERE id_alumno = :id_alumno 
                      AND id_asignatura = :id_asignatura
                      AND estado IN ('Activa', 'Pendiente')";
        
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->execute([
            ':id_alumno' => $id_alumno,
            ':id_asignatura' => $id_asignatura
        ]);
        
        if ($stmt_check->rowCount() > 0) {
            throw new Exception("Este alumno ya está matriculado en esta asignatura");
        }
        
        // ===== VALIDACIÓN 2: ¿Tiene matrícula en el mismo período? =====
        $sql_periodo = "SELECT id_matricula FROM matricula 
                        WHERE id_alumno = :id_alumno 
                        AND id_asignatura = :id_asignatura
                        AND (
                            (fecha_inicio_curso BETWEEN :fecha_inicio AND :fecha_fin)
                            OR (fecha_fin_curso BETWEEN :fecha_inicio AND :fecha_fin)
                            OR (:fecha_inicio BETWEEN fecha_inicio_curso AND fecha_fin_curso)
                        )
                        AND estado != 'Retirada'";
        
        $stmt_periodo = $conexion->prepare($sql_periodo);
        $stmt_periodo->execute([
            ':id_alumno' => $id_alumno,
            ':id_asignatura' => $id_asignatura,
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_fin' => $fecha_fin
        ]);
        
        if ($stmt_periodo->rowCount() > 0) {
            throw new Exception("El alumno ya tiene una matrícula en este período temporal");
        }
        
        // ===== VALIDACIÓN 3: ¿La asignatura está activa? =====
        $sql_asignatura = "SELECT estado FROM asignaturas WHERE id_asignatura = :id_asignatura";
        $stmt_asignatura = $conexion->prepare($sql_asignatura);
        $stmt_asignatura->execute([':id_asignatura' => $id_asignatura]);
        $asignatura_data = $stmt_asignatura->fetch(PDO::FETCH_ASSOC);
        
        if (!$asignatura_data || $asignatura_data['estado'] !== 'Activa') {
            throw new Exception("La asignatura no está disponible para matrícula");
        }
        
        // ===== VALIDACIÓN 4: ¿El alumno está activo? =====
        $sql_alumno = "SELECT u.estado 
                       FROM alumno a 
                       JOIN usuarios u ON a.id_usuario = u.id_usuario 
                       WHERE a.id_alumno = :id_alumno";
        $stmt_alumno = $conexion->prepare($sql_alumno);
        $stmt_alumno->execute([':id_alumno' => $id_alumno]);
        $alumno_data = $stmt_alumno->fetch(PDO::FETCH_ASSOC);
        
        if (!$alumno_data || $alumno_data['estado'] !== 'Activo') {
            throw new Exception("El alumno no está activo en el sistema");
        }
        
        // ===== TODO BIEN, CREAR MATRÍCULA =====
        // Generar código de matrícula único según tu formato
        $codigo_matricula = 'MATRI-' . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 3)) . '-' . sprintf('%03d', rand(0, 999));
        
        // Preparar consulta SQL con PDO
        $sql = "INSERT INTO matricula (
            codigo_matricula, 
            id_alumno, 
            id_asignatura, 
            fecha_matricula, 
            fecha_inicio_curso, 
            fecha_fin_curso,
            estado
        ) VALUES (
            :codigo,
            :id_alumno,
            :id_asignatura,
            CURDATE(),
            :fecha_inicio,
            :fecha_fin,
            'Activa'
        )";
        
        // Preparar y ejecutar con parámetros
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':codigo' => $codigo_matricula,
            ':id_alumno' => $id_alumno,
            ':id_asignatura' => $id_asignatura,
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_fin' => $fecha_fin
        ]);
        
        // ===== OBTENER DATOS PARA EL MENSAJE =====
        $sql_info = "SELECT 
                     u.nombre as alumno_nombre,
                     u.apellido as alumno_apellido,
                     a.nombre_asignatura
                     FROM alumno al
                     JOIN usuarios u ON al.id_usuario = u.id_usuario
                     JOIN asignaturas a ON :id_asignatura = a.id_asignatura
                     WHERE al.id_alumno = :id_alumno";
        
        $stmt_info = $conexion->prepare($sql_info);
        $stmt_info->execute([
            ':id_alumno' => $id_alumno,
            ':id_asignatura' => $id_asignatura
        ]);
        $info = $stmt_info->fetch(PDO::FETCH_ASSOC);
        
        $mensaje = "✅ Matrícula creada exitosamente!<br>";
        $mensaje .= "Alumno: " . $info['alumno_nombre'] . " " . $info['alumno_apellido'] . "<br>";
        $mensaje .= "Asignatura: " . $info['nombre_asignatura'] . "<br>";
        $mensaje .= "Código: " . $codigo_matricula . "<br>";
        $mensaje .= "Período: " . date('d/m/Y', strtotime($fecha_inicio)) . " - " . date('d/m/Y', strtotime($fecha_fin));
        
        // Guardar en sesión y redirigir
        $_SESSION['matricula_msg'] = $mensaje;
        $_SESSION['matricula_tipo'] = 'success';
        header("Location: matricular_alumno.php");
        exit();
        
    } catch (Exception $e) {
        // Guardar en sesión y redirigir
        $_SESSION['matricula_msg'] = $e->getMessage();
        $_SESSION['matricula_tipo'] = 'error';
        
        // Debug: verificar que se guarda correctamente
        error_log("Mensaje de error guardado en sesión: " . $e->getMessage());
        
        header("Location: matricular_alumno.php");
        exit();
    }
} else {
    // Si no es POST, redirigir
    header("Location: matricular_alumno.php");
    exit();
}
?>