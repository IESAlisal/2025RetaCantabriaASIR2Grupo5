<?php
require_once '../../funciones/funciones.php';
requireRole(['ADMIN']);

$conexion = getConexionPDO();

// Procesar edición de asignatura
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_asignatura'])) {
    $id_asignatura = $_POST['id_asignatura'];
    $nombre_asignatura = $_POST['nombre_asignatura'];
    $horas_semanales = $_POST['horas_semanales'];
    $descripcion = $_POST['descripcion'];
    $id_profesor = $_POST['id_profesor'];
    $estado = $_POST['estado'];
    
    $sql = "UPDATE `asignaturas` SET 
            `nombre_asignatura` = ?, 
            `horas_semanales` = ?, 
            `descripcion` = ?, 
            `id_profesor` = ?, 
            `estado` = ? 
            WHERE `id_asignatura` = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$nombre_asignatura, $horas_semanales, $descripcion, $id_profesor, $estado, $id_asignatura]);
    
    $mensaje = "Asignatura actualizada correctamente.";
}

// Procesar eliminación de asignatura
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_asignatura'])) {
    $id_asignatura = $_POST['id_asignatura'];
    
    // Primero, eliminar matrículas asociadas
    $sql = "DELETE FROM `matricula` WHERE `id_asignatura` = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_asignatura]);
    
    // Luego, eliminar la asignatura
    $sql = "DELETE FROM `asignaturas` WHERE `id_asignatura` = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_asignatura]);
    
    $mensaje = "Asignatura eliminada correctamente.";
}

// Obtener todas las asignaturas con información del profesor
$sql = "SELECT a.`id_asignatura`, a.`codigo_asignatura`, a.`nombre_asignatura`, a.`horas_semanales`, 
               a.`descripcion`, a.`estado`, p.`id_profesor`, u.`nombre`, u.`apellido`
        FROM `asignaturas` a
        LEFT JOIN `profesor` p ON a.`id_profesor` = p.`id_profesor`
        LEFT JOIN `usuarios` u ON p.`id_usuario` = u.`id_usuario`
        ORDER BY a.`nombre_asignatura`";
$result = $conexion->query($sql);
$asignaturas = $result->fetchAll(PDO::FETCH_ASSOC);

// Obtener profesores para el dropdown
$sql = "SELECT p.`id_profesor`, u.`nombre`, u.`apellido` 
        FROM `profesor` p
        JOIN `usuarios` u ON p.`id_usuario` = u.`id_usuario`
        ORDER BY u.`nombre`";
$result = $conexion->query($sql);
$profesores = $result->fetchAll(PDO::FETCH_ASSOC);

// Variable para saber qué asignatura estamos editando
$editar_id = $_GET['editar'] ?? null;
$asignatura_editar = null;

if ($editar_id) {
    foreach ($asignaturas as $asig) {
        if ($asig['id_asignatura'] == $editar_id) {
            $asignatura_editar = $asig;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignaturas - Admin</title>
    <link rel="stylesheet" href="../../css/estilos.css">
    <style>
        .asignaturas-container {
            margin: 20px auto;
            max-width: 1000px;
        }
        .asignatura-item {
            background: #f5f5f5;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .asignatura-info h3 {
            margin: 0;
            color: #333;
        }
        .asignatura-info p {
            margin: 5px 0;
            font-size: 0.9em;
            color: #666;
        }
        .asignatura-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            text-decoration: none;
            display: inline-block;
        }
        .btn-editar {
            background-color: #4CAF50;
            color: white;
        }
        .btn-editar:hover {
            background-color: #45a049;
        }
        .btn-eliminar {
            background-color: #f44336;
            color: white;
        }
        .btn-eliminar:hover {
            background-color: #da190b;
        }
        .form-editar {
            background: #fff;
            padding: 20px;
            margin: 20px 0;
            border: 2px solid #4CAF50;
            border-radius: 5px;
        }
        .form-editar h3 {
            margin-top: 0;
            color: #4CAF50;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .form-buttons {
            display: flex;
            gap: 10px;
        }
        .btn-guardar, .btn-cancelar {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }
        .btn-guardar {
            background-color: #4CAF50;
            color: white;
        }
        .btn-guardar:hover {
            background-color: #45a049;
        }
        .btn-cancelar {
            background-color: #999;
            color: white;
        }
        .btn-cancelar:hover {
            background-color: #777;
        }
        .mensaje {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .estado-badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 0.85em;
            font-weight: bold;
        }
        .estado-activa {
            background-color: #4CAF50;
            color: white;
        }
        .estado-electiva {
            background-color: #2196F3;
            color: white;
        }
        .estado-retirada {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
<?php include '../panel/panel.php'; ?>
<div class="container">
    <div class="welcome">
        <h2>Gestión de Asignaturas</h2>
    </div>

    <?php if (isset($mensaje)): ?>
        <div class="mensaje"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <?php if ($asignatura_editar): ?>
    <div class="form-editar">
        <h3>Editar Asignatura: <?php echo htmlspecialchars($asignatura_editar['nombre_asignatura']); ?></h3>
        <form method="POST">
            <input type="hidden" name="id_asignatura" value="<?php echo $asignatura_editar['id_asignatura']; ?>">
            <input type="hidden" name="editar_asignatura" value="1">
            
            <div class="form-group">
                <label for="nombre">Nombre de la Asignatura:</label>
                <input type="text" id="nombre" name="nombre_asignatura" value="<?php echo htmlspecialchars($asignatura_editar['nombre_asignatura']); ?>" required>
            </div>

            <div class="form-group">
                <label for="horas">Horas Semanales:</label>
                <input type="number" id="horas" name="horas_semanales" min="1" max="20" value="<?php echo $asignatura_editar['horas_semanales']; ?>" required>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion"><?php echo htmlspecialchars($asignatura_editar['descripcion']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="profesor">Profesor:</label>
                <select id="profesor" name="id_profesor">
                    <?php foreach ($profesores as $prof): ?>
                        <option value="<?php echo $prof['id_profesor']; ?>" 
                            <?php echo ($prof['id_profesor'] == $asignatura_editar['id_profesor']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($prof['nombre'] . ' ' . $prof['apellido']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="estado">Estado:</label>
                <select id="estado" name="estado">
                    <option value="Activa" <?php echo ($asignatura_editar['estado'] === 'Activa') ? 'selected' : ''; ?>>Activa</option>
                    <option value="Electiva" <?php echo ($asignatura_editar['estado'] === 'Electiva') ? 'selected' : ''; ?>>Electiva</option>
                    <option value="Retirada" <?php echo ($asignatura_editar['estado'] === 'Retirada') ? 'selected' : ''; ?>>Retirada</option>
                </select>
            </div>

            <div class="form-buttons">
                <button type="submit" class="btn-guardar">Guardar Cambios</button>
                <a href="gestionar_asignaturas.php" class="btn-cancelar" style="text-decoration: none; text-align: center;">Cancelar</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="asignaturas-container">
        <h3>Asignaturas Disponibles</h3>
        <?php if (empty($asignaturas)): ?>
            <p>No hay asignaturas registradas.</p>
        <?php else: ?>
            <?php foreach ($asignaturas as $asig): ?>
            <div class="asignatura-item">
                <div class="asignatura-info">
                    <h3><?php echo htmlspecialchars($asig['nombre_asignatura']); ?></h3>
                    <p><strong>Código:</strong> <?php echo htmlspecialchars($asig['codigo_asignatura']); ?></p>
                    <p><strong>Horas Semanales:</strong> <?php echo $asig['horas_semanales']; ?></p>
                    <p><strong>Profesor:</strong> 
                        <?php 
                            if ($asig['nombre'] && $asig['apellido']) {
                                echo htmlspecialchars($asig['nombre'] . ' ' . $asig['apellido']);
                            } else {
                                echo 'Sin asignar';
                            }
                        ?>
                    </p>
                    <p><strong>Descripción:</strong> <?php echo htmlspecialchars($asig['descripcion']); ?></p>
                    <p>
                        <strong>Estado:</strong> 
                        <span class="estado-badge estado-<?php echo strtolower($asig['estado']); ?>">
                            <?php echo ucfirst($asig['estado']); ?>
                        </span>
                    </p>
                </div>
                <div class="asignatura-actions">
                    <a href="gestionar_asignaturas.php?editar=<?php echo $asig['id_asignatura']; ?>" class="btn btn-editar">Editar</a>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta asignatura?');">
                        <input type="hidden" name="id_asignatura" value="<?php echo $asig['id_asignatura']; ?>">
                        <input type="hidden" name="eliminar_asignatura" value="1">
                        <button type="submit" class="btn btn-eliminar">Eliminar</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>