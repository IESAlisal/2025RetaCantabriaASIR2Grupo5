<?php
// Acceso restringido: solamente ADMIN puede acceder a este formulario
require_once "../../../funciones/funciones.php";
requireRole(['ADMIN']);
?>
<!DOCTYPE html>
<!-- Página de registro para la Academia de Pintura -->
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Academia de Pintura</title>
    <link rel="stylesheet" href="../../../css/estilos_unificados.css">
    <!-- Estilos personalizados eliminados, ahora en el CSS unificado -->
</head>
<body>
    <!-- Contenedor principal del formulario de registro -->
    <div class="registro-container">
        <h1>Registro - Academia de Pintura</h1>
        
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
        
        <!-- Formulario de registro que envía los datos a procesar_registro.php -->
        <form method="POST" action="procesar_registro.php">
            <!-- Fila con campos de nombre y apellido -->
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
            
            <!-- Campo de usuario con validación mínima de 4 caracteres -->
            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" required minlength="4">
            </div>
            
            <!-- Campo de correo con validación de email -->
            <div class="form-group">
                <label for="correo">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" required>
            </div>
            
            <!-- Campo de teléfono -->
            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input type="tel" id="telefono" name="telefono" required>
            </div>
            
            <!-- Fila con campos de contraseña y confirmación (mínimo 6 caracteres) -->
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
            
            <!-- Campo desplegable para seleccionar el rol (Alumno o Profesor) -->
            <div class="form-group">
                <label for="rol">Tipo de Usuario:</label>
                <select id="rol" name="rol" required>
                    <option value="">-- Seleccione un rol --</option>
                    <option value="ROL-ALU">Alumno</option>
                    <option value="ROL-PRO">Profesor</option>
                </select>
            </div>
            
            <!-- Botón para enviar el formulario de registro -->
            <button type="submit" class="registro-btn" name="registro">Registrarse</button>
            <br><br>
            <div style="text-align: center;">
    <a class="registro-btn" href="../../panel/panel.php">Salir</a>
</div>
        </form>
    
    </div>
    <!-- Cierre del contenedor y del body -->
</body>
</html>
