<?php
// ============================================================================
// ARCHIVO: logout.php
// DESCRIPCIÓN: Destruye la sesión del usuario y lo redirecciona al login
// UBICACIÓN: Raíz del proyecto (accesible desde cualquier módulo)
// ============================================================================

// Inicia sesión para poder acceder a $_SESSION
session_start();

// ===== DESTRUIR SESIÓN =====
// Borra todas las variables de sesión
session_unset();

// Destruye completamente la sesión PHP
session_destroy();

// ===== REDIRECCIONAR AL LOGIN =====
// Envía al usuario al formulario de login
header("Location: ./php/login/login.php");

// Termina ejecución para evitar código posterior
exit();
?>