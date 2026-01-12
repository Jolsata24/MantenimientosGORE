<?php
// procesos/validar_login.php

// 1. Iniciar Sesión de PHP (Obligatorio al principio)
session_start();

include '../conexion.php';

// 2. Recibir datos del formulario (Login.php)
// Usamos trim() para borrar espacios accidentales al inicio o final
$usuario = trim($_POST['usuario']); 
$password_ingresado = $_POST['password'];

// 3. Buscar al usuario en la base de datos
// Usamos Prepared Statements para evitar Hackeos (SQL Injection)
$sql = "SELECT id_usuario, nombre_usuario, password_hash, rol FROM usuarios_sistema WHERE nombre_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

// 4. Verificar si el usuario existe
if ($fila = $resultado->fetch_assoc()) {
    
    // 5. Verificar la contraseña (HASH)
    // password_verify compara la clave escrita ('123') contra el hash encriptado ('$2y$10$...')
    if (password_verify($password_ingresado, $fila['password_hash'])) {
        
        // ¡LOGIN EXITOSO!
        // Guardamos datos clave en la sesión para usarlos en todo el sistema
        $_SESSION['id_usuario'] = $fila['id_usuario'];
        $_SESSION['nombre'] = $fila['nombre_usuario'];
        $_SESSION['rol'] = $fila['rol'];
        $_SESSION['logeado'] = true;

        // Redirigir al Dashboard
        header("Location: ../index.php");
        exit();

    } else {
        // Contraseña incorrecta
        header("Location: ../login.php?error=1");
        exit();
    }

} else {
    // Usuario no encontrado
    header("Location: ../login.php?error=1");
    exit();
}

$stmt->close();
$conn->close();
?>