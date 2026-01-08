<?php
session_start();

// ============================================
// CONFIGURACIÓN DE CONEXIÓN A BASE DE DATOS
// ============================================
function getConexionSimple() {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    
    try {
        // Primero conectar sin base de datos
        $conexion = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Obtener lista de bases de datos
        $stmt = $conexion->query("SHOW DATABASES");
        $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Buscar la base de datos de la academia
        $possible_databases = [
            'academia_pintura',
            'registro_asignaturas', 
            'academia',
            'pintura_db',
            'artes_db'
        ];
        
        $selected_db = '';
        foreach ($possible_databases as $db) {
            if (in_array($db, $databases)) {
                $selected_db = $db;
                break;
            }
        }
        
        // Si no encontramos, usar la primera disponible (excepto mysql, information_schema, etc.)
        if (empty($selected_db)) {
            $system_dbs = ['mysql', 'information_schema', 'performance_schema', 'sys', 'test'];
            foreach ($databases as $db) {
                if (!in_array($db, $system_dbs)) {
                    $selected_db = $db;
                    break;
                }
            }
        }
        
        if (empty($selected_db)) {
            die("No se encontró ninguna base de datos disponible.");
        }
        
        // Ahora conectar a la base de datos específica
        $conexion = new PDO("mysql:host=$host;dbname=$selected_db;charset=utf8mb4", $username, $password);
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Guardar nombre de BD en sesión para uso posterior
        $_SESSION['selected_database'] = $selected_db;
        
        return $conexion;
        
    } catch(PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

// ============================================
// VERIFICAR SI EL USUARIO ESTÁ LOGUEADO
// ============================================
function verificarSesion() {
    // Modo simple: Si no hay sesión, mostrar aviso pero permitir continuar
    // Para producción, deberías tener tu propio sistema de login
    if (!isset($_SESSION['logged_in'])) {
        // Solo mostrar aviso, no bloquear
        $_SESSION['warning'] = "⚠️ Modo de prueba: No hay verificación de usuario activa.";
        $_SESSION['logged_in'] = true; // Simular login para pruebas
    }
    return true;
}

// ============================================
// INICIALIZAR
// ============================================
verificarSesion();
$conexion = getConexionSimple();

// Mostrar advertencia si existe
if (isset($_SESSION['warning'])) {
    $warning_message = $_SESSION['warning'];
    unset($_SESSION['warning']);
}

// Obtener todas las tablas
$tablas = [];
$error = '';
$database_name = $_SESSION['selected_database'] ?? 'Base de datos';

try {
    $sql = "SHOW TABLES";
    $stmt = $conexion->query($sql);
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tablas)) {
        $error = "No se encontraron tablas en la base de datos.";
    }
    
} catch(PDOException $e) {
    $error = "Error al obtener las tablas: " . $e->getMessage();
}

// Procesar la exportación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exportar'])) {
    $tabla_seleccionada = $_POST['tabla'] ?? 'todos';
    
    try {
        if ($tabla_seleccionada === 'todos') {
            exportarBackupCompleto($conexion, $tablas, $database_name);
        } else {
            exportarTablaCSV($conexion, $tabla_seleccionada);
        }
    } catch(Exception $e) {
        $_SESSION['error_exportacion'] = "Error en la exportación: " . $e->getMessage();
        header("Location: exportar_datos.php");
        exit();
    }
}

// ============================================
// FUNCIONES DE EXPORTACIÓN
// ============================================
function exportarTablaCSV($conexion, $tabla) {
    // Obtener datos
    $sql = "SELECT * FROM `$tabla`";
    $stmt = $conexion->query($sql);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($datos)) {
        // Si está vacía, aún así exportar encabezados
        // Primero obtener estructura
        $sql_structure = "DESCRIBE `$tabla`";
        $stmt_structure = $conexion->query($sql_structure);
        $structure = $stmt_structure->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($structure)) {
            throw new Exception("La tabla $tabla no existe o está vacía");
        }
        
        $datos = [array_fill_keys($structure, '')];
    }
    
    // Configurar headers para descarga
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $tabla . '_' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Crear output
    $output = fopen('php://output', 'w');
    
    // Escribir BOM para UTF-8 en Excel
    fwrite($output, "\xEF\xBB\xBF");
    
    // Escribir encabezados
    fputcsv($output, array_keys($datos[0]), ';');
    
    // Escribir datos (si hay)
    if (!empty($datos[0][array_keys($datos[0])[0]])) { // Si no es solo estructura vacía
        foreach ($datos as $fila) {
            // Escapar valores nulos
            foreach ($fila as $key => $value) {
                if (is_null($value)) {
                    $fila[$key] = '';
                }
            }
            fputcsv($output, $fila, ';');
        }
    }
    
    fclose($output);
    exit();
}

function exportarBackupCompleto($conexion, $tablas, $database_name) {
    // Verificar si ZipArchive está disponible
    $zip_available = class_exists('ZipArchive');
    
    if (!$zip_available) {
        // Si no hay ZIP, exportar la primera tabla
        if (!empty($tablas)) {
            exportarTablaCSV($conexion, $tablas[0]);
        } else {
            throw new Exception("No hay tablas para exportar");
        }
        return;
    }
    
    // Crear archivo ZIP
    $zip = new ZipArchive();
    $zip_filename = 'backup_' . $database_name . '_' . date('Y-m-d_H-i-s') . '.zip';
    
    // Crear directorio temporal en el mismo directorio
    $temp_dir = __DIR__ . '/temp_backup_' . uniqid();
    
    if (!mkdir($temp_dir, 0755, true)) {
        throw new Exception("No se pudo crear directorio temporal. Verifique permisos de escritura.");
    }
    
    $exported_tables = 0;
    $error_tables = [];
    
    foreach ($tablas as $tabla) {
        try {
            // Obtener datos
            $sql = "SELECT * FROM `$tabla`";
            $stmt = $conexion->query($sql);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $csv_file = $temp_dir . '/' . $tabla . '.csv';
            $file = fopen($csv_file, 'w');
            
            if (!$file) {
                throw new Exception("No se pudo crear archivo: $csv_file");
            }
            
            // Escribir BOM para UTF-8 en Excel
            fwrite($file, "\xEF\xBB\xBF");
            
            if (!empty($datos)) {
                // Escribir encabezados
                fputcsv($file, array_keys($datos[0]), ';');
                
                // Escribir datos
                foreach ($datos as $fila) {
                    // Escapar valores nulos
                    foreach ($fila as $key => $value) {
                        if (is_null($value)) {
                            $fila[$key] = '';
                        }
                    }
                    fputcsv($file, $fila, ';');
                }
                $exported_tables++;
            } else {
                // Tabla vacía - solo estructura
                $sql_structure = "DESCRIBE `$tabla`";
                $stmt_structure = $conexion->query($sql_structure);
                $structure = $stmt_structure->fetchAll(PDO::FETCH_COLUMN);
                
                if (!empty($structure)) {
                    fputcsv($file, $structure, ';');
                    $exported_tables++;
                } else {
                    fwrite($file, "Tabla vacía o sin estructura definida");
                    $error_tables[] = $tabla;
                }
            }
            
            fclose($file);
            
        } catch(Exception $e) {
            // Registrar error y continuar
            $error_tables[] = $tabla . " - " . $e->getMessage();
            error_log("Error exportando tabla $tabla: " . $e->getMessage());
        }
    }
    
    // Crear archivo README
    $readme = $temp_dir . '/README.txt';
    $readme_content = "BACKUP COMPLETO - Sistema de Academia\n" .
                     "=============================================\n" .
                     "Base de datos: " . $database_name . "\n" .
                     "Fecha generación: " . date('Y-m-d H:i:s') . "\n" .
                     "Total de tablas: " . count($tablas) . "\n" .
                     "Tablas exportadas: " . $exported_tables . "\n" .
                     "Tablas con error: " . count($error_tables) . "\n" .
                     "Formato: CSV (delimitador ;, codificación UTF-8)\n" .
                     "=============================================\n\n" .
                     "INSTRUCCIONES:\n" .
                     "1. Descomprima el archivo ZIP\n" .
                     "2. Cada tabla está en un archivo CSV separado\n" .
                     "3. Puede abrir los CSV con Excel, Google Sheets, etc.\n" .
                     "4. El delimitador es punto y coma (;)\n" .
                     "5. La codificación es UTF-8\n\n";
    
    if (!empty($error_tables)) {
        $readme_content .= "TABLAS CON ERRORES:\n";
        foreach ($error_tables as $error_table) {
            $readme_content .= "- " . $error_table . "\n";
        }
        $readme_content .= "\n";
    }
    
    $readme_content .= "LISTA DE TABLAS EXPORTADAS:\n";
    foreach ($tablas as $tabla) {
        if (!in_array($tabla, $error_tables)) {
            $readme_content .= "- " . $tabla . "\n";
        }
    }
    
    file_put_contents($readme, $readme_content);
    
    // Comprimir todo
    $zip_path = $temp_dir . '/' . $zip_filename;
    if ($zip->open($zip_path, ZipArchive::CREATE) !== TRUE) {
        throw new Exception("No se pudo crear archivo ZIP");
    }
    
    // Agregar archivos al ZIP
    $files = scandir($temp_dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && $file != $zip_filename) {
            $full_path = $temp_dir . '/' . $file;
            if (is_file($full_path)) {
                $zip->addFile($full_path, $file);
            }
        }
    }
    
    if (!$zip->close()) {
        throw new Exception("Error al cerrar archivo ZIP");
    }
    
    // Verificar que el archivo ZIP existe
    if (!file_exists($zip_path)) {
        throw new Exception("El archivo ZIP no se creó correctamente");
    }
    
    // Enviar archivo ZIP al navegador
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
    header('Content-Length: ' . filesize($zip_path));
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Limpiar buffer de salida
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    readfile($zip_path);
    
    // Limpiar archivos temporales (opcional - comentado para debug)
    // array_map('unlink', glob("$temp_dir/*"));
    // @rmdir($temp_dir);
    
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exportar Datos - Academia de Pintura</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .export-container {
            width: 100%;
            max-width: 1000px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .export-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .export-header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .export-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .export-form {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #333;
            font-size: 1.1rem;
        }
        
        select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5eb;
            border-radius: 8px;
            font-size: 16px;
            background: white;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        
        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .table-list {
            max-height: 400px;
            overflow-y: auto;
            border: 2px solid #e1e5eb;
            border-radius: 8px;
            margin-top: 10px;
        }
        
        .table-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }
        
        .table-item:hover {
            background: #f8f9fa;
            padding-left: 20px;
        }
        
        .table-item.selected {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            font-weight: 600;
        }
        
        .table-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .table-name {
            font-weight: 600;
            color: #333;
        }
        
        .table-count {
            font-size: 0.9rem;
            color: #666;
        }
        
        .table-icon {
            font-size: 1.5rem;
            color: #667eea;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        
        .alert-warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        
        .alert-error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        
        .alert-success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        
        .info-box {
            background: #f8f9fa;
            border: 2px solid #e1e5eb;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        
        .info-box h3 {
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-box ul {
            padding-left: 20px;
            color: #555;
        }
        
        .info-box li {
            margin-bottom: 8px;
            line-height: 1.5;
        }
        
        .btn-export {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 16px 40px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
        }
        
        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-export:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 25px 0;
        }
        
        .stat-card {
            background: white;
            border: 2px solid #e1e5eb;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .database-info {
            background: #e7f3ff;
            border: 2px solid #b3d4fc;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .export-container {
                margin: 10px;
            }
            
            .export-header {
                padding: 20px;
            }
            
            .export-header h1 {
                font-size: 1.8rem;
                flex-direction: column;
                gap: 10px;
            }
            
            .export-form {
                padding: 20px;
            }
            
            .stats {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="export-container">
        <div class="export-header">
            <h1>
                <span>📊</span>
                Exportar Datos del Sistema
            </h1>
            <p>Exporte información completa de su academia</p>
        </div>
        
        <div class="export-form">
            <?php if (isset($warning_message)): ?>
                <div class="alert alert-warning">
                    <?php echo $warning_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_exportacion'])): ?>
                <div class="alert alert-error">
                    <?php 
                    echo $_SESSION['error_exportacion'];
                    unset($_SESSION['error_exportacion']);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="database-info">
                🔍 Base de datos detectada: <strong><?php echo htmlspecialchars($database_name); ?></strong>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="tabla">Seleccionar tabla para exportar:</label>
                    <select name="tabla" id="tabla" required <?php echo empty($tablas) ? 'disabled' : ''; ?>>
                        <?php if (!empty($tablas)): ?>
                            <option value="todos">📦 TODAS las tablas (Backup completo)</option>
                            <?php foreach ($tablas as $tabla): ?>
                                <option value="<?php echo htmlspecialchars($tabla); ?>">
                                    📋 <?php echo htmlspecialchars($tabla); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">No hay tablas disponibles</option>
                        <?php endif; ?>
                    </select>
                </div>
                
                <?php if (!empty($tablas)): ?>
                <div class="form-group">
                    <label>Tablas disponibles (<?php echo count($tablas); ?>):</label>
                    <div class="table-list">
                        <?php foreach ($tablas as $tabla): ?>
                            <div class="table-item" onclick="selectTable('<?php echo $tabla; ?>')" id="table-<?php echo $tabla; ?>">
                                <div class="table-info">
                                    <div class="table-name"><?php echo htmlspecialchars($tabla); ?></div>
                                    <?php
                                    try {
                                        $sql_count = "SELECT COUNT(*) as count FROM `$tabla`";
                                        $stmt_count = $conexion->query($sql_count);
                                        $count = $stmt_count->fetch(PDO::FETCH_ASSOC)['count'];
                                        echo '<div class="table-count">' . number_format($count) . ' registros</div>';
                                    } catch(Exception $e) {
                                        echo '<div class="table-count">Error al contar</div>';
                                    }
                                    ?>
                                </div>
                                <div class="table-icon">📋</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="stats">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($tablas); ?></div>
                        <div class="stat-label">Tablas</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo class_exists('ZipArchive') ? '✓' : '✗'; ?></div>
                        <div class="stat-label">ZIP</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">CSV</div>
                        <div class="stat-label">Formato</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $database_name; ?></div>
                        <div class="stat-label">Base de datos</div>
                    </div>
                </div>
                
                <div class="info-box">
                    <h3><span>💡</span> Información importante</h3>
                    <ul>
                        <li><strong>Base de datos:</strong> <?php echo htmlspecialchars($database_name); ?></li>
                        <li><strong>Formato:</strong> CSV compatible con Excel y Google Sheets</li>
                        <li><strong>Delimitador:</strong> Punto y coma (;) para mejor compatibilidad</li>
                        <li><strong>Backup completo:</strong> <?php echo class_exists('ZipArchive') ? 'Genera archivo ZIP' : 'ZIP no disponible - Solo exportación individual'; ?></li>
                        <li><strong>UTF-8:</strong> Incluye codificación correcta para caracteres especiales</li>
                        <li><strong>Tablas vacías:</strong> Se exportarán con solo los encabezados</li>
                    </ul>
                </div>
                
                <button type="submit" name="exportar" value="1" class="btn-export">
                    <span>📥</span>
                    Generar y Descargar Exportación
                </button>
                
                <?php else: ?>
                <div class="alert alert-warning">
                    <p>No se encontraron tablas en la base de datos.</p>
                    <p>Verifique que la base de datos exista y contenga tablas.</p>
                </div>
                <button type="button" class="btn-export" disabled>
                    <span>⚠️</span>
                    No hay datos para exportar
                </button>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <script>
        function selectTable(tableName) {
            // Actualizar select
            document.getElementById('tabla').value = tableName;
            
            // Quitar selección anterior
            document.querySelectorAll('.table-item').forEach(item => {
                item.classList.remove('selected');
            });
            
            // Agregar selección nueva
            document.getElementById('table-' + tableName).classList.add('selected');
        }
        
        // Configurar eventos
        document.addEventListener('DOMContentLoaded', function() {
            const tablaSelect = document.getElementById('tabla');
            const form = document.querySelector('form');
            
            // Seleccionar primera tabla por defecto
            if (tablaSelect.options.length > 1 && tablaSelect.value !== 'todos') {
                const firstTable = document.querySelector('.table-item');
                if (firstTable) {
                    const firstTableName = firstTable.id.replace('table-', '');
                    selectTable(firstTableName);
                }
            }
            
            // Mostrar confirmación para exportación completa
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (tablaSelect.value === 'todos') {
                        if (!confirm('⚠️ ¿Exportar TODAS las tablas?\n\nEsto generará un archivo ZIP con ' + 
                                    <?php echo count($tablas); ?> + ' tablas de la base de datos.\n\n¿Desea continuar?')) {
                            e.preventDefault();
                            return false;
                        }
                    } else {
                        if (!confirm('¿Exportar la tabla: ' + tablaSelect.value + '?')) {
                            e.preventDefault();
                            return false;
                        }
                    }
                    
                    // Mostrar mensaje de carga
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<span>⏳</span> Generando archivo...';
                    submitBtn.disabled = true;
                    
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 5000);
                });
            }
            
            // Resaltar tabla seleccionada en el select
            tablaSelect.addEventListener('change', function() {
                if (this.value !== 'todos') {
                    selectTable(this.value);
                } else {
                    // Quitar todas las selecciones
                    document.querySelectorAll('.table-item').forEach(item => {
                        item.classList.remove('selected');
                    });
                }
            });
        });
    </script>
</body>
</html>