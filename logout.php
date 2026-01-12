<?php
session_start();
session_destroy(); // Destruye todos los datos de sesión
header("Location: login.php");
exit();
?>