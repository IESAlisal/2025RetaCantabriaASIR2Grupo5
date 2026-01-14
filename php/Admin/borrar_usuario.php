<?php
require_once '../../funciones/funciones.php';
requireRole(['ADMIN']);

$id = $_GET['id'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_post = $_POST['id_usuario'] ?? null;

    if (!$id_post) {
        header('Location: gestionar_usuarios.php?msg=' . urlencode('ID no proporcionado') . '&tipo=error');
        exit();
    }

    try {
        $conexion = getConexionPDO();
        $check = $conexion->prepare("SELECT u.id_usuario, u.rol_codigo FROM usuarios u WHERE u.id_usuario = :id");
        $check->execute([':id' => $id_post]);
        $row = $check->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            header('Location: gestionar_usuarios.php?msg=' . urlencode('Usuario no existe') . '&tipo=error');
            exit();
        }
        
        if ($row['rol_codigo'] === 'ROL-ADM') {
            header('Location: gestionar_usuarios.php?msg=' . urlencode('No se puede borrar un usuario con rol ADMIN') . '&tipo=error');
            exit();
        }

        $del = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = :id");
        $del->execute([':id' => $id_post]);

        header('Location: gestionar_usuarios.php?msg=' . urlencode('Usuario borrado exitosamente') . '&tipo=success');
        exit();
        
    } catch (Exception $e) {
        header('Location: gestionar_usuarios.php?msg=' . urlencode('Error al borrar usuario') . '&tipo=error');
        exit();
    }
}

// Si GET, mostrar confirmación
if (!$id) {
    header('Location: gestionar_usuarios.php?msg=' . urlencode('ID de usuario no proporcionado') . '&tipo=error');
    exit();
}

try {
    $conexion = getConexionPDO();
    $stmt = $conexion->prepare("SELECT id_usuario, nombre, apellido, correo, rol_codigo FROM usuarios WHERE id_usuario = :id");
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

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Borrar usuario</title>
    <link rel="stylesheet" href="../../css/estilos_unificados.css">
    <style>
        body.modal-body {
            background: transparent !important;
            padding: 0 !important;
            margin: 0 !important;
            min-height: 100vh !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body class="modal-body">
<!-- Confirmación: implementación nueva -->
<div class="confirm-overlay" id="confirmOverlay" aria-hidden="false">
    <div class="confirm-modal" role="dialog" aria-labelledby="confirmTitle" aria-describedby="confirmDesc">
        <h2 id="confirmTitle">Confirmar borrado</h2>
        <p id="confirmDesc">¿Deseas borrar al usuario <strong><?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellido']); ?></strong> (<em><?php echo htmlspecialchars($u['correo']); ?></em>)? Esta acción no se puede deshacer.</p>

        <div id="confirmMsg" class="confirm-msg hidden" aria-live="polite"></div>

        <div class="confirm-actions">
            <form method="POST" action="borrar_usuario.php" id="deleteForm">
                <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($u['id_usuario']); ?>">
                <button class="btn-danger" id="confirmDeleteBtn" type="submit">Eliminar usuario</button>
            </form>
            <button class="btn-cancel" id="confirmCancelBtn">Cancelar</button>
        </div>
    </div>
</div>

<!-- Estilos personalizados eliminados, ahora en el CSS unificado -->

<script>
document.addEventListener('DOMContentLoaded', function(){
    const overlay = document.getElementById('confirmOverlay');
    const modal = overlay.querySelector('.confirm-modal');
    const cancelBtn = document.getElementById('confirmCancelBtn');
    const deleteForm = document.getElementById('deleteForm');
    const deleteBtn = document.getElementById('confirmDeleteBtn');

    // Mostrar modal con animación
    requestAnimationFrame(()=>{
        overlay.classList.add('show');
        modal.classList.add('show');
    });

    // Cancelar -> redirigir a gestionar usuarios
    cancelBtn.addEventListener('click', function(){
        window.location.href = 'gestionar_usuarios.php';
    });

    // Envío simple del formulario (redirección normal)
    deleteForm.addEventListener('submit', function(e){
        e.preventDefault();
        
        // Deshabilitar botón
        deleteBtn.disabled = true;
        deleteBtn.textContent = 'Eliminando...';
        
        // Enviar formulario normalmente
        deleteForm.submit();
    });
});
</script>

</body>
</html>