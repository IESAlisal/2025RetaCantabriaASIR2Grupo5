<?php
// login.php
session_start();
require_once "funciones.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$usuario = $_POST["usuario"] ?? "";
$contrasena_hash  = $_POST["contrasena_hash"] ?? "";

if (comprobarLogin($usuario, $contrasena_hash)) {
    $_SESSION["usuario"] = $usuario;
    header("Location: indice.php");
    exit;
} else {
    header("Location: index.php?msg=" . urlencode("Usuario o contraseña incorrectos"));
    exit;
}