<!DOCTYPE html>
<!-- Página de login para la Academia de Pintura -->
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Academia de Pintura</title>
    <link rel="stylesheet" href="../../css/estilos.css">
    <!-- Estilos personalizados para la página de login -->
    <style>
        body {
            /* Centra el contenedor de login y cubre toda la pantalla con gradiente */
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .login-container {
            /* Contenedor blanco con sombra para el formulario */
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }
        .login-container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }
        .form-group input:focus {
            /* Efecto visual cuando el usuario hace click en los inputs */
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.5);
        }
        .login-btn {
            /* Botón de envío con gradiente y efecto hover */
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .login-btn:hover {
            transform: translateY(-2px);
        }
        .error-msg {
            /* Estilo para mensajes de error en rojo */
            color: #d32f2f;
            background: #ffebee;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #d32f2f;
            display: none;
        }
        .success-msg {
            /* Estilo para mensajes de éxito en verde */
            color: #2e7d32;
            background: #e8f5e9;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #4caf50;
            display: none;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
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
                // Muestra el mensaje con display:block para hacerlo visible
                echo '<div class="' . $clase . '" style="display: block;">' . $msg . '</div>';
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
