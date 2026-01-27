<?php
// procesos/eliminar_bien.php
include '../conexion.php';
session_start();

// 1. Seguridad: Solo administradores pueden entrar aquí
if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true || $_SESSION['rol'] !== 'Administrador') {
    header("Location: ../login.php");
    exit;
}

// 2. Validar que llegue un ID
if (isset($_GET['id'])) {
    $id_bien = $conn->real_escape_string($_GET['id']);
    
    // Opcional: Obtener la categoría antes de borrar para saber a dónde redirigir (1=PC, 2=Imp, 3=Mon)
    $q_cat = $conn->query("SELECT id_categoria FROM bienes WHERE id_bien = $id_bien");
    $cat = ($q_cat->num_rows > 0) ? $q_cat->fetch_assoc()['id_categoria'] : 1;

    // 3. Eliminar
    $sql = "DELETE FROM bienes WHERE id_bien = $id_bien";
    
    if ($conn->query($sql)) {
        // Redirección inteligente según categoría
        $redirect = "../inventario.php";
        if($cat == 1) $redirect = "../computacion.php";
        if($cat == 2) $redirect = "../impresora.php";
        if($cat == 3) $redirect = "../monitor.php";
        
        header("Location: $redirect?status=deleted");
    } else {
        echo "Error al eliminar: " . $conn->error;
    }
} else {
    header("Location: ../inventario.php");
}
?>