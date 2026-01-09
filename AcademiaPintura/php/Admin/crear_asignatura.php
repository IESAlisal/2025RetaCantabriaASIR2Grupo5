<?php
session_start();
require_once '../../funciones/funciones.php';
requireRole(['ADMIN']);

$conexion = getConexionPDO();

// Mensajes de sesión
$mensaje = '';
$tipo_mensaje = '';

if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    $tipo_mensaje = $_SESSION['tipo_mensaje'];
    unset($_SESSION['mensaje']);
    unset($_SESSION['tipo_mensaje']);
}

// Procesar creación de asignatura
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_asignatura'])) {
    $nombre_asignatura = trim($_POST['nombre_asignatura']);
    $horas_semanales = $_POST['horas_semanales'];
    $descripcion = trim($_POST['descripcion']);
    $id_profesor = $_POST['id_profesor'] ?: null;
    $estado = $_POST['estado'];
    
    // Validaciones
    $errores = [];
    
    if (empty($nombre_asignatura)) {
        $errores[] = "El nombre de la asignatura es obligatorio";
    }
    
    if (!is_numeric($horas_semanales) || $horas_semanales <= 0) {
        $errores[] = "Las horas semanales deben ser un número positivo";
    }
    
    if (empty($estado)) {
        $errores[] = "El estado es obligatorio";
    }
    
    if (empty($errores)) {
        try {
            // Generar código automático
            $sql = "SELECT COUNT(*) as total FROM `asignaturas`";
            $stmt = $conexion->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $numero = str_pad($result['total'] + 1, 3, '0', STR_PAD_LEFT);
            $codigo_asignatura = "ASIG-" . $numero;
            
            // Insertar la nueva asignatura
            $sql = "INSERT INTO `asignaturas` 
                    (`codigo_asignatura`, `nombre_asignatura`, `horas_semanales`, 
                     `descripcion`, `id_profesor`, `estado`)
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                $codigo_asignatura, 
                $nombre_asignatura, 
                $horas_semanales, 
                $descripcion, 
                $id_profesor, 
                $estado
            ]);
            
            $_SESSION['mensaje'] = "Asignatura creada exitosamente con código: <strong>" . $codigo_asignatura . "</strong>";
            $_SESSION['tipo_mensaje'] = "success";
            
            // Redirigir para evitar reenvío del formulario
            header("Location: crear_asignatura.php");
            exit();
            
        } catch(PDOException $e) {
            $_SESSION['mensaje'] = "Error al crear la asignatura: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "error";
            header("Location: crear_asignatura.php");
            exit();
        }
    } else {
        $mensaje = implode("<br>", $errores);
        $tipo_mensaje = "error";
    }
}

// Obtener profesores para el dropdown
$profesores = [];
try {
    // Opción 1: Si tienes la columna correo_electronico
    // $sql = "SELECT p.`id_profesor`, u.`nombre`, u.`apellido`, u.`correo_electronico` as email
    
    // Opción 2: Si tienes la columna email
    // $sql = "SELECT p.`id_profesor`, u.`nombre`, u.`apellido`, u.`email`
    
    // Opción 3: Sin email (solo nombre y apellido)
    $sql = "SELECT p.`id_profesor`, u.`nombre`, u.`apellido`
            FROM `profesor` p
            JOIN `usuarios` u ON p.`id_usuario` = u.`id_usuario`
            WHERE u.`estado` = 'Activo'
            ORDER BY u.`nombre`, u.`apellido`";
    
    $result = $conexion->query($sql);
    $profesores = $result->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $mensaje = "Error al cargar los profesores: " . $e->getMessage();
    $tipo_mensaje = "error";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Crear Nueva Asignatura - Academia de Pintura</title>
    <link rel="stylesheet" href="../../css/estilos_unificados.css">
</head>
<body>
    <?php include '../panel/panel.php'; ?>
    
    <div class="container">
        <a href="asignaturas.php" class="back-link">
            ← Volver a Asignaturas
        </a>
        
        <div class="header">
            <h1> Crear Nueva Asignatura</h1>
            <p>Complete el formulario para registrar una nueva asignatura en el sistema</p>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <div class="info-box">
            <h4>Información importante</h4>
            <ul>
                <li>El código de asignatura se generará automáticamente</li>
                <li>Los campos marcados con <span class="required">*</span> son obligatorios</li>
                <li>Puede asignar un profesor ahora o hacerlo más tarde</li>
                <li>Las horas semanales pueden incluir decimales (ej: 2.5 horas)</li>
            </ul>
        </div>
        
        <div class="card">
            <form method="POST" action="">
                <input type="hidden" name="crear_asignatura" value="1">
                
                <div class="form-section">
                    <h3>Información básica</h3>
                    
                    <div class="form-group">
                        <label for="nombre_asignatura" class="form-label">
                            Nombre de la asignatura <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="nombre_asignatura" 
                               name="nombre_asignatura" 
                               class="form-control" 
                               value="<?php echo isset($_POST['nombre_asignatura']) ? htmlspecialchars($_POST['nombre_asignatura']) : ''; ?>" 
                               required 
                               placeholder="Ej: Cerámica, Pintura al óleo, Dibujo artístico">
                        <span class="form-text">Nombre completo y descriptivo de la asignatura</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="horas_semanales" class="form-label">
                            Horas semanales <span class="required">*</span>
                        </label>
                        <input type="number" 
                               id="horas_semanales" 
                               name="horas_semanales" 
                               class="form-control" 
                               value="<?php echo isset($_POST['horas_semanales']) ? $_POST['horas_semanales'] : '4'; ?>" 
                               min="0.5" 
                               max="40" 
                               step="0.5" 
                               required 
                               placeholder="Ej: 4">
                        <span class="form-text">Número de horas por semana (puede usar decimales: 2.5, 3.0, etc.)</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion" class="form-label">
                            Descripción
                        </label>
                        <textarea id="descripcion" 
                                  name="descripcion" 
                                  class="form-control" 
                                  placeholder="Describa los contenidos, objetivos y metodología de la asignatura..."><?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?></textarea>
                        <span class="form-text">Esta descripción será visible para los estudiantes</span>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Asignación de profesor</h3>
                    
                    <div class="form-group">
                        <label for="id_profesor" class="form-label">
                            Profesor responsable
                        </label>
                        <div class="profesor-select">
                            <select id="id_profesor" name="id_profesor" class="form-control">
                                <option value="">-- Seleccionar profesor --</option>
                                <?php foreach ($profesores as $prof): ?>
                                    <option value="<?php echo $prof['id_profesor']; ?>"
                                        <?php echo (isset($_POST['id_profesor']) && $_POST['id_profesor'] == $prof['id_profesor']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($prof['nombre'] . ' ' . $prof['apellido']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <span class="form-text">Puede dejar sin asignar y designar un profesor más tarde</span>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Estado de la asignatura</h3>
                    
                    <div class="form-group">
                        <div class="radio-group">
                            <div class="radio-option">
                                <input type="radio" 
                                       id="estado_activa" 
                                       name="estado" 
                                       value="Activa" 
                                       <?php echo (!isset($_POST['estado']) || $_POST['estado'] == 'Activa') ? 'checked' : ''; ?>
                                       required>
                                <label for="estado_activa">
                                    <strong>Activa</strong><br>
                                    <small>Disponible para matrícula</small>
                                </label>
                            </div>
                            
                            <div class="radio-option">
                                <input type="radio" 
                                       id="estado_retirada" 
                                       name="estado" 
                                       value="Retirada" 
                                       <?php echo (isset($_POST['estado']) && $_POST['estado'] == 'Retirada') ? 'checked' : ''; ?>>
                                <label for="estado_retirada">
                                    <strong>Retirada</strong><br>
                                    <small>No disponible temporalmente</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="reset" class="btn btn-secondary">
                        Limpiar formulario
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span style="margin-right: 8px;">✅</span>
                        Crear asignatura
                    </button>
                </div>
            </form>
        </div>
    <script>
        // Validación en tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const nombreInput = document.getElementById('nombre_asignatura');
            const horasInput = document.getElementById('horas_semanales');
            
            // Validar nombre mientras se escribe
            nombreInput.addEventListener('input', function() {
                if (this.value.trim().length < 3) {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }
            });
            
            // Validar horas
            horasInput.addEventListener('input', function() {
                const value = parseFloat(this.value);
                if (isNaN(value) || value < 0.5 || value > 40) {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }
            });
            
            // Confirmación antes de enviar
            form.addEventListener('submit', function(e) {
                const nombre = nombreInput.value.trim();
                const horas = parseFloat(horasInput.value);
                
                if (nombre.length < 3) {
                    e.preventDefault();
                    alert('El nombre de la asignatura debe tener al menos 3 caracteres');
                    nombreInput.focus();
                    return false;
                }
                
                if (isNaN(horas) || horas < 0.5 || horas > 40) {
                    e.preventDefault();
                    alert('Las horas semanales deben estar entre 0.5 y 40 horas');
                    horasInput.focus();
                    return false;
                }
                
                return confirm('¿Está seguro de crear esta asignatura?');
            });
            
            // Auto-ajustar textarea
            const textarea = document.getElementById('descripcion');
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        });
    </script>
</body>
</html>