<?php
include '../conexion.php';
session_start();

if($_SESSION['rol'] != 'Administrador') header("Location: ../index.php");

$id = $_POST['id_usuario'];
$pass = $_POST['password'];

// Encriptar nueva clave
$pass_hash = password_hash($pass, PASSWORD_DEFAULT);

$sql = "UPDATE usuarios_sistema SET password_hash = ? WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $pass_hash, $id);

if($stmt->execute()) {
    header("Location: ../usuarios.php?status=updated");
} else {
    header("Location: ../usuarios.php?status=error");
}
?>