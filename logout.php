<?php
// ============================================================================
// ARCHIVO: logout.php
// DESCRIPCIÓN: Destruye la sesión del usuario y muestra confirmación
// UBICACIÓN: Raíz del proyecto (accesible desde cualquier módulo)
// ============================================================================

// Inicia sesión para poder acceder a $_SESSION
session_start();

// ===== DESTRUIR SESIÓN =====
// Borra todas las variables de sesión
session_unset();

// Destruye completamente la sesión PHP
session_destroy();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesión Cerrada - Academia de Pintura</title>
    <link rel="stylesheet" href="./css/estilos_unificados.css">
</head>
<body class="centered-layout">
    <div class="login-container">
        <h1>Sesión Cerrada</h1>
        <div class="success-msg">
            <p>✅ Tu sesión ha sido cerrada correctamente.</p>
        </div>
        <p style="text-align: center; margin-top: 20px;">
            <a href="./php/login/login.php" class="btn btn-primary">Volver al Login</a>
        </p>
    </div>
</body>
</html>
