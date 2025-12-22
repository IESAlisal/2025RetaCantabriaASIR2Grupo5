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
            <summary>Cursos</summary>
            <ul>
                <li><a href="../alumno/mis_cursos.php">Mis cursos</a></li>
                <li><a href="../alumno/matricula.php">Inscribirme / Anular matrícula</a></li>
            </ul>
        </details>

        <details>
            <summary>Resultados</summary>
            <ul>
                <li><a href="../alumno/calificaciones.php">Ver calificaciones</a></li>
                <li><a href="../alumno/historial.php">Historial académico</a></li>
            </ul>
        </details>
    </div>
</div>
