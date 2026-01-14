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
    <link rel="stylesheet" href="css/estilos_unificados.css">
</head>
<body>
    <div class="navbar">
        <h1>🎨 Academia de Pintura</h1>
    </div>
    
    <div class="container">
        <div id="loading" class="loading hidden">
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
