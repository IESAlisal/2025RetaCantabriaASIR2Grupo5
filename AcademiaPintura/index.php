!DOCTYPE html>
<html lang="es">
<head>
    <!-- Título de la página -->
    <title>Creacion de tablas</title>
    
    <!-- Codificación UTF-8 -->
    <meta charset="UTF-8">
    
    <!-- Configuración responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>

<body>

    <!-- Encabezado de bienvenida -->
    <h1>Bienvenido a nuestra academia de pintura</h1>
   
    <?php 
        // Mostrar errores de PHP en la página
        ini_set("display_errors", true);
        
        // Incluir archivo de funciones con conexión a BD
        include_once './funciones/funciones.php';
        
        // Nombre de la base de datos a crear
        $basedatos = DATABASE;
        // Alternativa: $basedatos = DATABASE; (usar la constante del archivo constantes.php)
        
        // Intenta crear la BD
        $bbdd = crearBBDD($basedatos);
        
        // Verifica si se creó la BD o ya existía
        if($bbdd == 0){
            // Si la BD se creó exitosamente (retorna 0)
            // Intenta crear las tablas
            if(crearTablas() == 1){
                // Si las tablas se crearon exitosamente (retorna 1)
                // Redirige a la página de login
                header("Location: ./php/login.php");
            }
        }
        else if ($bbdd == 1){
            // Si la BD ya existía (retorna 1)
            // Redirige directamente a la página de login
            header("Location: ./php/login.php");
        }
    ?>

</body>
</html>