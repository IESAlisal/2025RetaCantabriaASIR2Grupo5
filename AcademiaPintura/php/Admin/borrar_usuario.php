<?php
require_once '../../funciones/funciones.php';
requireRole(['ADMIN']);

$id = $_GET['id'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar borrado
    $id_post = $_POST['id_usuario'] ?? null;
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if (!$id_post) {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            exit();
        } else {
            header('Location: usuarios.php?msg=' . urlencode('ID no proporcionado') . '&tipo=error');
            exit();
        }
    }

    try {
        $conexion = getConexionPDO();
        // No permitir borrar admins
        $check = $conexion->prepare("SELECT u.id_usuario, u.rol_codigo FROM usuarios u WHERE u.id_usuario = :id");
        $check->execute([':id' => $id_post]);
        $row = $check->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Usuario no existe']);
                exit();
            } else {
                header('Location: usuarios.php?msg=' . urlencode('Usuario no existe') . '&tipo=error');
                exit();
            }
        }
        if ($row['rol_codigo'] === 'ROL-ADM') {
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'No se puede borrar un usuario con rol ADMIN']);
                exit();
            } else {
                header('Location: usuarios.php?msg=' . urlencode('No se puede borrar un usuario con rol ADMIN') . '&tipo=error');
                exit();
            }
        }

        $del = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = :id");
        $del->execute([':id' => $id_post]);

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'redirect' => 'usuarios.php?msg=' . rawurlencode('Usuario borrado') . '&tipo=success']);
            exit();
        } else {
            header('Location: usuarios.php?msg=' . urlencode('Usuario borrado') . '&tipo=success');
            exit();
        }
    } catch (Exception $e) {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Error al borrar usuario']);
            exit();
        } else {
            header('Location: usuarios.php?msg=' . urlencode('Error al borrar usuario') . '&tipo=error');
            exit();
        }
    }
}

// Si GET, mostrar confirmación
if (!$id) {
    header('Location: usuarios.php?msg=' . urlencode('ID de usuario no proporcionado') . '&tipo=error');
    exit();
}

try {
    $conexion = getConexionPDO();
    $stmt = $conexion->prepare("SELECT id_usuario, nombre, apellido, correo, rol_codigo FROM usuarios WHERE id_usuario = :id");
    $stmt->execute([':id' => $id]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$u) {
        header('Location: usuarios.php?msg=' . urlencode('Usuario no encontrado') . '&tipo=error');
        exit();
    }
} catch (Exception $e) {
    header('Location: usuarios.php?msg=' . urlencode('Error al cargar usuario') . '&tipo=error');
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Borrar usuario</title>
    <link rel="stylesheet" href="../../css/estilos.css">
</head>
<body>
<?php include '../panel/panel.php'; ?>

<!-- Confirmación: implementación nueva -->
<div class="confirm-overlay" id="confirmOverlay" aria-hidden="false">
    <div class="confirm-modal" role="dialog" aria-labelledby="confirmTitle" aria-describedby="confirmDesc">
        <h2 id="confirmTitle">Confirmar borrado</h2>
        <p id="confirmDesc">¿Deseas borrar al usuario <strong><?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellido']); ?></strong> (<em><?php echo htmlspecialchars($u['correo']); ?></em>)? Esta acción no se puede deshacer.</p>

        <div id="confirmMsg" class="confirm-msg" style="display:none;" aria-live="polite"></div>

        <div class="confirm-actions">
            <form method="POST" action="borrar_usuario.php" id="deleteForm">
                <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($u['id_usuario']); ?>">
                <button class="btn-danger" id="confirmDeleteBtn" type="submit">Eliminar usuario</button>
            </form>
            <button class="btn-cancel" id="confirmCancelBtn">Cancelar</button>
        </div>
    </div>
</div>

<style>
    /* Nuevo overlay/modal con animación de entrada/salida (fade + scale) */
    .confirm-overlay {
        position: fixed;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(15,15,15,0.0);
        z-index: 9999;
        padding: 20px;
        pointer-events: none;
        opacity: 0;
        transition: opacity 260ms ease-in-out;
    }
    .confirm-overlay.show { opacity: 1; background: rgba(15,15,15,0.5); pointer-events: auto; }
    .confirm-overlay.hide { opacity: 0; pointer-events: none; }

    .confirm-modal {
        width: 100%;
        max-width: 640px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.18);
        padding: 20px 22px;
        transform: scale(0.98);
        opacity: 0;
        transition: transform 260ms cubic-bezier(.2,.9,.2,1), opacity 240ms ease-in-out;
    }
    .confirm-modal.show { transform: scale(1); opacity: 1; }
    .confirm-modal.hide { transform: scale(0.96); opacity: 0; }

    .confirm-modal h2 { margin: 0 0 8px; }
    .confirm-modal p { margin: 0 0 14px; color: #333; }

    .confirm-msg { margin-bottom: 12px; padding: 10px 12px; border-radius: 6px; background: #fff6f6; color: #7a1a1a; border: 1px solid #ffd6d6; }

    .confirm-actions { display: flex; gap: 10px; justify-content: flex-end; }
    .btn-danger { background: #c62828; color: #fff; border: none; padding: 9px 14px; border-radius: 6px; font-weight:600; cursor: pointer; }
    .btn-danger[disabled] { opacity: 0.7; cursor: default; }
    .btn-cancel { background: transparent; border: 1px solid #ddd; padding: 9px 12px; border-radius: 6px; cursor: pointer; }

    /* spinner pequeño en botón */
    .btn-spinner { display:inline-block; width:14px; height:14px; border-radius:50%; border:2px solid rgba(255,255,255,0.3); border-top-color: #fff; animation: spin 0.8s linear infinite; vertical-align:middle; margin-right:8px; }
    @keyframes spin { to { transform: rotate(360deg); } }

    @media (max-width:520px){ .confirm-actions { flex-direction: column-reverse; } .btn-danger,.btn-cancel{ width:100%; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const overlay = document.getElementById('confirmOverlay');
    const modal = overlay.querySelector('.confirm-modal');
    const cancelBtn = document.getElementById('confirmCancelBtn');
    const deleteForm = document.getElementById('deleteForm');
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    const msgBox = document.getElementById('confirmMsg');

    // Mostrar con animación
    requestAnimationFrame(()=>{
        overlay.classList.add('show');
        modal.classList.add('show');
    });

    function hideAndRedirect(url){
        // Añadir clase de hide para animar salida
        overlay.classList.remove('show'); overlay.classList.add('hide');
        modal.classList.remove('show'); modal.classList.add('hide');

        const onEnd = function(e){
            if (e.target !== overlay) return; // esperar evento en overlay
            overlay.removeEventListener('transitionend', onEnd);
            window.location.href = url;
        };
        overlay.addEventListener('transitionend', onEnd);
        // fallback
        setTimeout(()=> window.location.href = url, 700);
    }

    // Cancelar -> animar salida y volver a lista
    cancelBtn.addEventListener('click', function(){
        hideAndRedirect('usuarios.php');
    });

    // Evitar que clics en overlay cierren modal (previene cierres accidentales)
    overlay.addEventListener('click', function(e){
        if (e.target === overlay) {
            // no hacer nada
        }
    });

    // Envío por fetch (AJAX) con soporte a no-JS (fallback al POST normal)
    deleteForm.addEventListener('submit', async function(e){
        e.preventDefault();
        if (!confirm('¿Seguro que quieres eliminar este usuario?')) return;

        // Estado visual
        deleteBtn.disabled = true;
        const spinner = document.createElement('span'); spinner.className = 'btn-spinner';
        deleteBtn.prepend(spinner);
        msgBox.style.display = 'none'; msgBox.textContent = '';

        try {
            const resp = await fetch(deleteForm.action, {
                method: 'POST',
                body: new FormData(deleteForm),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });

            // Leer JSON si hay
            const ct = resp.headers.get('Content-Type') || '';
            let json = null;
            if (ct.indexOf('application/json') !== -1) {
                json = await resp.json().catch(()=>null);
            }

            if (json && json.success === true && json.redirect) {
                hideAndRedirect(json.redirect); return;
            }

            if (resp.ok && !json) {
                hideAndRedirect('usuarios.php'); return;
            }

            // Mostrar error devuelto
            const errMsg = (json && json.message) ? json.message : 'Error al borrar usuario.';
            msgBox.textContent = errMsg; msgBox.style.display = 'block';
            deleteBtn.disabled = false; if (spinner.parentNode) spinner.parentNode.removeChild(spinner);
        } catch (err) {
            msgBox.textContent = 'Error al conectar con el servidor.'; msgBox.style.display = 'block';
            deleteBtn.disabled = false; if (spinner.parentNode) spinner.parentNode.removeChild(spinner);
        }
    });
});
</script>

</body>
</html>