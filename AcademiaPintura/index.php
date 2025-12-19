<?php
session_start();
ini_set("display_errors", true);
error_reporting(E_ALL);

require_once './php/constantes/constantes.php';
require_once './funciones/funciones.php';

// Crear base de datos y tablas si es necesario
$basedatos = DATABASE;
$bbdd = crearBBDD($basedatos);
if ($bbdd == 0 || $bbdd == 1) {
    crearTablas();
    ensureDefaultRoles();

    // Ejecutar script SQL externo para insertar roles y usuarios si no existen
    $conexion = getConexionPDO();
    if ($conexion) {
        try {
            $usersToCheck = ['admin','daniel','lucas','pool','hugo'];
            $placeholders = implode(',', array_fill(0, count($usersToCheck), '?'));
            $stmt = $conexion->prepare("SELECT usuario FROM `login` WHERE usuario IN ($placeholders)");
            $stmt->execute($usersToCheck);
            $found = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (count($found) === 0) {
                $sqlFile = __DIR__ . '/bbdd/insert_academia.sql';
                if (file_exists($sqlFile)) {
                    $sql = file_get_contents($sqlFile);
                    try {
                        $conexion->beginTransaction();
                        $stmts = array_filter(array_map('trim', explode(';', $sql)));
                        foreach ($stmts as $s) {
                            if ($s === '') continue;
                            $conexion->exec($s);
                        }
                        $conexion->commit();
                    } catch (PDOException $e) {
                        $conexion->rollBack();
                        echo "<!-- Error ejecutando insert_academia.sql: " . htmlspecialchars($e->getMessage()) . " -->";
                    }
                } else {
                    echo "<!-- insert_academia.sql no encontrado: $sqlFile -->";
                }
            }
        } catch (Exception $e) {
            echo "<!-- Error comprobando usuarios existentes: " . htmlspecialchars($e->getMessage()) . " -->";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academia de Pintura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            background: rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }
        .navbar h1 {
            margin: 0;
            font-size: 28px;
        }
        .navbar-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 10px 20px;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.2);
            transition: background 0.3s;
        }
        .navbar-links a:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        .container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }
        .content-box {
            background: white;
            padding: 60px 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            text-align: center;
            max-width: 600px;
            width: 100%;
        }
        .content-box h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 32px;
        }
        .content-box p {
            color: #666;
            font-size: 18px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .btn-group {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 15px 40px;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
            border: 2px solid #667eea;
        }
        .btn-secondary:hover {
            transform: translateY(-2px);
            background: #667eea;
            color: white;
        }
        .loading {
            text-align: center;
            color: white;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🎨 Academia de Pintura</h1>
    </div>
    
    <div class="container">
        <div id="loading" class="loading" style="display: none;">
            <h2>Inicializando sistema...</h2>
            <p>Preparando la base de datos.</p>
        </div>
        
        <div id="content" class="content-box">
            <h2>Bienvenido</h2>
            <p>Accede a tu cuenta para continuar con tus actividades en la Academia de Pintura.</p>
            <div class="btn-group">
                <a href="php/login/login.php" class="btn btn-primary">Iniciar Sesión</a>
            </div>
        </div>
    </div>

    
</body>
</html>
