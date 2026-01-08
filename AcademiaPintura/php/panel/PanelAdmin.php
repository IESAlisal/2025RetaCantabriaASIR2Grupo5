<?php
// PanelAdmin.php - Contenido del panel para ADMIN
require_once dirname(__FILE__) . '/../../funciones/funciones.php';
requireRole(['ADMIN']);
?>

<div class="welcome">
    <h2>Panel de Administración</h2>
    <p>Acciones disponibles:</p>
    <div class="dropdown">
        <details>
            <summary>Usuarios</summary>
            <ul>
                <li><a href="../Admin/registro/registro.php">Crear usuario</a></li>
                <li><a href="../admin/gestionar_usuarios.php">Gestionar usuarios</a></li>
            </ul>
        </details>

        <details>
            <summary>Asignaturas</summary>
            <ul>
                <li><a href="../admin/crear_asignatura.php">Crear asignatura</a></li>
                <li><a href="../admin/gestionar_asignaturas.php">Gestionar asignaturas</a></li>
            </ul>
        </details>

        <details>
            <summary>Aulas</summary>
            <ul>
                <li><a href="../admin/gestionar_aulas.php">Gestionar aulas</a></li>
            </ul>
        </details>

        <details>
            <summary>Exportar datos</summary>
            <ul>
                <li><a href="../admin/exportar_datos.php">Exportar datos</a></li>
            </ul>
        </details>
    </div>
</div>
