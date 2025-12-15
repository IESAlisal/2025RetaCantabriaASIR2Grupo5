<?php
// ============================================================================
// ARCHIVO: funciones/funciones.php
// DESCRIPCIÓN: Contiene todas las funciones de conexión a BD y operaciones
//              de usuarios (login, registro, gestión de tablas)
// ============================================================================

// Verifica si las constantes ya están definidas para evitar redeclaraciones
if (!defined('HOST')) {
    // Incluye el archivo de configuración con las credenciales de BD
    require_once dirname(__FILE__) . '/../php/constantes/constantes.php';
}


// ============================================================================
// FUNCIÓN: getConexionPDO()
// PROPÓSITO: Establece una conexión PDO con la base de datos
// RETORNA: Objeto PDO conectado o null si hay error
// ============================================================================
function getConexionPDO()
{
    // Define opciones para la conexión PDO (especifica charset utf8)
	$opciones = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
	try
	{
	    // Crea nueva conexión PDO usando constantes HOST, DATABASE, USERNAME, PASSWORD
	    $dwes = new PDO('mysql:host='.HOST.';dbname='.DATABASE, USERNAME, PASSWORD, $opciones);
	    
	    // Configura PDO para lanzar excepciones en caso de error
	    $dwes->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    
	    // Retorna el objeto de conexión
	    return $dwes;
	}
	catch(Exception $ex)
	{
	    // Si hay error, muestra el mensaje en HTML
	    echo "<h4>{$ex->getMessage()}</h4>";
	    
	    // Retorna null indicando que la conexión falló
	    return null;
	}
}


// ============================================================================
// FUNCIÓN: getConexion_sin_bbdd_PDO()
// PROPÓSITO: Establece conexión PDO SIN especificar una base de datos
//            Se usa para crear bases de datos nuevas
// RETORNA: Objeto PDO conectado o null si hay error
// ============================================================================
function getConexion_sin_bbdd_PDO()
{
    try {
        // Define opciones: modo error con excepciones y charset utf8mb4
        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        );

        // Cadena DSN sin especificar base de datos (solo host)
        $dsn = 'mysql:host=' . HOST . ';charset=utf8mb4';

        // Crea conexión PDO
        $pdo = new PDO($dsn, USERNAME, PASSWORD, $options);
        
        // Retorna la conexión
        return $pdo;
    } catch (PDOException $ex) {
        // Si hay error, muestra mensaje sanitizado con htmlspecialchars
        echo "<p>Se ha producido un error con la conexión a la base de datos: " . htmlspecialchars($ex->getMessage()) . "</p>";
        
        // Retorna null indicando error
        return null;
    }
}

// ============================================================================
// FUNCIÓN: crearBBDD($basedatos)
// PROPÓSITO: Crea la base de datos si no existe
// PARÁMETRO: $basedatos - nombre de la BD a crear
// RETORNA: 0 si se creó correctamente o ya existía, 1 si hay error
// ============================================================================
function crearBBDD($basedatos){
    try {
        // Obtiene conexión SIN especificar BD
        $conexion = getConexion_sin_bbdd_PDO();
        
        // Si no hay conexión, retorna error
        if (!$conexion) {
            return 1;
        }
        
        // Consulta preparada para verificar si la BD ya existe
        $sql = "SELECT schema_name FROM information_schema.schemata WHERE schema_name = :basedatos";
        
        // Prepara la consulta
        $stm = $conexion->prepare($sql);
        
        // Ejecuta con el nombre de BD como parámetro
        $stm->execute([':basedatos' => $basedatos]);
        
        // Obtiene el resultado (null si no existe)
        $existe = $stm->fetch(PDO::FETCH_ASSOC);
        
        // Si la BD no existe, la crea
        if (!$existe) {
            // Consulta para crear BD
            $sql = "CREATE DATABASE `" . $basedatos . "`";
            try {
                // Ejecuta la creación
                $conexion->exec($sql);
                
                // Muestra mensaje de éxito
                echo "Base de datos $basedatos creada en MySQL por Objetos ";
                echo "<br>";
                
                // Retorna 0 (éxito)
                return 0;
            } catch (PDOException $ex) {
                // Si hay error, muestra mensaje
                echo "Error al ejecutar consulta: " . $ex->getMessage();
                
                // Retorna 1 (error)
                return 1;
            }
        }
        
        // Si la BD ya existía, retorna 0 (sin error)
        return 0;
    } catch (PDOException $e) {
        // Error general, muestra mensaje
        echo "Error: " . $e->getMessage();
        
        // Retorna 1 (error)
        return 1;
    }
}

// ============================================================================
// FUNCIÓN: ensureDefaultRoles()
// PROPÓSITO: Inserta los roles por defecto (PROFESOR, ALUMNO, ADMIN)
//            si no existen en la tabla rol
// RETORNA: true si se completa sin errores fatales
// ============================================================================
function ensureDefaultRoles()
{
    try {
        // Obtiene conexión a BD
        $conexion = getConexionPDO();
        
        // Si no hay conexión, muestra error y retorna false
        if (!$conexion) {
            echo "<!-- Error: No hay conexión a BD en ensureDefaultRoles -->";
            return false;
        }

        // Array con los roles a insertar: [codigo, nombre]
        $roles = [
            ['ROL-PRO', 'PROFESOR'],
            ['ROL-ALU', 'ALUMNO'],
            ['ROL-ADM', 'ADMIN']
        ];

        // Itera sobre cada rol
        foreach ($roles as $r) {
            try {
                // Consulta preparada para verificar si el rol ya existe
                $check_sql = "SELECT `codigo_rol` FROM `rol` WHERE `codigo_rol` = :codigo";
                
                // Prepara la consulta
                $check_stmt = $conexion->prepare($check_sql);
                
                // Ejecuta con el código de rol
                $check_stmt->execute([':codigo' => $r[0]]);
                
                // Si no existe ningún registro (rowCount() === 0)
                if ($check_stmt->rowCount() === 0) {
                    // Consulta para insertar el nuevo rol
                    $insert_sql = "INSERT INTO `rol` (`codigo_rol`, `nombre_rol`) VALUES (:codigo, :nombre)";
                    
                    // Prepara la consulta de inserción
                    $insert_stmt = $conexion->prepare($insert_sql);
                    
                    // Ejecuta con los parámetros del rol
                    $insert_stmt->execute([':codigo' => $r[0], ':nombre' => $r[1]]);
                    
                    // Muestra confirmación en HTML (invisible)
                    echo "<!-- Rol " . $r[0] . " insertado -->";
                }
            } catch (PDOException $e) {
                // Si hay error al procesar un rol, lo muestra como comentario HTML
                echo "<!-- Error insertando rol " . $r[0] . ": " . htmlspecialchars($e->getMessage()) . " -->";
            }
        }

        // Retorna true (completó sin errores fatales)
        return true;
    } catch (PDOException $ex) {
        // Error general en la función
        echo "<!-- Advertencia: Error en ensureDefaultRoles: " . htmlspecialchars($ex->getMessage()) . " -->";
        
        // Retorna false
        return false;
    }
}


// ============================================================================
// FUNCIÓN: crearTablas()
// PROPÓSITO: Crea todas las tablas de la BD si no existen
// RETORNA: 1 si todas las 11 tablas se crearon, 0 si hay error
// ============================================================================
function crearTablas()
{
    // Obtiene conexión a BD
    $conexion = getConexionPDO();
    
    // Contador de tablas creadas
    $tablasCreadas = 0;
    
    // Total de tablas esperadas
    $totalTablas = 11;
    
    // ===== TABLA 1: rol =====
    // Almacena los tipos de roles de usuarios (PROFESOR, ALUMNO, ADMIN)
    $rol = "CREATE TABLE IF NOT EXISTS `rol` (
             `codigo_rol` VARCHAR(10) PRIMARY KEY CHECK (`codigo_rol` IN ('ROL-PRO', 'ROL-ALU', 'ROL-ADM')),
             `nombre_rol` VARCHAR(20) NOT NULL UNIQUE CHECK (`nombre_rol` IN ('PROFESOR', 'ALUMNO', 'ADMIN'))
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    // Ejecuta la creación de la tabla
    if ($conexion->query($rol)) {
        // Incrementa contador
        $tablasCreadas++;
        
        // Inserta los roles por defecto si no existen
        ensureDefaultRoles();
    }

    // ===== TABLA 2: usuarios =====
    // Almacena datos básicos de todos los usuarios del sistema
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

    // Ejecuta creación de tabla usuarios
    if ($conexion->query($usuarios)) {
        $tablasCreadas++;
    }
    
    // ===== TABLA 3: login =====
    // Almacena credenciales de acceso (usuario y contraseña hash)
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

    // Ejecuta creación de tabla login
    if ($conexion->query($login)) {
        $tablasCreadas++;
    }

    // ===== TABLA 4: datos_bancarios =====
    // Almacena información bancaria de usuarios (datos encriptados)
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

    // Ejecuta creación de tabla datos_bancarios
    if ($conexion->query($datos_bancarios)) {
        $tablasCreadas++;
    }

    // ===== TABLA 5: profesor =====
    // Almacena información específica de usuarios con rol PROFESOR
    $profesor = "CREATE TABLE IF NOT EXISTS `profesor` (
                 `id_profesor` INT AUTO_INCREMENT PRIMARY KEY,
                 `id_usuario` VARCHAR(20) NOT NULL UNIQUE,
                 `fecha_contratacion` DATE NOT NULL,
                 `tipo_contrato` ENUM('Tiempo Completo', 'Medio Tiempo', 'Por Horas') DEFAULT 'Tiempo Completo',
                 `salario` DECIMAL(10,2),
                 FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE
                 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    // Ejecuta creación de tabla profesor
    if ($conexion->query($profesor)) {
        $tablasCreadas++;
    }

    // ===== TABLA 6: alumno =====
    // Almacena información específica de usuarios con rol ALUMNO
    $alumno = "CREATE TABLE IF NOT EXISTS `alumno` (
                `id_alumno` INT AUTO_INCREMENT PRIMARY KEY,
                `id_usuario` VARCHAR(20) NOT NULL UNIQUE,
                `fecha_ingreso` DATE NOT NULL,
                `beca` ENUM('Si', 'No') DEFAULT 'No',
                FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    // Ejecuta creación de tabla alumno
    if ($conexion->query($alumno)) {
        $tablasCreadas++;
    }

    // ===== TABLA 7: aulas =====
    // Almacena información de aulas disponibles en la academia
    $aulas = "CREATE TABLE IF NOT EXISTS `aulas` (
                `id_aula` INT AUTO_INCREMENT PRIMARY KEY,
                `codigo_aula` VARCHAR(20) UNIQUE NOT NULL CHECK (`codigo_aula` REGEXP '^AULA-A-[0-9]{3}$'),
                `capacidad` INT NOT NULL CHECK (`capacidad` BETWEEN 1 AND 50),
                `piso` INT CHECK (`piso` BETWEEN 1 AND 10),
                `equipamiento` TEXT,
                `estado` ENUM('Activa', 'Mantenimiento', 'Inactiva') DEFAULT 'Activa'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    // Ejecuta creación de tabla aulas
    if ($conexion->query($aulas)) {
        $tablasCreadas++;
    }

    // ===== TABLA 8: asignaturas =====
    // Almacena cursos/asignaturas que se imparten
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

    // Ejecuta creación de tabla asignaturas
    if ($conexion->query($asignaturas)) {
        $tablasCreadas++;
    }

    // ===== TABLA 9: materiales =====
    // Almacena recursos y materiales disponibles para uso en academia
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

    // Ejecuta creación de tabla materiales
    if ($conexion->query($materiales)) {
        $tablasCreadas++;
    }

    // ===== TABLA 10: matricula =====
    // Registra inscripciones de alumnos en asignaturas
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

    // Ejecuta creación de tabla matricula
    if ($conexion->query($matricula)) {
        $tablasCreadas++;
    }

    // ===== TABLA 11: auditoria_login =====
    // Registra intentos de login (éxitos y fallos) para auditoría
    $auditoria_login = "CREATE TABLE IF NOT EXISTS `auditoria_login` (
                    `id_auditoria` INT AUTO_INCREMENT PRIMARY KEY,
                    `id_usuario` VARCHAR(20) NOT NULL,
                    `accion` ENUM('LOGIN_OK', 'LOGIN_FAIL', 'LOGOUT', 'BLOQUEADO') NOT NULL,
                    `fecha_hora` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `ip_address` VARCHAR(45)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    // Ejecuta creación de tabla auditoria_login
    if ($conexion->query($auditoria_login)) {
        $tablasCreadas++;
    }

    // Cierra conexión
    $conexion = null;
    
    // Retorna 1 si se crearon todas las tablas, 0 si hay error
    return ($tablasCreadas === $totalTablas) ? 1 : 0;
}

// ============================================================================
// FUNCIÓN: registrarUsuario($usuario, $contrasena_hash)
// PROPÓSITO: Registra un usuario en la tabla login (FUNCIÓN LEGACY)
// NOTA: Esta función está deprecada, usar registroUsuario() en su lugar
// ============================================================================
function registrarUsuario($usuario, $contrasena_hash)
{
    // Intenta obtener conexión a BD
    try {
        // Obtiene conexión PDO
        $conexion = getConexionPDO();
        
        // Si no hay conexión, retorna false
        if (!$conexion) {
            return false;
        }
        
        // Hashea la contraseña con MD5 (menos seguro que bcrypt pero usado en proyecto)
        $passMD5 = md5($contrasena_hash);
        
        // Consulta preparada para insertar usuario en tabla login
        // :usuario y :hash son placeholders que se reemplazan con valores seguros
        $sql = "INSERT INTO `login` (`usuario`, `contrasena_hash`) VALUES (:usuario, :hash)";
        
        // Prepara la consulta (evita SQL injection)
        $stmt = $conexion->prepare($sql);
        
        // Ejecuta la consulta reemplazando placeholders con valores reales
        $stmt->execute([':usuario' => $usuario, ':hash' => $passMD5]);
        
        // rowCount() retorna número de filas afectadas, > 0 significa éxito
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        // Si hay error PDO, lo muestra
        echo "Error: " . $e->getMessage();
        
        // Retorna false (operación fallida)
        return false;
    }
}

// ============================================================================
// FUNCIÓN: insertarAsignatura($nombre_asignatura, $descripcion)
// PROPÓSITO: Inserta una nueva asignatura en la BD
// PARÁMETROS: $nombre_asignatura - nombre de la asignatura
//             $descripcion - descripción de la asignatura
// RETORNA: 1 si se insertó, 0 si hay error
// ============================================================================
function insertarAsignatura($nombre_asignatura, $descripcion)
{
    // Intenta insertar la asignatura
    try {
        // Obtiene conexión a BD
        $conexion = getConexionPDO();
        
        // Si no hay conexión, retorna 0 (error)
        if (!$conexion) {
            return 0;
        }
        
        // Consulta preparada para insertar en tabla asignaturas
        $sql = "INSERT INTO `asignaturas` (`nombre_asignatura`, `descripcion`) VALUES (:nombre, :descripcion)";
        
        // Prepara la consulta
        $stmt = $conexion->prepare($sql);
        
        // Ejecuta con los parámetros
        $stmt->execute([':nombre' => $nombre_asignatura, ':descripcion' => $descripcion]);
        
        // Si se insertó una fila (rowCount() > 0) retorna 1, sino retorna 0
        return ($stmt->rowCount() > 0) ? 1 : 0;
    } catch (PDOException $e) {
        // Error al insertar, muestra mensaje
        echo "Error: " . $e->getMessage();
        
        // Retorna 0 (error)
        return 0;
    }
}


// ============================================================================
// FUNCIÓN: getAsignaturas()
// PROPÓSITO: Obtiene todas las asignaturas de la BD
// RETORNA: Array de objetos con las asignaturas, o array vacío si error
// ============================================================================
function getAsignaturas()
{
    // Intenta obtener las asignaturas
    try {
        // Obtiene conexión a BD
        $conexion = getConexionPDO();
        
        // Si no hay conexión, retorna array vacío
        if (!$conexion) {
            return [];
        }
        
        // Consulta SQL para obtener todas las asignaturas
        $sql = "SELECT * FROM `asignaturas`";
        
        // Ejecuta la consulta directamente (sin parámetros)
        $stmt = $conexion->query($sql);
        
        // Array donde guardaremos los resultados
        $asignaturas = [];
        
        // Itera sobre cada fila retornada
        // PDO::FETCH_OBJ convierte cada fila a objeto
        while ($asignatura = $stmt->fetch(PDO::FETCH_OBJ)) {
            // Añade el objeto asignatura al array
            $asignaturas[] = $asignatura;
        }
        
        // Retorna el array de asignaturas
        return $asignaturas;
    } catch (PDOException $e) {
        // Error al recuperar datos, muestra mensaje
        echo "Error: " . $e->getMessage();
        
        // Retorna array vacío en caso de error
        return [];
    }
}


// ============================================================================
// FUNCIÓN: borrarAsignaturas($id)
// PROPÓSITO: Elimina una asignatura de la BD por su ID
// PARÁMETRO: $id - ID de la asignatura a eliminar
// RETORNA: 1 si se eliminó, 0 si hay error o no existe
// ============================================================================
function borrarAsignaturas($id)
{
    // Intenta eliminar la asignatura
    try {
        // Obtiene conexión a BD
        $conexion = getConexionPDO();
        
        // Si no hay conexión, retorna 0 (error)
        if (!$conexion) {
            return 0;
        }
        
        // Consulta preparada para eliminar asignatura por ID
        $sql = "DELETE FROM `asignaturas` WHERE `id_asignatura` = :id";
        
        // Prepara la consulta
        $stmt = $conexion->prepare($sql);
        
        // Ejecuta con el ID como parámetro
        $stmt->execute([':id' => $id]);
        
        // Si se eliminó una fila, retorna 1, sino retorna 0
        return ($stmt->rowCount() > 0) ? 1 : 0;
    } catch (PDOException $e) {
        // Error al eliminar, muestra mensaje
        echo "Error: " . $e->getMessage();
        
        // Retorna 0 (error)
        return 0;
    }
}

// ============================================================================
// FUNCIÓN: comprobarLogin($usuario, $contrasena_hash)
// PROPÓSITO: Verifica si existe un usuario con esa contraseña hash
// PARÁMETROS: $usuario - nombre de usuario
//             $contrasena_hash - contraseña ya hashada con MD5
// RETORNA: true si las credenciales son válidas, false si no
// ============================================================================
function comprobarLogin($usuario, $contrasena_hash) {
    // Intenta verificar las credenciales
    try {
        // Obtiene conexión a BD
        $conexion = getConexionPDO();
        
        // Si no hay conexión, retorna false
        if (!$conexion) {
            return false;
        }
        
        // Consulta preparada para buscar usuario con esa contraseña
        $sql = "SELECT * FROM `login` WHERE `usuario` = :usuario AND `contrasena_hash` = :hash";
        
        // Prepara la consulta
        $stmt = $conexion->prepare($sql);
        
        // Ejecuta con usuario y hash
        $stmt->execute([':usuario' => $usuario, ':hash' => $contrasena_hash]);
        
        // Si encontró exactamente 1 resultado (rowCount() === 1), login válido
        return ($stmt->rowCount() === 1);
    } catch (PDOException $e) {
        // Error al verificar, muestra mensaje
        echo "Error: " . $e->getMessage();
        
        // Retorna false (login inválido)
        return false;
    }
}

// ============================================================================
// FUNCIÓN: registroUsuario($usuario, $contrasena, $nombre, $apellido, $correo, $telefono, $rol)
// PROPÓSITO: Registra un nuevo usuario completo en el sistema
//            Inserta en tablas: usuarios, login, y alumno/profesor según rol
// PARÁMETROS: $usuario - nombre de usuario (único)
//             $contrasena - contraseña en texto plano (será hasheada)
//             $nombre - nombre del usuario
//             $apellido - apellido del usuario
//             $correo - email (único)
//             $telefono - número de teléfono
//             $rol - código de rol (ROL-ALU o ROL-PRO)
// RETORNA: Array con ['success' => bool, 'msg' => string]
// ============================================================================
function registroUsuario($usuario, $contrasena, $nombre, $apellido, $correo, $telefono, $rol)
{
    // Intenta realizar el registro
    try {
        // Obtiene conexión a BD
        $conexion = getConexionPDO();
        
        // Si no hay conexión, retorna error
        if (!$conexion) {
            return ['success' => false, 'msg' => 'Error de conexión a la base de datos'];
        }
        
        // ===== VALIDACIÓN 1: Verificar que el usuario no existe =====
        // Consulta preparada para buscar usuario existente
        $sql_check = "SELECT `usuario` FROM `login` WHERE `usuario` = :usuario";
        
        // Prepara y ejecuta
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->execute([':usuario' => $usuario]);
        
        // Si encontró un resultado, el usuario ya existe
        if ($stmt_check->rowCount() > 0) {
            return ['success' => false, 'msg' => 'El usuario ya existe'];
        }
        
        // ===== VALIDACIÓN 2: Verificar que el correo no existe =====
        // Consulta preparada para buscar correo existente
        $sql_email_check = "SELECT `correo` FROM `usuarios` WHERE `correo` = :correo";
        
        // Prepara y ejecuta
        $stmt_email = $conexion->prepare($sql_email_check);
        $stmt_email->execute([':correo' => $correo]);
        
        // Si encontró un resultado, el correo ya está registrado
        if ($stmt_email->rowCount() > 0) {
            return ['success' => false, 'msg' => 'El correo ya está registrado'];
        }
        
        // ===== GENERACIÓN DE IDS =====
        // Hashea la contraseña con MD5
        $contrasena_hash = md5($contrasena);
        
        // Genera ID de usuario: 'USU-' + 6 caracteres aleatorios
        // str_shuffle mezcla caracteres, substr toma los primeros 6
        $id_usuario = 'USU-' . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6));
        
        // Genera código de usuario (único para referencia)
        $codigo_usuario = 'USU-' . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6));
        
        // ===== INICIAR TRANSACCIÓN =====
        // Una transacción asegura que todas las inserciones éxito o ninguna
        // Si hay error en una inserción, se revierten todas
        $conexion->beginTransaction();
        
        // Intenta insertar en múltiples tablas
        try {
            // ===== INSERCIÓN 1: Tabla usuarios =====
            // Inserta datos básicos del usuario
            $sql_usuarios = "INSERT INTO `usuarios` (`id_usuario`, `codigo_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `rol_codigo`) 
                            VALUES (:id_usuario, :codigo_usuario, :nombre, :apellido, :correo, :telefono, :rol)";
            
            // Prepara la consulta
            $stmt_usuarios = $conexion->prepare($sql_usuarios);
            
            // Ejecuta con todos los parámetros
            $stmt_usuarios->execute([
                ':id_usuario' => $id_usuario,
                ':codigo_usuario' => $codigo_usuario,
                ':nombre' => $nombre,
                ':apellido' => $apellido,
                ':correo' => $correo,
                ':telefono' => $telefono,
                ':rol' => $rol
            ]);
            
            // ===== INSERCIÓN 2: Tabla login =====
            // Inserta credenciales de acceso
            $sql_login = "INSERT INTO `login` (`id_usuario`, `usuario`, `contrasena_hash`) 
                         VALUES (:id_usuario, :usuario, :hash)";
            
            // Prepara la consulta
            $stmt_login = $conexion->prepare($sql_login);
            
            // Ejecuta con credenciales
            $stmt_login->execute([
                ':id_usuario' => $id_usuario,
                ':usuario' => $usuario,
                ':hash' => $contrasena_hash
            ]);
            
            // ===== INSERCIÓN 3: Tabla alumno O profesor (según rol) =====
            // Si el rol es ALUMNO
            if ($rol === 'ROL-ALU') {
                // Consulta para insertar en tabla alumno
                $sql_alumno = "INSERT INTO `alumno` (`id_usuario`, `fecha_ingreso`) 
                              VALUES (:id_usuario, NOW())";
                
                // Prepara y ejecuta
                $stmt_alumno = $conexion->prepare($sql_alumno);
                $stmt_alumno->execute([':id_usuario' => $id_usuario]);
            }
            // Si el rol es PROFESOR
            else if ($rol === 'ROL-PRO') {
                // Consulta para insertar en tabla profesor
                $sql_profesor = "INSERT INTO `profesor` (`id_usuario`, `fecha_contratacion`) 
                                VALUES (:id_usuario, NOW())";
                
                // Prepara y ejecuta
                $stmt_profesor = $conexion->prepare($sql_profesor);
                $stmt_profesor->execute([':id_usuario' => $id_usuario]);
            }
            
            // ===== CONFIRMAR TRANSACCIÓN =====
            // Si llegó aquí sin errores, confirma todos los cambios
            $conexion->commit();
            
            // Retorna éxito
            return ['success' => true, 'msg' => 'Registro completado exitosamente'];
        } catch (PDOException $e) {
            // Si hay error, revierte todas las inserciones
            $conexion->rollBack();
            
            // Retorna error con detalles
            return ['success' => false, 'msg' => 'Error al registrar: ' . $e->getMessage()];
        }
        
    } catch (PDOException $e) {
        // Error general, retorna con mensaje de error
        return ['success' => false, 'msg' => 'Error: ' . $e->getMessage()];
    }
}
?>