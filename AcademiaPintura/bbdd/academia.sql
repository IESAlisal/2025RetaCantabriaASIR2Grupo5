
-- Estructura de tabla para la tabla rol`
CREATE TABLE IF NOT EXISTS `rol` (
    `codigo_rol` VARCHAR(10) PRIMARY KEY CHECK (`codigo_rol` IN ('ROL-PRO', 'ROL-ALU', 'ROL-ADM')),
    `nombre_rol` VARCHAR(20) NOT NULL UNIQUE CHECK (`nombre_rol` IN ('PROFESOR', 'ALUMNO', 'ADMIN'))
);

-- Estructura de tabla para la tabla `usuarios`
CREATE TABLE IF NOT EXISTS `usuarios` (
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
);

-- Estructura de tabla para la tabla `login`
CREATE TABLE IF NOT EXISTS `login` (
    `id_login` INT AUTO_INCREMENT PRIMARY KEY,
    `id_usuario` VARCHAR(20) NOT NULL UNIQUE,
    `usuario` VARCHAR(50) UNIQUE NOT NULL,
    `contrasena_hash` VARCHAR(255) NOT NULL,
    `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `ultimo_acceso` TIMESTAMP NULL,
    `estado` ENUM('Activo', 'Inactivo', 'Bloqueado') DEFAULT 'Activo',
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE
);

-- Estructura de tabla para la tabla `datos_bancarios`
CREATE TABLE IF NOT EXISTS `datos_bancarios` (
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
);

-- Estructura de tabla para la tabla `profesor`
CREATE TABLE IF NOT EXISTS `profesor` (
    `id_profesor` INT AUTO_INCREMENT PRIMARY KEY,
    `id_usuario` VARCHAR(20) NOT NULL UNIQUE,
    `fecha_contratacion` DATE NOT NULL,
    `tipo_contrato` ENUM('Tiempo Completo', 'Medio Tiempo', 'Por Horas') DEFAULT 'Tiempo Completo',
    `salario` DECIMAL(10,2),
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS `alumno` (
    `id_alumno` INT AUTO_INCREMENT PRIMARY KEY,
    `id_usuario` VARCHAR(20) NOT NULL UNIQUE,
    `fecha_ingreso` DATE NOT NULL,
    `beca` ENUM('Si', 'No') DEFAULT 'No',
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `aulas` (
    `id_aula` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo_aula` VARCHAR(20) UNIQUE NOT NULL CHECK (`codigo_aula` REGEXP '^AULA-A-[0-9]{3}$'),
    `capacidad` INT NOT NULL CHECK (`capacidad` BETWEEN 1 AND 50),
    `piso` INT CHECK (`piso` BETWEEN 1 AND 10),
    `equipamiento` TEXT,
    `estado` ENUM('Activa', 'Mantenimiento', 'Inactiva') DEFAULT 'Activa'
);


CREATE TABLE IF NOT EXISTS `asignaturas` (
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
);


CREATE TABLE IF NOT EXISTS `materiales` (
    `id_material` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo_material` VARCHAR(20) UNIQUE NOT NULL CHECK (`codigo_material` LIKE 'MAT-%'),
    `nombre_material` VARCHAR(150) NOT NULL,
    `tipo` ENUM('Libro', 'Digital', 'Equipo', 'Software', 'Otro') NOT NULL,
    `descripcion` TEXT,
    `cantidad_disponible` INT NOT NULL CHECK (`cantidad_disponible` >= 0),
    `ubicacion` VARCHAR(100),
    `estado` ENUM('Disponible', 'Prestado', 'Mantenimiento', 'Baja') DEFAULT 'Disponible',
    `fecha_adquisicion` DATE
);


CREATE TABLE IF NOT EXISTS `matricula` (
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
);


CREATE TABLE IF NOT EXISTS `auditoria_login` (
    `id_auditoria` INT AUTO_INCREMENT PRIMARY KEY,
    `id_usuario` VARCHAR(20) NOT NULL,
    `accion` ENUM('LOGIN_OK', 'LOGIN_FAIL', 'LOGOUT', 'BLOQUEADO') NOT NULL,
    `fecha_hora` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `ip_address` VARCHAR(45)
);

INSERT INTO rol (codigo_rol, nombre_rol) VALUES
('ROL-PRO', 'PROFESOR'),
('ROL-ALU', 'ALUMNO'),
('ROL-ADM', 'ADMIN');