<?php
// procesos/guardar_mantenimiento.php
include '../conexion.php';
session_start();

// Validar sesión por seguridad
if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: ../login.php");
    exit();
}

// 1. Recibir datos del formulario
$id_bien = $_POST['id_bien'];
$tipo_evento = $_POST['tipo_evento'];
$fecha = $_POST['fecha'];
$detalle = ucfirst(trim($_POST['detalle'])); // Primera letra mayúscula
$tecnico = strtoupper(trim($_POST['tecnico'])); // Nombre en mayúsculas
$costo = !empty($_POST['costo']) ? $_POST['costo'] : 0.00; // Si está vacío, es 0

// 2. Insertar en la tabla mantenimientos
$sql = "INSERT INTO mantenimientos (id_bien, tipo_evento, fecha_realizacion, detalle_tecnico, tecnico_responsable, costo) 
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

// i = integer, s = string, s = string, s = string, s = string, d = double (decimal)
$stmt->bind_param("issssd", $id_bien, $tipo_evento, $fecha, $detalle, $tecnico, $costo);

if ($stmt->execute()) {
    // Redirigir de vuelta a la ficha del activo
    header("Location: ../ver_activo.php?id=" . $id_bien . "&status=success");
} else {
    echo "Error al guardar el reporte: " . $conn->error;
}

$stmt->close();
$conn->close();
?>