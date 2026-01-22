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
$tipo = $_POST['tipo']; 
$fecha = $_POST['fecha']; 
$descripcion = ucfirst(trim($_POST['descripcion'])); 
$tecnico = trim($_POST['tecnico']); 
$costo = !empty($_POST['costo']) ? $_POST['costo'] : 0.00;

// 2. Insertar en la tabla mantenimientos
$sql = "INSERT INTO mantenimientos (id_bien, tipo_mantenimiento, fecha_mantenimiento, descripcion, tecnico_responsable, costo, fecha_registro) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("issssd", $id_bien, $tipo, $fecha, $descripcion, $tecnico, $costo);

// --- CORRECCIÓN: UN SOLO BLOQUE DE EJECUCIÓN ---
if ($stmt->execute()) {
    
    // A. Auditoría (Se registra ANTES de irse)
    // Verificamos si existe para evitar errores fatales si borraras el archivo
    if (file_exists('logger.php')) {
        include 'logger.php';
        $detalles = "Registró mantenimiento ($tipo) al activo ID: $id_bien. Técnico: $tecnico";
        registrar_auditoria($conn, 'MANTENIMIENTO', $detalles);
    }
    
    // B. Redirigir (Esto detiene el script, por eso la auditoría va antes)
    header("Location: ../ver_activo.php?id=" . $id_bien . "&status=saved");
    exit(); 
} else {
    echo "Error al guardar el reporte: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>