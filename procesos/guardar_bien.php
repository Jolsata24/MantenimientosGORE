<?php
// procesos/guardar_bien.php
session_start();
include '../conexion.php';

// Librería QR (opcional)
$tiene_libreria_qr = false;
if (file_exists('../phpqrcode.php')) { include '../phpqrcode.php'; $tiene_libreria_qr = true; }
elseif (file_exists('../phpqrcode/qrlib.php')) { include '../phpqrcode/qrlib.php'; $tiene_libreria_qr = true; }

// 1. Datos básicos
$codigo = trim($_POST['codigo']);
$categoria = $_POST['categoria'];
$descripcion = strtoupper(trim($_POST['descripcion']));
$marca = strtoupper(trim($_POST['marca']));
$modelo = strtoupper(trim($_POST['modelo']));
$serie = strtoupper(trim($_POST['serie']));
$estado = $_POST['estado'];
$ubicacion = !empty($_POST['ubicacion']) ? strtoupper(trim($_POST['ubicacion'])) : NULL;
$id_personal = !empty($_POST['id_personal']) ? $_POST['id_personal'] : NULL;

// 2. Datos técnicos
$procesador = !empty($_POST['procesador']) ? $_POST['procesador'] : NULL;
$ram = !empty($_POST['ram']) ? $_POST['ram'] : NULL;
$disco = !empty($_POST['disco']) ? $_POST['disco'] : NULL;
$so = !empty($_POST['so']) ? $_POST['so'] : NULL;
$ip = !empty($_POST['ip']) ? $_POST['ip'] : NULL;
$mac = !empty($_POST['mac']) ? $_POST['mac'] : NULL;

// 3. PROCESAR ARCHIVO DE BAJA
$informe_baja = NULL;
if ($estado == 'Baja' && isset($_FILES['archivo_baja']) && $_FILES['archivo_baja']['error'] == 0) {
    $dir_subida = '../docs/bajas/';
    if (!file_exists($dir_subida)) mkdir($dir_subida, 0777, true); // Crear carpeta si no existe

    $ext = pathinfo($_FILES['archivo_baja']['name'], PATHINFO_EXTENSION);
    $nombre_archivo = "BAJA_" . preg_replace('/[^A-Za-z0-9]/', '', $codigo) . "_" . time() . "." . $ext;
    
    if (move_uploaded_file($_FILES['archivo_baja']['tmp_name'], $dir_subida . $nombre_archivo)) {
        $informe_baja = $nombre_archivo;
    }
}

// 4. Validación duplicados
$check = $conn->query("SELECT id_bien FROM bienes WHERE codigo_patrimonial = '$codigo'");
if ($check->num_rows > 0) {
    echo "<script>alert('El código $codigo ya existe.'); window.history.back();</script>";
    exit();
}

// 5. Insertar
$sql = "INSERT INTO bienes (
            codigo_patrimonial, id_categoria, descripcion, marca, modelo, serie, estado_fisico, 
            procesador, ram, disco, so, ip, mac, ubicacion, id_personal, informe_baja, fecha_registro
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sissssssssssssss", 
    $codigo, $categoria, $descripcion, $marca, $modelo, $serie, $estado,
    $procesador, $ram, $disco, $so, $ip, $mac, $ubicacion, $id_personal, $informe_baja
);

if ($stmt->execute()) {
    $id_insertado = $conn->insert_id;

    // Generar QR
    if ($tiene_libreria_qr) {
        $tempDir = '../img/qr/';
        if (!file_exists($tempDir)) mkdir($tempDir, 0777, true);
        $fileName = preg_replace('/[^A-Za-z0-9\-]/', '', $codigo) . '.png';
        QRcode::png("http://" . $_SERVER['HTTP_HOST'] . "/patrimoniogore/ver_activo.php?id=" . $id_insertado, $tempDir . $fileName, QR_ECLEVEL_L, 10, 2);
    }
    
    // Auditoría
    include 'logger.php';
    $detalles = "Agregó activo: $descripcion - Cod: $codigo. Estado: $estado";
    registrar_auditoria($conn, 'CREAR', $detalles);

    header("Location: ../inventario.php?status=success");
    exit();
} else {
    echo "Error BD: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>