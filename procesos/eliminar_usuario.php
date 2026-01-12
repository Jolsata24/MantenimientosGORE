<?php
include '../conexion.php';
session_start();

// Validaciones de Seguridad
if($_SESSION['rol'] != 'Administrador') { header("Location: ../index.php"); exit(); }

$id_a_borrar = isset($_GET['id']) ? intval($_GET['id']) : 0;
$id_actual = $_SESSION['id_usuario'];

// 1. No borrar al ID 1 (Super Admin)
if($id_a_borrar == 1) {
    header("Location: ../usuarios.php?error=superadmin");
    exit();
}

// 2. No borrarse a sí mismo
if($id_a_borrar == $id_actual) {
    header("Location: ../usuarios.php?error=self");
    exit();
}

$sql = "DELETE FROM usuarios_sistema WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_a_borrar);

if($stmt->execute()) {
    header("Location: ../usuarios.php?status=deleted");
}
?>