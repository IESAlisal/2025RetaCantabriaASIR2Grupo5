<?php
// PanelAlumno.php - Contenido del panel para ALUMNO
require_once dirname(__FILE__) . '/../../funciones/funciones.php';
requireRole(['ALUMNO']);
?>

<div class="welcome">
    <h2>Panel de Alumno</h2>
    <p>Acciones disponibles:</p>
    <div class="dropdown">
        <details>
            <summary>Asignaturas</summary>
            <ul>
                <li><a href="../alumno/ver_mis_asignaturas.php">Ver Mis Asignaturas</a></li> 
            </ul>
        </details>
    </div>
</div>
