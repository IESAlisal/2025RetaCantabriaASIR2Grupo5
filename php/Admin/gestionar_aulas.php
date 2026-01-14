<?php
require_once '../../funciones/funciones.php';
requireRole(['ADMIN']);

$conexion = getConexionPDO();

// Procesar edición de aula
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_aula'])) {
    $id_aula = $_POST['id_aula'];
    $codigo_aula = $_POST['codigo_aula'];
    $capacidad = $_POST['capacidad'];
    $piso = $_POST['piso'];
    $equipamiento = $_POST['equipamiento'];
    $estado = $_POST['estado'];
    
    $sql = "UPDATE `aulas` SET 
            `codigo_aula` = ?, 
            `capacidad` = ?, 
            `piso` = ?, 
            `equipamiento` = ?, 
            `estado` = ? 
            WHERE `id_aula` = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$codigo_aula, $capacidad, $piso, $equipamiento, $estado, $id_aula]);
    
    $mensaje = "Aula actualizada correctamente.";
}

// Procesar eliminación de aula
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_aula'])) {
    $id_aula = $_POST['id_aula'];
    
    // Primero, actualizar asignaturas que usan esta aula
    $sql = "UPDATE `asignaturas` SET `id_aula` = NULL WHERE `id_aula` = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_aula]);
    
    // Luego, eliminar el aula
    $sql = "DELETE FROM `aulas` WHERE `id_aula` = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_aula]);
    
    $mensaje = "Aula eliminada correctamente.";
}

// Obtener todas las aulas con información de las asignaturas y profesores
$sql = "SELECT a.`id_aula`, a.`codigo_aula`, a.`capacidad`, a.`piso`, a.`equipamiento`, a.`estado`,
               asig.`nombre_asignatura`, p.`id_profesor`, u.`nombre`, u.`apellido`
        FROM `aulas` a
        LEFT JOIN `asignaturas` asig ON a.`id_aula` = asig.`id_aula`
        LEFT JOIN `profesor` p ON asig.`id_profesor` = p.`id_profesor`
        LEFT JOIN `usuarios` u ON p.`id_usuario` = u.`id_usuario`
        ORDER BY a.`codigo_aula`";
$result = $conexion->query($sql);
$aulas = $result->fetchAll(PDO::FETCH_ASSOC);

// Variable para saber qué aula estamos editando
$editar_id = $_GET['editar'] ?? null;
$aula_editar = null;

if ($editar_id) {
    foreach ($aulas as $aul) {
        if ($aul['id_aula'] == $editar_id) {
            $aula_editar = $aul;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Aulas - Admin</title>
    <link rel="stylesheet" href="../../css/estilos_unificados.css">
</head>
<body>
<?php include '../panel/panel.php'; ?>
<div class="container">
    <div class="welcome">
        <h2>Gestión de Aulas</h2>
    </div>

    <?php if (isset($mensaje)): ?>
        <div class="mensaje"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <?php if ($aula_editar): ?>
    <div class="form-editar">
        <h3>Editar Aula: <?php echo htmlspecialchars($aula_editar['codigo_aula']); ?></h3>
        <form method="POST">
            <input type="hidden" name="id_aula" value="<?php echo $aula_editar['id_aula']; ?>">
            <input type="hidden" name="editar_aula" value="1">
            
            <div class="form-group">
                <label for="codigo">Código del Aula:</label>
                <input type="text" id="codigo" name="codigo_aula" value="<?php echo htmlspecialchars($aula_editar['codigo_aula']); ?>" required>
            </div>

            <div class="form-group">
                <label for="capacidad">Capacidad de Alumnos:</label>
                <input type="number" id="capacidad" name="capacidad" min="1" max="50" value="<?php echo $aula_editar['capacidad']; ?>" required>
            </div>

            <div class="form-group">
                <label for="piso">Piso:</label>
                <input type="number" id="piso" name="piso" min="1" max="10" value="<?php echo $aula_editar['piso']; ?>">
            </div>

            <div class="form-group">
                <label for="equipamiento">Equipamiento:</label>
                <textarea id="equipamiento" name="equipamiento"><?php echo htmlspecialchars($aula_editar['equipamiento']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="estado">Estado:</label>
                <select id="estado" name="estado">
                    <option value="Activa" <?php echo ($aula_editar['estado'] === 'Activa') ? 'selected' : ''; ?>>Activa</option>
                    <option value="Mantenimiento" <?php echo ($aula_editar['estado'] === 'Mantenimiento') ? 'selected' : ''; ?>>Mantenimiento</option>
                    <option value="Inactiva" <?php echo ($aula_editar['estado'] === 'Inactiva') ? 'selected' : ''; ?>>Inactiva</option>
                </select>
            </div>

            <div class="form-buttons">
                <button type="submit" class="btn-guardar">Guardar Cambios</button>
                <a href="gestionar_aulas.php" class="btn-cancelar btn-link">Cancelar</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="aulas-container">
        <h3>Aulas Disponibles</h3>
        <?php if (empty($aulas)): ?>
            <p>No hay aulas registradas.</p>
        <?php else: ?>
            <?php foreach ($aulas as $aul): ?>
            <div class="aula-item">
                <div class="aula-info">
                    <h3><?php echo htmlspecialchars($aul['codigo_aula']); ?></h3>
                    <p>
                        <strong>Capacidad:</strong> 
                        <span class="capacidad-badge"><?php echo $aul['capacidad']; ?> alumnos</span>
                    </p>
                    <p><strong>Piso:</strong> <?php echo $aul['piso'] ?? 'No especificado'; ?></p>
                    <p><strong>Asignatura:</strong> 
                        <?php 
                            if ($aul['nombre_asignatura']) {
                                echo htmlspecialchars($aul['nombre_asignatura']);
                            } else {
                                echo 'Sin asignar';
                            }
                        ?>
                    </p>
                    <?php if ($aul['nombre_asignatura']): ?>
                    <p><strong>Profesor:</strong> 
                        <?php 
                            if ($aul['nombre'] && $aul['apellido']) {
                                echo htmlspecialchars($aul['nombre'] . ' ' . $aul['apellido']);
                            } else {
                                echo 'Sin asignar';
                            }
                        ?>
                    </p>
                    <?php endif; ?>
                    <p><strong>Equipamiento:</strong> <?php echo htmlspecialchars($aul['equipamiento']); ?></p>
                    <p>
                        <strong>Estado:</strong> 
                        <span class="estado-badge estado-<?php echo strtolower($aul['estado']); ?>">
                            <?php echo ucfirst($aul['estado']); ?>
                        </span>
                    </p>
                </div>
                <div class="aula-actions">
                    <a href="gestionar_aulas.php?editar=<?php echo $aul['id_aula']; ?>" class="btn btn-editar">Editar</a>
                    <form method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta aula?');">
                        <input type="hidden" name="id_aula" value="<?php echo $aul['id_aula']; ?>">
                        <input type="hidden" name="eliminar_aula" value="1">
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