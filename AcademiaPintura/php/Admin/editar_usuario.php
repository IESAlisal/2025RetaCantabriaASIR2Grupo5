<?php
require_once '../../funciones/funciones.php';
requireRole(['ADMIN']);

// Obtener id por GET
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: gestionar_usuarios.php?msg=' . urlencode('ID de usuario no proporcionado') . '&tipo=error');
    exit();
}

// Cargar datos del usuario
try {
    $conexion = getConexionPDO();
    $sql = "SELECT id_usuario, codigo_usuario, nombre, apellido, correo, telefono, rol_codigo FROM usuarios WHERE id_usuario = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $id]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$u) {
        header('Location: gestionar_usuarios.php?msg=' . urlencode('Usuario no encontrado') . '&tipo=error');
        exit();
    }
} catch (Exception $e) {
    header('Location: gestionar_usuarios.php?msg=' . urlencode('Error al cargar usuario') . '&tipo=error');
    exit();
}

// Obtener lista de roles para el select
$roles = ['ROL-ADM' => 'ADMIN', 'ROL-PRO' => 'PROFESOR', 'ROL-ALU' => 'ALUMNO'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar usuario</title>
    <link rel="stylesheet" href="../../css/estilos_unificados.css">
</head>
<body>
<?php include '../panel/panel.php'; ?>

<div class="container">
    <div class="welcome">
        <h2>Editar usuario: <?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellido']); ?></h2>
        <form method="POST" action="procesar_editar_usuario.php">
            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($u['id_usuario']); ?>">

            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($u['nombre']); ?>" required>
            </div>

            <div class="form-group">
                <label>Apellido</label>
                <input type="text" name="apellido" value="<?php echo htmlspecialchars($u['apellido']); ?>" required>
            </div>

            <div class="form-group">
                <label>Correo</label>
                <input type="email" name="correo" value="<?php echo htmlspecialchars($u['correo']); ?>" required>
            </div>

            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" value="<?php echo htmlspecialchars($u['telefono']); ?>" required>
            </div>

            <div class="form-group">
                <label>Rol</label>
                <select name="rol" required>
                    <?php foreach ($roles as $code => $label): ?>
                        <option value="<?php echo $code; ?>" <?php echo $u['rol_codigo'] === $code ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button class="btn" type="submit">Guardar cambios</button>
            <a class="btn" style="background:#aaa; margin-left:8px;" href="gestionar_usuarios.php">Cancelar</a>
        </form>
    </div>
</div>

</body>
</html>