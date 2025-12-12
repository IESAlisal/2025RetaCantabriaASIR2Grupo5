<?php
// procesar_login.php - Procesar login
session_start();
error_reporting(E_ALL);
ini_set("display_errors", true);

require_once "../constantes/constantes.php";
require_once "../../funciones/funciones.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ./login.php");
    exit();
}

$usuario = $_POST["usuario"] ?? "";
$contrasena = $_POST["contrasena"] ?? "";

// Hashear la contraseña con MD5
$contrasena_hash = md5($contrasena);

if (comprobarLogin($usuario, $contrasena_hash)) {
    $_SESSION["usuario"] = $usuario;
    header("Location: ../panel/panel.php");
    exit();
} else {
    header("Location: ./login.php?msg=" . urlencode("Usuario o contraseña incorrectos"));
    exit();
}
?>
