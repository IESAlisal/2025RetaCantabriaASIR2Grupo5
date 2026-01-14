<?php
// ============================================================================
// ARCHIVO: php/profesor/validar_matricula.php
// DESCRIPCIÓN: Valida si un alumno ya está matriculado (AJAX)
// ACCESO: Solo para PROFESORES
// ============================================================================

// Iniciar sesión
session_start();

// Verificar que existe sesión
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

// Incluir funciones y constantes
require_once "../../funciones/funciones.php";
require_once "../constantes/constantes.php";

// Verificar que es PROFESOR
$rol = $_SESSION['rol'] ?? obtenerRolUsuario($_SESSION['usuario']);
if ($rol !== 'PROFESOR') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit();
}

// Verificar que es POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

// Obtener datos
$id_alumno = $_POST['id_alumno'] ?? '';
$id_asignatura = $_POST['id_asignatura'] ?? '';

// Validar que los parámetros estén presentes
if (empty($id_alumno) || empty($id_asignatura)) {
    echo json_encode(['error' => 'Parámetros inválidos']);
    exit();
}

try {
    // Obtener conexión
    $conexion = getConexionPDO();
    if (!$conexion) {
        throw new Exception("Error de conexión a la base de datos");
    }
    
    // Verificar si ya está matriculado en esta asignatura
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
        echo json_encode(['error' => 'Este alumno ya está matriculado en esta asignatura']);
        exit();
    }
    
    // Si llegamos aquí, no hay error
    echo json_encode(['error' => null]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>
