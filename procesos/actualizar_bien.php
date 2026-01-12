<?php
// procesos/actualizar_bien.php
include '../conexion.php';

// 1. Recibir datos
$id_bien = $_POST['id_bien'];
$categoria = $_POST['categoria'];
$descripcion = strtoupper(trim($_POST['descripcion']));
$marca = strtoupper(trim($_POST['marca']));
$modelo = strtoupper(trim($_POST['modelo']));
$serie = strtoupper(trim($_POST['serie']));
$estado = $_POST['estado'];

// RECIBIMOS EL ID DEL PERSONAL (Si viene vacío, lo convertimos a NULL para la base de datos)
$id_personal = !empty($_POST['id_personal']) ? $_POST['id_personal'] : NULL;

// 2. Consulta UPDATE Actualizada
$sql = "UPDATE bienes SET 
        id_categoria = ?, 
        descripcion = ?, 
        marca = ?, 
        modelo = ?, 
        serie = ?, 
        estado_fisico = ?,
        id_personal = ?  /* NUEVO CAMPO */
        WHERE id_bien = ?";

$stmt = $conn->prepare($sql);

// "isssssii" -> El penúltimo es 'i' (integer) o null para el id_personal
$stmt->bind_param("isssssii", $categoria, $descripcion, $marca, $modelo, $serie, $estado, $id_personal, $id_bien);

if ($stmt->execute()) {
    header("Location: ../inventario.php?status=updated");
} else {
    echo "Error al actualizar: " . $conn->error;
}

$stmt->close();
$conn->close();
?>