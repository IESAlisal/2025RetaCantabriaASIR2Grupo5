<?php
// ============================================================================
// ARCHIVO: php/registro/procesar_registro.php
// DESCRIPCIÓN: Procesa el formulario de registro y crea nuevo usuario
// VALIDACIONES: Nombre, apellido, usuario (4+ chars), email válido, 
//               teléfono (6-15 dígitos), contraseña (6+ chars), coincidencia
// ============================================================================

// Habilita mostrar errores en desarrollo
ini_set("display_errors", true);
error_reporting(E_ALL);

// Incluye configuración de BD
require_once "../constantes/constantes.php";

// Incluye funciones de BD
require_once "../../funciones/funciones.php";

// ===== VALIDACIÓN: Verifica que es solicitud POST =====
// Si no es POST (ej: acceso directo), redirecciona
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Redirecciona al formulario
    header("Location: registro.php");
    exit();
}

// ===== RECUPERAR DATOS DEL FORMULARIO =====
// Obtiene todos los campos del POST, valor por defecto cadena vacía
$nombre = $_POST["nombre"] ?? "";
$apellido = $_POST["apellido"] ?? "";
$usuario = $_POST["usuario"] ?? "";
$correo = $_POST["correo"] ?? "";
$telefono = $_POST["telefono"] ?? "";
$contrasena = $_POST["contrasena"] ?? "";
$contrasena_conf = $_POST["contrasena_conf"] ?? "";
$rol = $_POST["rol"] ?? "";

// ===== VALIDACIONES LADO SERVIDOR =====
// Array para almacenar errores encontrados
$errores = [];

// Valida que nombre no esté vacío
if (empty($nombre)) {
    $errores[] = "El nombre es obligatorio";
}

// Valida que apellido no esté vacío
if (empty($apellido)) {
    $errores[] = "El apellido es obligatorio";
}

// Valida que usuario no esté vacío y tenga mínimo 4 caracteres
// strlen() retorna número de caracteres
if (empty($usuario) || strlen($usuario) < 4) {
    $errores[] = "El usuario debe tener al menos 4 caracteres";
}

// Valida que correo sea válido
// filter_var() con FILTER_VALIDATE_EMAIL retorna false si no es email válido
if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $errores[] = "El correo debe ser válido";
}

// Valida que teléfono tenga 6-15 dígitos
// preg_match() usa regex, '^[0-9]{6,15}$' = 6 a 15 números solamente
if (empty($telefono) || !preg_match('/^[0-9]{6,15}$/', $telefono)) {
    $errores[] = "El teléfono debe tener entre 6 y 15 dígitos";
}

// Valida que contraseña no esté vacía y tenga mínimo 6 caracteres
if (empty($contrasena) || strlen($contrasena) < 6) {
    $errores[] = "La contraseña debe tener al menos 6 caracteres";
}

// Valida que las dos contraseñas coincidan
// !== es "no idéntico" (compara valor y tipo)
if ($contrasena !== $contrasena_conf) {
    $errores[] = "Las contraseñas no coinciden";
}

// Valida que el rol sea uno de los aceptados (ALUMNO o PROFESOR)
if ($rol !== 'ROL-ALU' && $rol !== 'ROL-PRO') {
    $errores[] = "Debe seleccionar un rol válido";
}

// ===== SI HAY ERRORES, REDIRECCIONA CON MENSAJE =====
if (!empty($errores)) {
    // Junta todos los errores con separador "|"
    // implode() convierte array a string
    $msg = implode(" | ", $errores);
    
    // Redirecciona al formulario con mensaje y tipo error
    // urlencode() convierte espacios y caracteres en URL-safe
    header("Location: registro.php?msg=" . urlencode($msg) . "&tipo=error");
    exit();
}

// ===== PREPARAR PARA INSERTAR =====
// Asegura que los roles existenen BD antes de intentar insertar usuario
ensureDefaultRoles();

// ===== REGISTRAR USUARIO =====
// Llama función que inserta en usuarios, login, y alumno/profesor
// Retorna array con ['success' => bool, 'msg' => string]
$resultado = registroUsuario($usuario, $contrasena, $nombre, $apellido, $correo, $telefono, $rol);

// ===== VERIFICAR RESULTADO =====
// Si el registro fue exitoso
if ($resultado['success']) {
    // Redirecciona a login con mensaje de éxito
    header("Location: ../login/login.php?msg=" . urlencode("Registro completado. Por favor inicia sesión.") . "&tipo=success");
    exit();
} else {
    // Si falló, redirecciona al formulario con error
    header("Location: registro.php?msg=" . urlencode($resultado['msg']) . "&tipo=error");
    exit();
}
}
?>
