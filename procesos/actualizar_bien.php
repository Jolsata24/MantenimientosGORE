<?php
// procesos/actualizar_bien.php - VERSION CORREGIDA Y SEGURA
session_start();
include '../conexion.php';

// 1. Recibir Datos
$id_bien = $_POST['id_bien'];
$descripcion = strtoupper(trim($_POST['descripcion']));
$categoria = !empty($_POST['categoria']) ? $_POST['categoria'] : 1; 
$marca = strtoupper(trim($_POST['marca']));
$modelo = strtoupper(trim($_POST['modelo']));
$serie = strtoupper(trim($_POST['serie']));
$estado = $_POST['estado'];
$ubicacion = strtoupper(trim($_POST['ubicacion']));
$id_personal = !empty($_POST['id_personal']) ? $_POST['id_personal'] : NULL;
$color = !empty($_POST['color']) ? $_POST['color'] : NULL;

// Specs
$procesador = !empty($_POST['procesador']) ? $_POST['procesador'] : NULL;
$ram = !empty($_POST['ram']) ? $_POST['ram'] : NULL;
$disco = !empty($_POST['disco']) ? $_POST['disco'] : NULL;
$so = !empty($_POST['so']) ? $_POST['so'] : NULL;
$ip = !empty($_POST['ip']) ? $_POST['ip'] : NULL;
$mac = !empty($_POST['mac']) ? $_POST['mac'] : NULL;

// 2. Lógica del Archivo
$nombre_archivo = null;
$subio_archivo = false;

if ($estado == 'Baja' && isset($_FILES['archivo_baja']) && $_FILES['archivo_baja']['error'] == 0) {
    $dir_subida = '../docs/bajas/';
    if (!file_exists($dir_subida)) mkdir($dir_subida, 0777, true);

    $ext = pathinfo($_FILES['archivo_baja']['name'], PATHINFO_EXTENSION);
    $nombre_archivo = "BAJA_" . $id_bien . "_" . time() . "." . $ext;
    
    if (move_uploaded_file($_FILES['archivo_baja']['tmp_name'], $dir_subida . $nombre_archivo)) {
        $subio_archivo = true;
    }
}

// 3. ACTUALIZACIÓN (Separada para evitar errores de bind_param)

if ($subio_archivo) {
    // --- OPCIÓN A: SI SUBIÓ ARCHIVO (Actualizamos todo + informe_baja) ---
    $sql = "UPDATE bienes SET 
            id_categoria = ?, 
            descripcion = ?,
            color = ?,  
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
            mac = ?,
            informe_baja = ?  /* <--- Campo extra */
            WHERE id_bien = ?";
    
    $stmt = $conn->prepare($sql);
    // 17 variables: 16 datos + 1 ID al final
    $stmt->bind_param("isssssssisssssssi", 
        $categoria, $descripcion, $color, $marca, $modelo, $serie, $estado, $ubicacion, $id_personal,
        $procesador, $ram, $disco, $so, $ip, $mac, 
        $nombre_archivo, // El archivo
        $id_bien
    );

} else {
    // --- OPCIÓN B: NO SUBIÓ ARCHIVO (Mantenemos el que estaba) ---
    $sql = "UPDATE bienes SET 
            id_categoria = ?, 
            descripcion = ?,
            color = ?,  
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
    // 16 variables: 15 datos + 1 ID al final
    $stmt->bind_param("isssssssissssssi", 
        $categoria, $descripcion, $color, $marca, $modelo, $serie, $estado, $ubicacion, $id_personal,
        $procesador, $ram, $disco, $so, $ip, $mac, 
        $id_bien
    );
}

// 4. Ejecutar y Redirigir
if ($stmt->execute()) {
    if (file_exists('logger.php')) {
        include 'logger.php';
        registrar_auditoria($conn, 'EDITAR', "Editó activo ID: $id_bien. Estado: $estado");
    }
    header("Location: ../inventario.php?status=updated");
} else {
    echo "Error al guardar: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>