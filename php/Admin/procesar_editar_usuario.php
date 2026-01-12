<?php
require_once '../../funciones/funciones.php';
requireRole(['ADMIN']);

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: gestionar_usuarios.php');
    exit();
}

$id = $_POST['id_usuario'] ?? '';
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$rol = $_POST['rol'] ?? '';

$errores = [];
if (empty($nombre)) $errores[] = 'Nombre obligatorio';
if (empty($apellido)) $errores[] = 'Apellido obligatorio';
if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) $errores[] = 'Email inválido';
if (empty($telefono) || !preg_match('/^[0-9]{6,15}$/', $telefono)) $errores[] = 'Teléfono inválido';
if (!in_array($rol, ['ROL-ALU','ROL-PRO','ROL-ADM'])) $errores[] = 'Rol inválido';

if (!empty($errores)) {
    header('Location: editar_usuario.php?id=' . urlencode($id) . '&msg=' . urlencode(implode(' | ', $errores)) . '&tipo=error');
    exit();
}

try {
    $conexion = getConexionPDO();
    // Comprobar email único (excluyendo al propio usuario)
    $sql = "SELECT id_usuario FROM usuarios WHERE correo = :correo AND id_usuario != :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':correo' => $correo, ':id' => $id]);
    if ($stmt->rowCount() > 0) {
        header('Location: editar_usuario.php?id=' . urlencode($id) . '&msg=' . urlencode('El correo ya está en uso') . '&tipo=error');
        exit();
    }

    // Actualizar
    $sql = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, correo = :correo, telefono = :telefono, rol_codigo = :rol WHERE id_usuario = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':nombre'=>$nombre, ':apellido'=>$apellido, ':correo'=>$correo, ':telefono'=>$telefono, ':rol'=>$rol, ':id'=>$id]);

    header('Location: gestionar_usuarios.php?msg=' . urlencode('Usuario actualizado') . '&tipo=success');
    exit();
} catch (Exception $e) {
    header('Location: editar_usuario.php?id=' . urlencode($id) . '&msg=' . urlencode('Error actualizando usuario') . '&tipo=error');
    exit();
}
