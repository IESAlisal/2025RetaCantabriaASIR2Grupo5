<?php
// ============================================================================
// ARCHIVO: php/constantes/constantes.php
// DESCRIPCIÓN: Archivo de configuración con credenciales y constantes
//              IMPORTANTE: NO subir a control de versión en producción
// ACCESO: Incluir en todos los archivos que necesiten conexión a BD
// ============================================================================

// ===== CONFIGURACIÓN DE CONEXIÓN A BASE DE DATOS =====

// HOST: Dirección del servidor MySQL
// "localhost" = servidor local (mismo donde corre PHP)
// En producción, cambiar a IP o dominio del servidor BD
define("HOST", "localhost");

// USERNAME: Usuario de BD con permisos para esta BD
// Por defecto en Laragon/XAMPP es "root"
// En producción, usar usuario con permisos limitados
define("USERNAME", "root");

// PASSWORD: Contraseña del usuario de BD
// Vacía ("") por defecto en Laragon/XAMPP
// En producción, usar contraseña fuerte
define("PASSWORD", "");

// DATABASE: Nombre de la base de datos a utilizar
// Se crea automáticamente en primera ejecución
define("DATABASE", "academia_pintura");

// ============================================================================
// NOTAS DE SEGURIDAD
// ============================================================================
// 1. NUNCA subir este archivo a repositorio público
// 2. En producción, cambiar contraseñas predeterminadas
// 3. Usar usuario BD con permisos mínimos necesarios
// 4. NO usar "root" en producción
// 5. Considerar usar variables de entorno (.env) en lugar de define()
// ============================================================================
?>
