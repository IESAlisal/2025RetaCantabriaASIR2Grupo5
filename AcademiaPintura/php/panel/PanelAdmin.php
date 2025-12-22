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
                <li><a href="../admin/usuarios.php">Gestionar usuarios</a></li>
                <li><a href="../admin/roles.php">Gestionar roles</a></li>
            </ul>
        </details>

        <details>
            <summary>Asignaturas</summary>
            <ul>
                <li><a href="../admin/asignaturas.php">Crear/editar asignaturas</a></li>
                <li><a href="../admin/aulas.php">Gestionar aulas</a></li>
            </ul>
        </details>

        <details>
            <summary>Reportes</summary>
            <ul>
                <li><a href="../admin/reportes.php">Ver reportes</a></li>
                <li><a href="../admin/exportar.php">Exportar datos</a></li>
            </ul>
        </details>
    </div>
</div>
