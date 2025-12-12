<?php
// procesar_registro.php - Procesar registro de usuario

ini_set("display_errors", true);
error_reporting(E_ALL);

require_once "../constantes/constantes.php";
require_once "../../funciones/funciones.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: registro.php");
    exit();
}

// Obtener y validar datos
$nombre = $_POST["nombre"] ?? "";
$apellido = $_POST["apellido"] ?? "";
$usuario = $_POST["usuario"] ?? "";
$correo = $_POST["correo"] ?? "";
$telefono = $_POST["telefono"] ?? "";
$contrasena = $_POST["contrasena"] ?? "";
$contrasena_conf = $_POST["contrasena_conf"] ?? "";
$rol = $_POST["rol"] ?? "";

// Validaciones básicas
$errores = [];

if (empty($nombre)) {
    $errores[] = "El nombre es obligatorio";
}

if (empty($apellido)) {
    $errores[] = "El apellido es obligatorio";
}

if (empty($usuario) || strlen($usuario) < 4) {
    $errores[] = "El usuario debe tener al menos 4 caracteres";
}

if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $errores[] = "El correo debe ser válido";
}

if (empty($telefono) || !preg_match('/^[0-9]{6,15}$/', $telefono)) {
    $errores[] = "El teléfono debe tener entre 6 y 15 dígitos";
}

if (empty($contrasena) || strlen($contrasena) < 6) {
    $errores[] = "La contraseña debe tener al menos 6 caracteres";
}

if ($contrasena !== $contrasena_conf) {
    $errores[] = "Las contraseñas no coinciden";
}

if ($rol !== 'ROL-ALU' && $rol !== 'ROL-PRO') {
    $errores[] = "Debe seleccionar un rol válido";
}

// Si hay errores, redirigir de vuelta
if (!empty($errores)) {
    $msg = implode(" | ", $errores);
    header("Location: registro.php?msg=" . urlencode($msg) . "&tipo=error");
    exit();
}

// Asegurar que los roles existen en BD antes de intentar insertar
ensureDefaultRoles();

// Registrar usuario
$resultado = registroUsuario($usuario, $contrasena, $nombre, $apellido, $correo, $telefono, $rol);

if ($resultado['success']) {
    header("Location: ../login/login.php?msg=" . urlencode("Registro completado. Por favor inicia sesión.") . "&tipo=success");
    exit();
} else {
    header("Location: registro.php?msg=" . urlencode($resultado['msg']) . "&tipo=error");
    exit();
}
?>
