<?php
session_start();
require_once "../constantes/constantes.php";
require_once "../../funciones/funciones.php";

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login/login.php");
    exit();
}

$usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel - Academia de Pintura</title>
    <link rel="stylesheet" href="../../css/estilos.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .navbar h1 {
            font-size: 24px;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            transition: background 0.3s;
        }
        .navbar a:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .welcome {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .welcome h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .welcome p {
            color: #666;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Academia de Pintura</h1>
        <a href="../../logout.php">Cerrar Sesión</a>
    </div>
    
    <div class="container">
        <div class="welcome">
            <h2>Bienvenido, <?php echo htmlspecialchars($usuario); ?>!</h2>
            <p>Has iniciado sesión correctamente en la Academia de Pintura.</p>
            <p>Sistema de gestión académico operativo.</p>
        </div>
    </div>
</body>
</html>
