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
    
    $conexion = getConexionPDO();

    
    $academiaOkey = false;
    $loginOkey = false;

    
    // Crea tabla de rol con campos: codigo_rol (PK), nombre_rol
    $rol = "CREATE TABLE IF NOT EXISTS `rol` (
             `codigo_rol` VARCHAR(10) PRIMARY KEY CHECK (`codigo_rol` IN ('ROL-PRO', 'ROL-ALU', 'ROL-ADM')),
             `nombre_rol` VARCHAR(20) NOT NULL UNIQUE CHECK (`nombre_rol` IN ('PROFESOR', 'ALUMNO', 'ADMIN'))
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    if ($conexion ->query($rol)) {
        $aplicacionOkey = true;
    }

    // Crea tabla de usuarios con varios campos y restricciones
    $usuarios = "CREATE TABLE IF NOT EXISTS `usuarios` (
                 `id_usuario` VARCHAR(20) PRIMARY KEY,
                 `codigo_usuario` VARCHAR(20) UNIQUE NOT NULL CHECK (`codigo_usuario` LIKE 'USU-%'),
                 `nombre` VARCHAR(100) NOT NULL,
                 `apellido` VARCHAR(100) NOT NULL,
                 `correo` VARCHAR(100) UNIQUE NOT NULL CHECK (`correo` LIKE '%@%.%'),
                 `telefono` VARCHAR(15) NOT NULL CHECK (`telefono` REGEXP '^[0-9]{6,15}$'),
                 `fecha_nacimiento` DATE,
                 `direccion` TEXT,
                 `estado` ENUM('Activo', 'Inactivo', 'Suspendido') DEFAULT 'Activo',
                 `rol_codigo` VARCHAR(10) NOT NULL,
                 `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                 FOREIGN KEY (`rol_codigo`) REFERENCES `rol`(`codigo_rol`),
                 CONSTRAINT chk_id_formato CHECK (`id_usuario` REGEXP '^[A-Z0-9-]+$')
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    if ($conexion ->query($usuarios)) {
        $loginOkey = true;
    
    if ($aplicacionOkey && $loginOkey) {
    }
    /*if ($aplicacionOkey && $loginOkey) {
        $conexion->close();
        return 1; // Ambas tablas creadas
    } else {
        $conexion->close();
        return 0; // Falló crear alguna tabla
    }*/
    
    $login = "CREATE TABLE IF NOT EXISTS `login` (
             `id_login` INT AUTO_INCREMENT PRIMARY KEY,
             `id_usuario` VARCHAR(20) NOT NULL UNIQUE,
             `usuario` VARCHAR(50) UNIQUE NOT NULL,
             `contrasena_hash` VARCHAR(255) NOT NULL,
             `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
             `ultimo_acceso` TIMESTAMP NULL,
             `estado` ENUM('Activo', 'Inactivo', 'Bloqueado') DEFAULT 'Activo',
             FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    if ($conexion ->query($login)) {
        $loginOkey = true;
    }
}}

    $datos_bancarios = "CREATE TABLE IF NOT EXISTS `datos_bancarios` (
                         `id_dato_bancario` INT AUTO_INCREMENT PRIMARY KEY,
                         `id_usuario` VARCHAR(20) NOT NULL UNIQUE,
                         `nombre_titular` VARBINARY(255) NOT NULL,
                         `numero_cuenta` VARBINARY(255) NOT NULL,
                         `tipo_cuenta` ENUM('Ahorros', 'Corriente', 'Nómina') NOT NULL,
                         `nombre_banco` VARCHAR(100) NOT NULL,
                         `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                         `ultima_modificacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                         `intentos_acceso_fallidos` INT DEFAULT 0,
                         FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE
                         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    if ($conexion ->query($datos_bancarios)) {
        $loginOkey = true;
    }

    $profesor = "CREATE TABLE IF NOT EXISTS `profesor` (
                 `id_profesor` INT AUTO_INCREMENT PRIMARY KEY,
                 `id_usuario` VARCHAR(20) NOT NULL UNIQUE,
                 `fecha_contratacion` DATE NOT NULL,
                 `tipo_contrato` ENUM('Tiempo Completo', 'Medio Tiempo', 'Por Horas') DEFAULT 'Tiempo Completo',
                 `salario` DECIMAL(10,2),
                 FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE
                 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    if ($conexion ->query($profesor)) {
        $loginOkey = true;
    }


/*unnciIniciarSesion($usuario, $password)
{
    $conexion = getConexionPDO();
    
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
*/

function registrarUsuario($usuario, $contrasena_hash)
{
    $conexion = getConexionPDO();
    
    // Consulta preparada para insertar usuario
    $insertusuarios = "INSERT INTO logins (usuario, contrasena_hash) VALUES (?, ?);";
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

function insertarAsignatura($nombre_asignatura,$descripcion)
{
    $conexion = getConexionPDO();
    
    // Consulta preparada para insertar aplicación
    $insertAsignaturas = "INSERT INTO asignaturas (nombre_asignatura, descripcion)
                     VALUES (?, ?);";
    $stmt = $conexion->prepare($insertAsignaturas);

    if (!$stmt) {
        $conexion->close();
        return 1;
    }
    
    // Vincula los parámetros
    $stmt->bind_param("ss", $nombre_asignatura, $descripcion);
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


function getAsignaturas()
{
    $conexion = getConexionPDO();
    
    // Selecciona todas las aplicaciones
    $selectlibro = "SELECT * FROM nombre_asignatura;";

    $res = $conexion->query($selectlibro);

    // Array para almacenar los resultados
    $nombre_asignatura= [];

    // Itera sobre los resultados y los convierte en objetos
    while ($aplicacion = $res->fetch_object()) {
        $asignaturas[] = $nombre_asignatura;
    }
    
    return $asignaturas;
}


function borrarAsignaturas($id)
{
    $conexion = getConexionPDO();
    
    // Consulta preparada para eliminar aplicación
    $deletenombre_asignatura = "DELETE FROM asignaturas WHERE id = ?;";
    
    $stmt = $conexion->prepare($deletenombre_asignatura);

    if (!$stmt) {
        $conexion->close();
        return 1;
    }

    // Vincula el parámetro ID
    $stmt->bind_param("i", $id);
    $res = $stmt->execute();
}
// Comprobar login
function comprobarLogin($usuario, $contrasena_hash) {
    $conn = getConnectionPDO();
    $sql  = "SELECT * FROM login WHERE usuario = ? AND contrasena_hash = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $usuario, $contrasena_hash);
    $stmt->execute();
    $res = $stmt->get_result();
    $ok  = ($res->num_rows === 1);
    $stmt->close();
    $conn->close();
    return $ok;
}
?>