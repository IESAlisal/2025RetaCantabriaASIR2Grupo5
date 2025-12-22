<?php
require_once '../../funciones/funciones.php';
requireRole(['ADMIN']);

// Ejemplo placeholder: lista de usuarios (vacía por ahora)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar usuarios - Admin</title>
    <link rel="stylesheet" href="../../css/estilos.css">
</head>
<body>
<?php include '../panel/panel.php'; ?>
<div class="container">
    <div class="welcome">
        <h2>Gestionar usuarios</h2>
        <p>Esta página permitirá listar, editar y eliminar usuarios (placeholder).</p>
    </div>
</div>
</body>
</html>