<?php
// procesos/guardar_usuario.php
include '../conexion.php';

// Recibir datos del formulario
$usuario = $_POST['usuario'];
$password_raw = $_POST['password']; // Contraseña tal cual la escribió
$rol = $_POST['rol'];

// 1. ENCRIPTAR LA CONTRASEÑA (Súper Importante)
// PASSWORD_DEFAULT usa el algoritmo Bcrypt, que es el estándar actual
$password_hash = password_hash($password_raw, PASSWORD_DEFAULT);

// 2. Insertar en la Base de Datos
$sql = "INSERT INTO usuarios_sistema (nombre_usuario, password_hash, rol) VALUES (?, ?, ?)";

// Usamos Prepared Statements para evitar Hackeos (SQL Injection)
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $usuario, $password_hash, $rol);

if ($stmt->execute()) {
    // Redirigir de vuelta a la lista con éxito
    header("Location: ../usuarios.php?status=success");
} else {
    echo "Error al registrar: " . $conn->error;
}

$stmt->close();
$conn->close();
?>