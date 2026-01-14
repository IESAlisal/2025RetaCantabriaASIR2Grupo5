<?php
require_once '../../funciones/funciones.php';
requireRole(['ADMIN']);

// Obtiene la lista de usuarios (excluye administradores)
try {
    $conexion = getConexionPDO();
    $sql = "SELECT u.id_usuario, u.codigo_usuario, u.nombre, u.apellido, u.correo, u.telefono, u.rol_codigo, r.nombre_rol
            FROM usuarios u
            INNER JOIN rol r ON u.rol_codigo = r.codigo_rol
            WHERE u.rol_codigo <> 'ROL-ADM'
            ORDER BY u.nombre, u.apellido";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $usuarios = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar usuarios - Admin</title>
    <link rel="stylesheet" href="../../css/estilos_unificados.css">
    <!-- Estilos personalizados eliminados, ahora en el CSS unificado -->
</head>
<body>
<?php include '../panel/panel.php'; ?>

<?php
// Mostrar mensaje opcional (success / error) enviado por la URL
if (isset($_GET['msg'])) {
    $msg = htmlspecialchars($_GET['msg']);
    $tipo = isset($_GET['tipo']) && $_GET['tipo'] === 'success' ? 'success-msg' : 'error-msg';
    echo '<div class="container"><div class="' . $tipo . ' mt-2">' . $msg . '</div></div>';
}
?>

<div class="container">
    <div class="welcome">
        <h2>Gestionar usuarios</h2>
        <p>A continuación se muestran los usuarios registrados. Desde aquí puedes editar o borrar usuarios.</p>

        <table class="users">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr><td colspan="6">No hay usuarios registrados.</td></tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($u['apellido']); ?></td>
                            <td><?php echo htmlspecialchars($u['correo']); ?></td>
                            <td><?php echo htmlspecialchars($u['telefono']); ?></td>
                            <td><?php echo htmlspecialchars($u['nombre_rol']); ?></td>
                            <td class="actions">
                                <a class="small-btn" href="editar_usuario.php?id=<?php echo urlencode($u['id_usuario']); ?>">Editar</a>
                                <a class="small-btn danger" href="borrar_usuario.php?id=<?php echo urlencode($u['id_usuario']); ?>">Borrar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
</div>
</body>
</html>