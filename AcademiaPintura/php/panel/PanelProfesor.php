<?php
// PanelProfesor.php - Contenido del panel para PROFESOR
require_once dirname(__FILE__) . '/../../funciones/funciones.php';
requireRole(['PROFESOR']);
?>

<div class="welcome">
    <h2>Panel de Profesor</h2>
    <p>Acciones disponibles:</p>
    <div class="dropdown">
        <details>
            <summary>Mis asignaturas</summary>
            <ul>
                <li><a href="../profesor/mis_asignaturas.php">Ver mis asignaturas</a></li>
                <li><a href="../profesor/material.php">Subir/gestionar material</a></li>
            </ul>
        </details>

        <details>
            <summary>Evaluaciones</summary>
            <ul>
                <li><a href="../profesor/introducir_calificaciones.php">Introducir calificaciones</a></li>
                <li><a href="../profesor/listados.php">Listados de grupos</a></li>
            </ul>
        </details>
    </div>
</div>
