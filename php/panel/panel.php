<?php
// ============================================================================
// ARCHIVO: php/panel/panel.php
// DESCRIPCIÓN: Panel principal de usuario después de login exitoso
// REQUIERE: Sesión activa ($_SESSION['usuario'] debe existir)
// ACCESO: Solo usuarios autenticados
// ============================================================================

// Inicia sesión para verificar autenticación
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluye configuración de BD
require_once "../constantes/constantes.php";

// Incluye funciones de BD
require_once "../../funciones/funciones.php";

// ===== VALIDACIÓN DE SESIÓN =====
// Verifica que el usuario esté autenticado (tiene sesión)
if (!isset($_SESSION['usuario'])) {
    // Si no hay sesión, redirecciona al login
    header("Location: ../login/login.php");
    exit();
}

// ===== OBTENER DATOS DE SESIÓN =====
// Obtiene el nombre de usuario de la sesión actual
$usuario = $_SESSION['usuario'];

// Obtiene el rol del usuario desde la sesión si existe, sino desde la BD
$rol = $_SESSION['rol'] ?? obtenerRolUsuario($usuario);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Especifica que el documento está en español -->
    <meta charset="UTF-8">
    
    <!-- Viewport para responsive design (se adapta a móviles) -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Título de la página (aparece en tab del navegador) -->
    <title>Panel - Academia de Pintura</title>
    
    <!-- Enlace a hoja de estilos global -->
    <link rel="stylesheet" href="../../css/estilos_unificados.css">
</head>
<body>
    <!-- BARRA DE NAVEGACIÓN -->
    <div class="navbar">
        <!-- Título/logo -->
        <h1>Academia de Pintura</h1>
        
        <!-- Enlace para cerrar sesión (destruye sesión y redirecciona) -->
        <a href="../../logout.php">Cerrar Sesión</a>
    </div>
    
    <!-- CONTENIDO PRINCIPAL -->
    <div class="container">
        <?php
            // Mostrar la vista según el rol usando un switch
            switch ($rol) {
                case 'ADMIN':
                    include 'PanelAdmin.php';
                    break;
                case 'PROFESOR':
                    include 'PanelProfesor.php';
                    break;
                case 'ALUMNO':
                    include 'PanelAlumno.php';
                    break;
                default:
                    echo '<div class="welcome"><h2>Bienvenido, '.htmlspecialchars($usuario).'!</h2><p>Rol desconocido.</p></div>';
            }
        ?>
    </div>
</body>
</html>
