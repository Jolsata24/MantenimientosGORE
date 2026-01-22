<?php
// procesos/actualizar_bien.php
session_start();
include '../conexion.php';

// 1. Recibir ID y Datos Obligatorios
$id_bien = $_POST['id_bien'];
$descripcion = strtoupper(trim($_POST['descripcion']));

// --- LÓGICA CRÍTICA (NO BORRAR) ---
// Validamos que si la categoría llega vacía, no la ponga en 0.
// Si $_POST['categoria'] tiene dato, lo usamos. Si no, mantenemos la que ya tenía (o ponemos 1 por seguridad).
$categoria = !empty($_POST['categoria']) ? $_POST['categoria'] : 1; 
// ----------------------------------

// Recibir el resto de datos
$marca = strtoupper(trim($_POST['marca']));
$modelo = strtoupper(trim($_POST['modelo']));
$serie = strtoupper(trim($_POST['serie']));
$estado = $_POST['estado'];
$ubicacion = strtoupper(trim($_POST['ubicacion']));
$id_personal = !empty($_POST['id_personal']) ? $_POST['id_personal'] : NULL;

// Datos técnicos
$procesador = !empty($_POST['procesador']) ? $_POST['procesador'] : NULL;
$ram = !empty($_POST['ram']) ? $_POST['ram'] : NULL;
$disco = !empty($_POST['disco']) ? $_POST['disco'] : NULL;
$so = !empty($_POST['so']) ? $_POST['so'] : NULL;
$ip = !empty($_POST['ip']) ? $_POST['ip'] : NULL;
$mac = !empty($_POST['mac']) ? $_POST['mac'] : NULL;

// 2. Query de Actualización
$sql = "UPDATE bienes SET 
        id_categoria = ?, 
        descripcion = ?,
        color = '$color',  /* <--- AGREGAR ESTA LINEA */ 
        marca = ?, 
        modelo = ?, 
        serie = ?, 
        estado_fisico = ?, 
        ubicacion = ?, 
        id_personal = ?,
        procesador = ?, 
        ram = ?, 
        disco = ?, 
        so = ?, 
        ip = ?, 
        mac = ?
        WHERE id_bien = ?";

$stmt = $conn->prepare($sql);

// Tipos: i (int), s (string)... total 15 variables (14 campos + 1 ID al final)
$stmt->bind_param("issssssissssssi", 
    $categoria, $descripcion, $marca, $modelo, $serie, $estado, $ubicacion, $id_personal,
    $procesador, $ram, $disco, $so, $ip, $mac, 
    $id_bien // El WHERE va al final
);

if ($stmt->execute()) {
    
    // --- AQUÍ VA LA AUDITORÍA ---
    // Solo entramos aquí si el UPDATE fue exitoso
    if (file_exists('logger.php')) {
        include 'logger.php';
        $detalles = "Editó activo ID: $id_bien ($descripcion). Actualizó datos generales/técnicos.";
        registrar_auditoria($conn, 'EDITAR', $detalles);
    }
    // ---------------------------

    header("Location: ../inventario.php?status=updated");
} else {
    echo "Error al actualizar: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>