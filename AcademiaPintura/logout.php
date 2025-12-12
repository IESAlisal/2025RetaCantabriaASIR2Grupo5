<?php
// logout.php - Cerrar sesión
session_start();
session_unset();
session_destroy();
header("Location: ./php/login/login.php");
exit();
?>