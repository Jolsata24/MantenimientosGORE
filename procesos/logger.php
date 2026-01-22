<?php
// procesos/logger.php

function registrar_auditoria($conn, $accion, $detalle) {
    // Verificamos si hay sesión activa para sacar el usuario
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    $usuario = isset($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : 'Sistema';
    $rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : 'Desconocido';
    $ip = $_SERVER['REMOTE_ADDR']; // Capturamos la IP desde donde se hizo

    // Preparamos la consulta
    $sql = "INSERT INTO auditoria (usuario, rol, accion, detalle, ip, fecha) VALUES (?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $usuario, $rol, $accion, $detalle, $ip);
    $stmt->execute();
    $stmt->close();
}
?>