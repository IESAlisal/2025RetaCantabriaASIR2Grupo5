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
    <link rel="stylesheet" href="../../css/estilos.css">
    
    <!-- Estilos CSS específicos de esta página -->
    <style>
        /* Reset de márgenes y padding en todos los elementos */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* Estilos del body (fondo gris claro) */
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        
        /* Barra de navegación superior con gradiente */
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        /* Título en la navbar */
        .navbar h1 {
            font-size: 24px;
        }
        
        /* Enlaces en la navbar (botón Cerrar Sesión) */
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        /* Efecto hover en enlaces de navbar */
        .navbar a:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        /* Contenedor principal con ancho máximo */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        /* Caja de bienvenida con fondo blanco */
        .welcome {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        /* Encabezado de bienvenida */
        .welcome h2 {
            color: #333;
            margin-bottom: 10px;
        }
        
        /* Párrafos en la caja de bienvenida */
        .welcome p {
            color: #666;
            font-size: 16px;
        }

        /* Estilos para desplegables (details/summary) usados en los paneles */
        .dropdown details {
            margin-top: 10px;
        }

        .dropdown summary {
            cursor: pointer;
            padding: 12px 15px;
            background: linear-gradient(90deg, #f3f4f6, #ffffff);
            border-radius: 8px;
            list-style: none;
            font-weight: 600;
            color: #333;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            outline: none;
        }

        .dropdown[open] summary {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .dropdown ul {
            margin: 10px 0 0 0;
            padding-left: 18px;
        }

        .dropdown ul li {
            margin: 8px 0;
        }

        .dropdown a {
            color: #4a6cf7;
            text-decoration: none;
        }

        .dropdown a:hover {
            text-decoration: underline;
        }
    </style>
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
