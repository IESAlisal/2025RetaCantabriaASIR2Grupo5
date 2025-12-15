<?php
// ============================================================================
// ARCHIVO: php/login/procesar_login.php
// DESCRIPCIÓN: Procesa el formulario de login y autentica al usuario
// FLUJO: Recibe POST → Valida credenciales → Crea sesión o redirecciona
// ============================================================================

// Inicia sesión de PHP para poder usar $_SESSION
session_start();

// Habilita mostrar errores (importante en desarrollo)
error_reporting(E_ALL);
ini_set("display_errors", true);

// Incluye archivo de constantes con credenciales de BD
require_once "../constantes/constantes.php";

// Incluye archivo con funciones de BD
require_once "../../funciones/funciones.php";

// ===== VALIDACIÓN: Verifica que es una solicitud POST =====
// Si el método no es POST (por ejemplo GET), redirecciona al formulario
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Redirecciona al formulario de login
    header("Location: ./login.php");
    // Termina la ejecución
    exit();
}

// ===== RECUPERAR DATOS DEL FORMULARIO =====
// Obtiene el usuario del POST, o cadena vacía si no existe
// El operador ?? es "null coalescing" - retorna valor si existe, sino default
$usuario = $_POST["usuario"] ?? "";

// Obtiene la contraseña del POST, o cadena vacía si no existe
$contrasena = $_POST["contrasena"] ?? "";

// ===== HASHEAR CONTRASEÑA =====
// Hashea la contraseña con MD5 para compararla con la BD
// NOTA: MD5 es menos seguro que bcrypt, considera migrar en futuro
$contrasena_hash = md5($contrasena);

// ===== VALIDAR CREDENCIALES =====
// Llama función que verifica si usuario y hash existen en BD
if (comprobarLogin($usuario, $contrasena_hash)) {
    // ===== LOGIN EXITOSO =====
    // Guarda el usuario en la sesión PHP
    $_SESSION["usuario"] = $usuario;
    
    // Redirecciona al panel de usuario
    header("Location: ../panel/panel.php");
    
    // Termina ejecución para evitar código posterior
    exit();
} else {
    // ===== LOGIN FALLIDO =====
    // Redirecciona al login con mensaje de error
    // urlencode() convierte espacios y caracteres especiales en URL-safe
    header("Location: ./login.php?msg=" . urlencode("Usuario o contraseña incorrectos"));
    
    // Termina ejecución
    exit();
}
?>
