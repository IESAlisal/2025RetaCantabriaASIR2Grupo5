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
        <div class="navbar-links">
            <a href="php/login/login.php">Iniciar Sesión</a>
            <a href="php/registro/registro.php">Registrarse</a>
        </div>
    </div>
    
    <div class="container">
        <div id="loading" class="loading" style="display: none;">
            <h2>Inicializando sistema...</h2>
            <p>Preparando la base de datos.</p>
        </div>
        
        <div id="content" class="content-box" style="display: none;">
            <h2>Bienvenido</h2>
            <p>Accede a tu cuenta para continuar con tus actividades en la Academia de Pintura.</p>
            <div class="btn-group">
                <a href="php/login/login.php" class="btn btn-primary">Iniciar Sesión</a>
                <a href="php/registro/registro.php" class="btn btn-secondary">Crear Cuenta</a>
            </div>
        </div>
    </div>

    <?php 
        // Mostrar errores de PHP en la página
        ini_set("display_errors", true);
        error_reporting(E_ALL);
        
        // Incluir archivo de constantes con configuración de BD
        require_once './php/constantes/constantes.php';
        
        // Incluir archivo de funciones con conexión a BD
        require_once './funciones/funciones.php';
        
        // Nombre de la base de datos a crear
        $basedatos = DATABASE;
        
        // Intenta crear la BD
        $bbdd = crearBBDD($basedatos);
        
        // Verifica si se creó la BD o ya existía
        if($bbdd == 0){
            // Si la BD se creó exitosamente (retorna 0)
            // Intenta crear las tablas
            if(crearTablas() == 1){
                // Las tablas se crearon exitosamente
                ensureDefaultRoles();
                echo '<script>
                    document.getElementById("loading").style.display = "none";
                    document.getElementById("content").style.display = "block";
                </script>';
            }
        }
        else if ($bbdd == 1){
            // Si la BD ya existía (retorna 1)
            ensureDefaultRoles();
            echo '<script>
                document.getElementById("loading").style.display = "none";
                document.getElementById("content").style.display = "block";
            </script>';
        }
    ?>
</body>
</html>
