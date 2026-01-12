<?php
include '../conexion.php';
session_start();

// Solo admin
if($_SESSION['rol'] != 'Administrador') header("Location: ../index.php");

$id = $_POST['id_usuario'];
$usuario = trim($_POST['usuario']);
$rol = $_POST['rol'];

$sql = "UPDATE usuarios_sistema SET nombre_usuario = ?, rol = ? WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $usuario, $rol, $id);

if($stmt->execute()) {
    header("Location: ../usuarios.php?status=updated");
} else {
    header("Location: ../usuarios.php?status=error");
}
?>