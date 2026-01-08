<?php
// CONEXIÓN DIRECTA
$host = 'localhost';
$dbname = 'tu_base_de_datos'; // CAMBIA ESTO
$username = 'root';
$password = '';

try {
    $conexion = new PDO("mysql:host=$host;'academia_pintura'=$dbname;charset=utf8mb4", $username, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Obtener tablas
$tablas = $conexion->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

if (isset($_GET['exportar'])) {
    $tabla = $_GET['tabla'];
    
    // Exportar tabla individual
    $datos = $conexion->query("SELECT * FROM $tabla")->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $tabla . '.csv"');
    
    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF");
    
    if (!empty($datos)) {
        fputcsv($output, array_keys($datos[0]), ';');
        foreach ($datos as $fila) {
            fputcsv($output, $fila, ';');
        }
    }
    
    fclose($output);
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Exportar Datos Simple</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h1 { color: #333; }
        .tabla { 
            background: #f5f5f5; 
            padding: 10px; 
            margin: 5px 0; 
            border-radius: 5px;
            cursor: pointer;
        }
        .tabla:hover { background: #e0e0e0; }
    </style>
</head>
<body>
    <h1>Exportar Datos</h1>
    <p>Haz clic en una tabla para exportarla:</p>
    
    <?php foreach ($tablas as $tabla): ?>
        <div class="tabla" onclick="exportarTabla('<?php echo $tabla; ?>')">
            📋 <?php echo $tabla; ?>
        </div>
    <?php endforeach; ?>
    
    <script>
        function exportarTabla(tabla) {
            if (confirm('¿Exportar tabla: ' + tabla + '?')) {
                window.location.href = '?exportar=1&tabla=' + tabla;
            }
        }
    </script>
</body>
</html>