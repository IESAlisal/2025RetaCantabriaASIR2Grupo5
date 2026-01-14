<?php
// backup_simple.php - Exportador Completo Simplificado
session_start();

// ========= CONFIGURACIÓN =========
$CONFIG = [
    'db_host' => 'localhost',
    'db_user' => 'root',
    'db_pass' => '',
    'db_name' => 'academia_pintura',
    'password' => '123456',
    'allow_files' => true
];

// ========= AUTENTICACIÓN =========
if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
    if (!isset($_POST['login'])) {
        echo '<form method="post">
                <h3>🔒 Backup del Sitio</h3>
                <input type="password" name="pass" placeholder="Contraseña" required>
                <input type="submit" name="login" value="Acceder">
                <a href="../panel/panel.php" class="btn-salir">Salir al menú principal</a>
              </form>';
        exit;
    }
    
    if ($_POST['pass'] === $CONFIG['password']) {
        $_SESSION['auth'] = true;
    } else {
        die('Contraseña incorrecta');
    }
}

// ========= PROCESAR ACCIÓN =========
if (isset($_GET['type'])) {
    switch ($_GET['type']) {
        case 'sql': exportSQL(); break;
        case 'json': exportJSON(); break;
        case 'zip': exportZIP(); break;
        case 'logout': session_destroy(); header('Location: ?'); exit;
    }
}

// ========= MENÚ =========
echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Exportar datos - Academia de Pintura</title>
    <link rel="stylesheet" href="../../css/estilos_unificados.css">
</head>
<body>';
echo '<h2>📦 Exportador del Sitio</h2>';
echo '<p><a href="?type=sql">📊 Exportar Base de Datos (SQL)</a></p>';
echo '<p><a href="?type=json">📁 Exportar BD + Info (JSON)</a></p>';
if ($CONFIG['allow_files']) {
    echo '<p><a href="?type=zip">🗂️ Exportar Todo (ZIP)</a></p>';
}
echo '<p><a href="?type=logout" class="logout-link">🚪 Salir</a></p>';
echo '</body></html>';
exit;

// ========= FUNCIONES =========
function exportSQL() {
    global $CONFIG;
    
    $conn = new mysqli($CONFIG['db_host'], $CONFIG['db_user'], $CONFIG['db_pass'], $CONFIG['db_name']);
    if ($conn->connect_error) die("Error BD: " . $conn->connect_error);
    
    $sql = "-- Backup SQL - " . date('Y-m-d H:i:s') . "\n\n";
    $tables = $conn->query("SHOW TABLES");
    
    while ($table = $tables->fetch_row()[0]) {
        $sql .= "-- Tabla: $table\n";
        $create = $conn->query("SHOW CREATE TABLE `$table`")->fetch_row()[1];
        $sql .= "DROP TABLE IF EXISTS `$table`;\n$create;\n\n";
        
        $data = $conn->query("SELECT * FROM `$table`");
        while ($row = $data->fetch_assoc()) {
            $values = array_map(function($v) use ($conn) {
                return $v === null ? 'NULL' : "'" . $conn->real_escape_string($v) . "'";
            }, array_values($row));
            $sql .= "INSERT INTO `$table` VALUES (" . implode(',', $values) . ");\n";
        }
        $sql .= "\n";
    }
    
    $conn->close();
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="backup_' . date('Ymd_His') . '.sql"');
    echo $sql;
    exit;
}

function exportJSON() {
    global $CONFIG;
    
    $conn = new mysqli($CONFIG['db_host'], $CONFIG['db_user'], $CONFIG['db_pass'], $CONFIG['db_name']);
    if ($conn->connect_error) die("Error BD: " . $conn->connect_error);
    
    $data = [
        'meta' => [
            'fecha' => date('Y-m-d H:i:s'),
            'sitio' => $_SERVER['HTTP_HOST'],
            'php' => phpversion()
        ],
        'bd' => []
    ];
    
    $tables = $conn->query("SHOW TABLES");
    while ($table = $tables->fetch_row()[0]) {
        $data['bd'][$table] = [
            'estructura' => $conn->query("SHOW CREATE TABLE `$table`")->fetch_row()[1],
            'datos' => []
        ];
        
        $rows = $conn->query("SELECT * FROM `$table`");
        while ($row = $rows->fetch_assoc()) {
            $data['bd'][$table]['datos'][] = $row;
        }
    }
    
    $conn->close();
    
    if ($CONFIG['allow_files']) {
        $data['archivos'] = escanearDir('.');
    }
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="export_' . date('Ymd_His') . '.json"');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

function exportZIP() {
    global $CONFIG;
    if (!class_exists('ZipArchive')) die('ZIP no disponible');
    
    $zip = new ZipArchive();
    $filename = 'full_backup_' . date('Ymd_His') . '.zip';
    
    if ($zip->open($filename, ZipArchive::CREATE) !== true) {
        die('Error creando ZIP');
    }
    
    // Base de datos
    $conn = new mysqli($CONFIG['db_host'], $CONFIG['db_user'], $CONFIG['db_pass'], $CONFIG['db_name']);
    if (!$conn->connect_error) {
        $sql = "";
        $tables = $conn->query("SHOW TABLES");
        while ($table = $tables->fetch_row()[0]) {
            $sql .= $conn->query("SHOW CREATE TABLE `$table`")->fetch_row()[1] . ";\n\n";
        }
        $zip->addFromString('database.sql', $sql);
        $conn->close();
    }
    
    // Archivos
    if ($CONFIG['allow_files']) {
        agregarArchivosZip($zip, '.');
    }
    
    $zip->addFromString('info.txt', "Backup completo\nFecha: " . date('Y-m-d H:i:s'));
    $zip->close();
    
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    readfile($filename);
    unlink($filename);
    exit;
}

function escanearDir($dir) {
    $result = [];
    $excluir = ['.', '..', basename(__FILE__)];
    
    $files = @scandir($dir);
    if (!$files) return $result;
    
    foreach ($files as $file) {
        if (in_array($file, $excluir)) continue;
        
        $path = $dir . '/' . $file;
        $result[$file] = [
            'tipo' => is_dir($path) ? 'carpeta' : 'archivo',
            'tamaño' => is_file($path) ? filesize($path) : 0,
            'modificado' => date('Y-m-d H:i:s', filemtime($path))
        ];
    }
    
    return $result;
}

function agregarArchivosZip($zip, $dir) {
    $excluir = ['.', '..', basename(__FILE__), '.git', 'backup_'];
    
    $files = @scandir($dir);
    if (!$files) return;
    
    foreach ($files as $file) {
        if (in_array($file, $excluir)) continue;
        
        $path = $dir . '/' . $file;
        $zipPath = ltrim($path, './');
        
        if (is_dir($path)) {
            agregarArchivosZip($zip, $path);
        } elseif (filesize($path) < 5242880) { // 5MB
            $content = @file_get_contents($path);
            if ($content !== false) {
                $zip->addFromString($zipPath, $content);
            }
        }
    }
}
?>