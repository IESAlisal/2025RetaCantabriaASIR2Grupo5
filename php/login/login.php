<!DOCTYPE html>
<!-- Página de login para la Academia de Pintura -->
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Academia de Pintura</title>
    <link rel="stylesheet" href="../../css/estilos_unificados.css">
    <!-- Estilos personalizados eliminados, ahora en el CSS unificado -->
</head>
<body class="centered-layout">
    <!-- Contenedor principal del formulario de login -->
    <div class="login-container">
        <h1>Academia de Pintura</h1>
        
        <?php
            // Verifica si existe un parámetro 'msg' en la URL (mensaje de error o éxito)
            if (isset($_GET['msg'])) {
                // Sanitiza el mensaje para evitar inyección de HTML
                $msg = htmlspecialchars($_GET['msg']);
                // Verifica el tipo de mensaje (success o error)
                $tipo = isset($_GET['tipo']) && $_GET['tipo'] === 'success' ? 'success' : 'error';
                // Asigna la clase CSS correspondiente
                $clase = $tipo === 'success' ? 'success-msg' : 'error-msg';
                // Muestra el mensaje para hacerlo visible
                echo '<div class="' . $clase . '">' . $msg . '</div>';
            }
        ?>
        
        <!-- Formulario de login que envía los datos a procesar_login.php -->
        <form method="POST" action="procesar_login.php">
            <!-- Campo para ingresar el usuario -->
            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" required>
            </div>
            
            <!-- Campo para ingresar la contraseña -->
            <div class="form-group">
                <label for="contrasena">Contraseña:</label>
                <input type="password" id="contrasena" name="contrasena" required>
            </div>
            
            <!-- Botón para enviar el formulario -->
            <button type="submit" class="login-btn">Iniciar Sesión</button>
        </form>
        
      
    </div>
    <!-- Cierre del contenedor y del body -->
</body>
</html>
