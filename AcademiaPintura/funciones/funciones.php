<?php

// Incluir archivo de constantes con configuración de BD
if (!defined('HOST')) {
    require_once dirname(__FILE__) . '/../php/constantes/constantes.php';
}


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


function getConexion_sin_bbdd_PDO()
{
    try {
        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        );

        $dsn = 'mysql:host=' . HOST . ';charset=utf8mb4';

        $pdo = new PDO($dsn, USERNAME, PASSWORD, $options);
        return $pdo;
    } catch (PDOException $ex) {
        echo "<p>Se ha producido un error con la conexión a la base de datos: " . htmlspecialchars($ex->getMessage()) . "</p>";
        return null;
    }
}

function crearBBDD($basedatos){
    try {
        $conexion = getConexion_sin_bbdd_PDO();
        
        if (!$conexion) {
            return 1;
        }
        
        $sql = "SELECT schema_name FROM information_schema.schemata WHERE schema_name = :basedatos";
        $stm = $conexion->prepare($sql);
        $stm->execute([':basedatos' => $basedatos]);
        
        $existe = $stm->fetch(PDO::FETCH_ASSOC);
        
        if (!$existe) {
            // Crear la base de datos
            $sql = "CREATE DATABASE `" . $basedatos . "`";
            try {
                $conexion->exec($sql);
                echo "Base de datos $basedatos creada en MySQL por Objetos ";
                echo "<br>";
                return 0;
            } catch (PDOException $ex) {
                echo "Error al ejecutar consulta: " . $ex->getMessage();
                return 1;
            }
        }
        
        return 0;
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

function registrarUsuario($usuario, $contrasena_hash)
{
    try {
        $conexion = getConexionPDO();
        
        if (!$conexion) {
            return false;
        }
        
        // Hashea la contraseña con MD5
        $passMD5 = md5($contrasena_hash);
        
        // Consulta preparada para insertar usuario
        $sql = "INSERT INTO `login` (`usuario`, `contrasena_hash`) VALUES (:usuario, :hash)";
        $stmt = $conexion->prepare($sql);
        
        $stmt->execute([':usuario' => $usuario, ':hash' => $passMD5]);
        
        // Verifica si se insertó alguna fila
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

function insertarAsignatura($nombre_asignatura, $descripcion)
{
    try {
        $conexion = getConexionPDO();
        
        if (!$conexion) {
            return 0;
        }
        
        // Consulta preparada para insertar asignatura
        $sql = "INSERT INTO `asignaturas` (`nombre_asignatura`, `descripcion`) VALUES (:nombre, :descripcion)";
        $stmt = $conexion->prepare($sql);
        
        $stmt->execute([':nombre' => $nombre_asignatura, ':descripcion' => $descripcion]);
        
        // Verifica si se insertó correctamente
        return ($stmt->rowCount() > 0) ? 1 : 0;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return 0;
    }
}


function getAsignaturas()
{
    try {
        $conexion = getConexionPDO();
        
        if (!$conexion) {
            return [];
        }
        
        // Selecciona todas las asignaturas
        $sql = "SELECT * FROM `asignaturas`";
        $stmt = $conexion->query($sql);
        
        // Array para almacenar los resultados
        $asignaturas = [];
        
        // Itera sobre los resultados y los convierte en objetos
        while ($asignatura = $stmt->fetch(PDO::FETCH_OBJ)) {
            $asignaturas[] = $asignatura;
        }
        
        return $asignaturas;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return [];
    }
}


function borrarAsignaturas($id)
{
    try {
        $conexion = getConexionPDO();
        
        if (!$conexion) {
            return 0;
        }
        
        // Consulta preparada para eliminar asignatura
        $sql = "DELETE FROM `asignaturas` WHERE `id_asignatura` = :id";
        $stmt = $conexion->prepare($sql);
        
        $stmt->execute([':id' => $id]);
        
        return ($stmt->rowCount() > 0) ? 1 : 0;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return 0;
    }
}
// Comprobar login
function comprobarLogin($usuario, $contrasena_hash) {
    try {
        $conexion = getConexionPDO();
        
        if (!$conexion) {
            return false;
        }
        
        $sql = "SELECT * FROM `login` WHERE `usuario` = :usuario AND `contrasena_hash` = :hash";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':usuario' => $usuario, ':hash' => $contrasena_hash]);
        
        return ($stmt->rowCount() === 1);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

// Registrar nuevo usuario
function registroUsuario($usuario, $contrasena, $nombre, $apellido, $correo, $telefono, $rol)
{
    try {
        $conexion = getConexionPDO();
        
        if (!$conexion) {
            return ['success' => false, 'msg' => 'Error de conexión a la base de datos'];
        }
        
        // Verificar que el usuario no existe
        $sql_check = "SELECT `usuario` FROM `login` WHERE `usuario` = :usuario";
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->execute([':usuario' => $usuario]);
        
        if ($stmt_check->rowCount() > 0) {
            return ['success' => false, 'msg' => 'El usuario ya existe'];
        }
        
        // Verificar que el correo no existe
        $sql_email_check = "SELECT `correo` FROM `usuarios` WHERE `correo` = :correo";
        $stmt_email = $conexion->prepare($sql_email_check);
        $stmt_email->execute([':correo' => $correo]);
        
        if ($stmt_email->rowCount() > 0) {
            return ['success' => false, 'msg' => 'El correo ya está registrado'];
        }
        
        // Hashear la contraseña
        $contrasena_hash = md5($contrasena);
        
        // Generar ID de usuario
        $id_usuario = 'USU-' . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6));
        $codigo_usuario = 'USU-' . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6));
        
        // Iniciar transacción
        $conexion->beginTransaction();
        
        try {
            // Insertar en tabla usuarios
            $sql_usuarios = "INSERT INTO `usuarios` (`id_usuario`, `codigo_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `rol_codigo`) 
                            VALUES (:id_usuario, :codigo_usuario, :nombre, :apellido, :correo, :telefono, :rol)";
            $stmt_usuarios = $conexion->prepare($sql_usuarios);
            $stmt_usuarios->execute([
                ':id_usuario' => $id_usuario,
                ':codigo_usuario' => $codigo_usuario,
                ':nombre' => $nombre,
                ':apellido' => $apellido,
                ':correo' => $correo,
                ':telefono' => $telefono,
                ':rol' => $rol
            ]);
            
            // Insertar en tabla login
            $sql_login = "INSERT INTO `login` (`id_usuario`, `usuario`, `contrasena_hash`) 
                         VALUES (:id_usuario, :usuario, :hash)";
            $stmt_login = $conexion->prepare($sql_login);
            $stmt_login->execute([
                ':id_usuario' => $id_usuario,
                ':usuario' => $usuario,
                ':hash' => $contrasena_hash
            ]);
            
            // Si el rol es alumno, insertar en tabla alumno
            if ($rol === 'ROL-ALU') {
                $sql_alumno = "INSERT INTO `alumno` (`id_usuario`, `fecha_ingreso`) 
                              VALUES (:id_usuario, NOW())";
                $stmt_alumno = $conexion->prepare($sql_alumno);
                $stmt_alumno->execute([':id_usuario' => $id_usuario]);
            }
            // Si el rol es profesor, insertar en tabla profesor
            else if ($rol === 'ROL-PRO') {
                $sql_profesor = "INSERT INTO `profesor` (`id_usuario`, `fecha_contratacion`) 
                                VALUES (:id_usuario, NOW())";
                $stmt_profesor = $conexion->prepare($sql_profesor);
                $stmt_profesor->execute([':id_usuario' => $id_usuario]);
            }
            
            // Confirmar transacción
            $conexion->commit();
            
            return ['success' => true, 'msg' => 'Registro completado exitosamente'];
        } catch (PDOException $e) {
            $conexion->rollBack();
            return ['success' => false, 'msg' => 'Error al registrar: ' . $e->getMessage()];
        }
        
    } catch (PDOException $e) {
        return ['success' => false, 'msg' => 'Error: ' . $e->getMessage()];
    }
}
?>