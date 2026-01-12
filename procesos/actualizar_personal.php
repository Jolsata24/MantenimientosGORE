<?php
// procesos/actualizar_personal.php
include '../conexion.php';

// 1. Recibir datos
$id = $_POST['id_personal'];
$nombres = strtoupper(trim($_POST['nombres']));
$apellidos = strtoupper(trim($_POST['apellidos']));
$cargo = strtoupper(trim($_POST['cargo']));
$id_area = $_POST['id_area']; // Este es un número (int)
$telefono = trim($_POST['telefono']);
$estado = $_POST['estado']; // Este es texto ("Activo"/"Inactivo")

// 2. Consulta UPDATE
$sql = "UPDATE personal SET 
        nombres = ?, 
        apellidos = ?, 
        cargo = ?, 
        id_area = ?, 
        telefono = ?, 
        estado = ? 
        WHERE id_personal = ?";

$stmt = $conn->prepare($sql);

/* EXPLICACIÓN DE LAS LETRAS (TIPOS DE DATOS):
   s = string (texto: nombres, apellidos, cargo)
   i = integer (número: id_area)
   s = string (texto: telefono - IMPORTANTE: el teléfono se trata como texto para no perder el 0 inicial)
   s = string (texto: estado - AQUÍ ESTABA EL ERROR, antes era 'i')
   i = integer (número: id_personal del WHERE)
   
   Total: "sssissi"
*/
$stmt->bind_param("sssissi", $nombres, $apellidos, $cargo, $id_area, $telefono, $estado, $id);

if ($stmt->execute()) {
    header("Location: ../personal.php?status=updated");
} else {
    echo "Error al actualizar: " . $conn->error;
}

$stmt->close();
$conn->close();
?>