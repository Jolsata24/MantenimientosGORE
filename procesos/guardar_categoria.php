<?php
// procesos/guardar_categoria.php
include '../conexion.php';
session_start();

if (!isset($_SESSION['logeado'])) { header("Location: ../login.php"); exit; }

$nombre = ucfirst(trim($_POST['nombre']));
$color = $_POST['color'];
$icono = $_POST['icono'];

$sql = "INSERT INTO categorias (nombre, icono, color, estado) VALUES ('$nombre', '$icono', '$color', 'Activo')";

if ($conn->query($sql)) {
    
    // --- AUDITORÍA NUEVA ---
    if (file_exists('logger.php')) {
        include 'logger.php';
        $detalles = "Creó nueva categoría de activos: $nombre";
        registrar_auditoria($conn, 'SISTEMA', $detalles);
    }
    // -----------------------

    header("Location: ../inventario.php?status=cat_created");
} else {
    echo "Error: " . $conn->error;
}
?>