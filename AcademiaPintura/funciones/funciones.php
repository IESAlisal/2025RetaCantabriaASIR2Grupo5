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
    
    $tablasCreadas = 0;
    $totalTablas = 11;
    
    // Crea tabla de rol
    $rol = "CREATE TABLE IF NOT EXISTS `rol` (
             `codigo_rol` VARCHAR(10) PRIMARY KEY CHECK (`codigo_rol` IN ('ROL-PRO', 'ROL-ALU', 'ROL-ADM')),
             `nombre_rol` VARCHAR(20) NOT NULL UNIQUE CHECK (`nombre_rol` IN ('PROFESOR', 'ALUMNO', 'ADMIN'))
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    if ($conexion->query($rol)) {
        $tablasCreadas++;
    }

    // Crea tabla de usuarios
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

    if ($conexion->query($usuarios)) {
        $tablasCreadas++;
    }
    
    // Crea tabla de login
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

    if ($conexion->query($login)) {
        $tablasCreadas++;
    }

    // Crea tabla de datos_bancarios
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

    if ($conexion->query($datos_bancarios)) {
        $tablasCreadas++;
    }

    // Crea tabla de profesor
    $profesor = "CREATE TABLE IF NOT EXISTS `profesor` (
                 `id_profesor` INT AUTO_INCREMENT PRIMARY KEY,
                 `id_usuario` VARCHAR(20) NOT NULL UNIQUE,
                 `fecha_contratacion` DATE NOT NULL,
                 `tipo_contrato` ENUM('Tiempo Completo', 'Medio Tiempo', 'Por Horas') DEFAULT 'Tiempo Completo',
                 `salario` DECIMAL(10,2),
                 FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE
                 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    if ($conexion->query($profesor)) {
        $tablasCreadas++;
    }

    // Crea tabla de alumno
    $alumno = "CREATE TABLE IF NOT EXISTS `alumno` (
                `id_alumno` INT AUTO_INCREMENT PRIMARY KEY,
                `id_usuario` VARCHAR(20) NOT NULL UNIQUE,
                `fecha_ingreso` DATE NOT NULL,
                `beca` ENUM('Si', 'No') DEFAULT 'No',
                FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    if ($conexion->query($alumno)) {
        $tablasCreadas++;
    }

    // Crea tabla de aulas
    $aulas = "CREATE TABLE IF NOT EXISTS `aulas` (
                `id_aula` INT AUTO_INCREMENT PRIMARY KEY,
                `codigo_aula` VARCHAR(20) UNIQUE NOT NULL CHECK (`codigo_aula` REGEXP '^AULA-A-[0-9]{3}$'),
                `capacidad` INT NOT NULL CHECK (`capacidad` BETWEEN 1 AND 50),
                `piso` INT CHECK (`piso` BETWEEN 1 AND 10),
                `equipamiento` TEXT,
                `estado` ENUM('Activa', 'Mantenimiento', 'Inactiva') DEFAULT 'Activa'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    if ($conexion->query($aulas)) {
        $tablasCreadas++;
    }

    // Crea tabla de asignaturas
    $asignaturas = "CREATE TABLE IF NOT EXISTS `asignaturas` (
                    `id_asignatura` INT AUTO_INCREMENT PRIMARY KEY,
                    `codigo_asignatura` VARCHAR(20) UNIQUE NOT NULL CHECK (`codigo_asignatura` LIKE 'ASIG-%'),
                    `nombre_asignatura` VARCHAR(100) UNIQUE NOT NULL,
                    `horas_semanales` INT CHECK (`horas_semanales` BETWEEN 1 AND 20),
                    `descripcion` TEXT,
                    `id_profesor` INT,
                    `id_aula` INT,
                    `estado` ENUM('Activa', 'Electiva', 'Retirada') DEFAULT 'Activa',
                    FOREIGN KEY (`id_profesor`) REFERENCES `profesor`(`id_profesor`),
                    FOREIGN KEY (`id_aula`) REFERENCES `aulas`(`id_aula`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    if ($conexion->query($asignaturas)) {
        $tablasCreadas++;
    }

    // Crea tabla de materiales
    $materiales = "CREATE TABLE IF NOT EXISTS `materiales` (
                    `id_material` INT AUTO_INCREMENT PRIMARY KEY,
                    `codigo_material` VARCHAR(20) UNIQUE NOT NULL CHECK (`codigo_material` LIKE 'MAT-%'),
                    `nombre_material` VARCHAR(150) NOT NULL,
                    `tipo` ENUM('Libro', 'Digital', 'Equipo', 'Software', 'Otro') NOT NULL,
                    `descripcion` TEXT,
                    `cantidad_disponible` INT NOT NULL CHECK (`cantidad_disponible` >= 0),
                    `ubicacion` VARCHAR(100),
                    `estado` ENUM('Disponible', 'Prestado', 'Mantenimiento', 'Baja') DEFAULT 'Disponible',
                    `fecha_adquisicion` DATE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    if ($conexion->query($materiales)) {
        $tablasCreadas++;
    }

    // Crea tabla de matricula
    $matricula = "CREATE TABLE IF NOT EXISTS `matricula` (
                    `id_matricula` INT AUTO_INCREMENT PRIMARY KEY,
                    `codigo_matricula` VARCHAR(20) UNIQUE NOT NULL CHECK (`codigo_matricula` REGEXP '^MATRI-[A-Z]{3}-[0-9]{3}$'),
                    `id_alumno` INT NOT NULL,
                    `id_asignatura` INT NOT NULL,
                    `fecha_matricula` DATE NOT NULL,
                    `fecha_inicio_curso` DATE NOT NULL,
                    `fecha_fin_curso` DATE NOT NULL,
                    `calificacion` DECIMAL(4,2) CHECK (`calificacion` BETWEEN 0 AND 10),
                    `estado` ENUM('Activa', 'Completada', 'Retirada') DEFAULT 'Activa',
                    FOREIGN KEY (`id_alumno`) REFERENCES `alumno`(`id_alumno`),
                    FOREIGN KEY (`id_asignatura`) REFERENCES `asignaturas`(`id_asignatura`),
                    CONSTRAINT chk_fechas_matricula CHECK (`fecha_matricula` < `fecha_inicio_curso` AND `fecha_inicio_curso` < `fecha_fin_curso`),
                    CONSTRAINT uq_alumno_asignatura_periodo UNIQUE (`id_alumno`, `id_asignatura`, `fecha_inicio_curso`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    if ($conexion->query($matricula)) {
        $tablasCreadas++;
    }

    // Crea tabla de auditoria_login
    $auditoria_login = "CREATE TABLE IF NOT EXISTS `auditoria_login` (
                    `id_auditoria` INT AUTO_INCREMENT PRIMARY KEY,
                    `id_usuario` VARCHAR(20) NOT NULL,
                    `accion` ENUM('LOGIN_OK', 'LOGIN_FAIL', 'LOGOUT', 'BLOQUEADO') NOT NULL,
                    `fecha_hora` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `ip_address` VARCHAR(45)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    if ($conexion->query($auditoria_login)) {
        $tablasCreadas++;
    }

    $conexion = null;
    
    return ($tablasCreadas === $totalTablas) ? 1 : 0;
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
{g
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