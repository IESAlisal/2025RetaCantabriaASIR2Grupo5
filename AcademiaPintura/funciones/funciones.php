<?php

// Incluir archivo de constantes con configuración de BD
include_once 'constantes.php';


function getConexionPDO()
{
	$opciones = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
	try
	{
	    $dwes = new PDO('mysql:host='.HOST.';dbname='.DATABASE, USERNAME, PASSWORD, $opciones);
	    $dwes->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    return $dwes;
	}
	catch(Exception $ex)
	{
	    echo "<h4>{$ex->getMessage()}</h4>";
	    return null;
	}
}


function getConexion_sin_bbdd()
{
    $conexion = new mysqli(HOST, USERNAME, PASSWORD);
    $conexion->set_charset("utf8");

    $error = $conexion->connect_errno;

    if ($conexion->connect_errno) {
        print "<p> Se ha producido un error con la conexion con la base de datos: $conexion -> $error. </p>";
        exit();
    } else {
        return $conexion;
    }
}


function crearBBDD($basedatos){
    try {
        $conexion = getConexionPDO();
        
        $sql = "SELECT schema_name FROM information_schema.schemata WHERE schema_name = :basedatos";
        $stm = $conexion->prepare($sql);
        $stm->bindParam(':basedatos', $basedatos);
        $stm->execute();
        
        $existe = $stm->fetch(PDO::FETCH_ASSOC);
        
        if (!$existe) {
            // Crear la base de datos
            $sql = "CREATE DATABASE $basedatos";
            if ($conexion->exec($sql) !== false) {
                echo "Base de datos $basedatos creada en MySQL por Objetos ";
                echo "<br>";
            } else {
                echo "Error al ejecutar consulta: " . $conexion->errorInfo();
                $existe = 1;
            }
            print_r("Estoy aqui0");
        }
        
        return $existe;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return 1;
    }
}


function crearTablas()
{
    $conexion = getConexion();
    
    $aplicacionOkey = false;
    $loginOkey = false;

    // Crea tabla de aplicaciones con campos: id, nombre, descripción
    $aplicaciones = "CREATE TABLE IF NOT EXISTS `aplicaciones` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `nombre_aplicacion` varchar(50) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
        `descripcion` varchar(300) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    if ($conexion ->query($aplicaciones)) {
        $aplicacionOkey = true;
    }
    
    // Crea tabla de logins con campos: usuario (PK), contraseña
    $login = "CREATE TABLE IF NOT EXISTS `logins` (
        `usuario` VARCHAR(50) NOT NULL PRIMARY KEY,
        `passwd` VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    if ($conexion ->query($login)) {
        $loginOkey = true;
    
    if ($aplicacionOkey && $loginOkey) {
        $conexion->close();
        return 1; // Ambas tablas creadas
    } else {
        $conexion->close();
        return 0; // Falló crear alguna tabla
    }
}}


function IniciarSesion($usuario, $password)
{
    $conexion = getConexion();
    
    // Consulta preparada para evitar SQL injection
    $consulta = "SELECT passwd FROM logins WHERE usuario = ?;";
    $stmt = $conexion->prepare($consulta);

    if (!$stmt) {
        $conexion->close();
        return 1;
    }

    // Vincula el parámetro usuario
    $stmt->bind_param("s", $usuario);
    $stmt->execute();

    $res = $stmt->get_result();

    // Si el usuario no existe
    if ($res->num_rows === 0) {
        $stmt->close();
        $conexion->close();
        return false;
    }

    // Obtiene la contraseña hasheada de la BD
    $row = $res->fetch_assoc();
    $passwordBD = $row['passwd'];
    $stmt->close();
    $conexion->close(); 

    // Compara la contraseña ingresada (hasheada) con la de la BD
    return md5($password) === $passwordBD;
}


function registrarUsuario($usuario, $password)
{
    $conexion = getConexion();
    
    // Consulta preparada para insertar usuario
    $insertusuarios = "INSERT INTO logins (usuario, passwd) VALUES (?, ?);";
    $stmt = $conexion->prepare($insertusuarios);

    if (!$stmt) {
        $conexion->close();
        return false;
    }

    // Hashea la contraseña con MD5
    $passMD5 = md5($password);

    // Vincula los parámetros
    $stmt->bind_param("ss", $usuario, $passMD5);
    $stmt->execute();

    // Verifica si se insertó alguna fila
    $res = $stmt->affected_rows > 0;
    
    $stmt->close();
    $conexion->close();

    return $res;
}


function insertarAplicacion($nombre_aplicacion, $descripcion)
{
    $conexion = getConexion();
    
    // Consulta preparada para insertar aplicación
    $insertlibros = "INSERT INTO aplicaciones (nombre_aplicacion, descripcion)
                     VALUES (?, ?);";
    $stmt = $conexion->prepare($insertlibros);

    if (!$stmt) {
        $conexion->close();
        return 1;
    }
    
    // Vincula los parámetros
    $stmt->bind_param("ss", $nombre_aplicacion, $descripcion);
    $stmt->execute();

    // Verifica si se insertó correctamente
    if ($res = $stmt->affected_rows > 0) {
        $stmt->close();
        $conexion->close();
        return 1; // Éxito
    } else {
        $stmt->close();
        $conexion->close();
        return 0; // Falló
    }
}


function getAplicaciones()
{
    $conexion = getConexion();
    
    // Selecciona todas las aplicaciones
    $selectlibro = "SELECT * FROM aplicaciones;";

    $res = $conexion->query($selectlibro);

    // Array para almacenar los resultados
    $aplicaciones = [];

    // Itera sobre los resultados y los convierte en objetos
    while ($aplicacion = $res->fetch_object()) {
        $aplicaciones[] = $aplicacion;
    }
    
    return $aplicaciones;
}


function borrarAplicaciones($id)
{
    $conexion = getConexion();
    
    // Consulta preparada para eliminar aplicación
    $deleteaplicacion = "DELETE FROM aplicaciones WHERE id = ?;";
    
    $stmt = $conexion->prepare($deleteaplicacion);

    if (!$stmt) {
        $conexion->close();
        return 1;
    }

    // Vincula el parámetro ID
    $stmt->bind_param("i", $id);
    $res = $stmt->execute();
}

?>