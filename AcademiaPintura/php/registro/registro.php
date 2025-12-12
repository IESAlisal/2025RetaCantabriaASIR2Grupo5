<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Academia de Pintura</title>
    <link rel="stylesheet" href="../../css/estilos.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        .registro-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
        }
        .registro-container h1 {
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
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.5);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .registro-btn {
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
        .registro-btn:hover {
            transform: translateY(-2px);
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .error-msg {
            color: #d32f2f;
            background: #ffebee;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #d32f2f;
            display: none;
        }
        .success-msg {
            color: #2e7d32;
            background: #e8f5e9;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #4caf50;
            display: none;
        }
    </style>
</head>
<body>
    <div class="registro-container">
        <h1>Registro - Academia de Pintura</h1>
        
        <?php
            if (isset($_GET['msg'])) {
                $msg = htmlspecialchars($_GET['msg']);
                $tipo = isset($_GET['tipo']) && $_GET['tipo'] === 'success' ? 'success' : 'error';
                $clase = $tipo === 'success' ? 'success-msg' : 'error-msg';
                echo '<div class="' . $clase . '" style="display: block;">' . $msg . '</div>';
            }
        ?>
        
        <form method="POST" action="procesar_registro.php">
            <div class="form-row">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" required minlength="4">
            </div>
            
            <div class="form-group">
                <label for="correo">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" required>
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input type="tel" id="telefono" name="telefono" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="contrasena">Contraseña:</label>
                    <input type="password" id="contrasena" name="contrasena" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="contrasena_conf">Confirmar Contraseña:</label>
                    <input type="password" id="contrasena_conf" name="contrasena_conf" required minlength="6">
                </div>
            </div>
            
            <div class="form-group">
                <label for="rol">Tipo de Usuario:</label>
                <select id="rol" name="rol" required>
                    <option value="">-- Seleccione un rol --</option>
                    <option value="ROL-ALU">Alumno</option>
                    <option value="ROL-PRO">Profesor</option>
                </select>
            </div>
            
            <button type="submit" class="registro-btn">Registrarse</button>
        </form>
        
        <div class="login-link">
            ¿Ya tienes cuenta? <a href="../login/login.php">Inicia sesión aquí</a>
        </div>
    </div>
</body>
</html>
