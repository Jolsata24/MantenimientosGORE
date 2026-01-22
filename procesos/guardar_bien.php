<?php
// procesos/guardar_bien.php
session_start();
include '../conexion.php';

// Verificación de librería QR (Igual que tenías)
$tiene_libreria_qr = false;
if (file_exists('../phpqrcode.php')) {
    include '../phpqrcode.php';
    $tiene_libreria_qr = true;
} elseif (file_exists('../phpqrcode/qrlib.php')) {
    include '../phpqrcode/qrlib.php';
    $tiene_libreria_qr = true;
}

// 1. Recibir datos básicos
$codigo = trim($_POST['codigo']);
$categoria = $_POST['categoria'];
$descripcion = strtoupper(trim($_POST['descripcion']));
$marca = strtoupper(trim($_POST['marca']));
$modelo = strtoupper(trim($_POST['modelo']));
$serie = strtoupper(trim($_POST['serie']));
$estado = $_POST['estado'];

// 2. Recibir datos técnicos (Con validación básica)
$procesador = !empty($_POST['procesador']) ? $_POST['procesador'] : NULL;
$ram = !empty($_POST['ram']) ? $_POST['ram'] : NULL;
$disco = !empty($_POST['disco']) ? $_POST['disco'] : NULL;
$so = !empty($_POST['so']) ? $_POST['so'] : NULL;
$ip = !empty($_POST['ip']) ? $_POST['ip'] : NULL;
$mac = !empty($_POST['mac']) ? $_POST['mac'] : NULL;
$ubicacion = !empty($_POST['ubicacion']) ? strtoupper(trim($_POST['ubicacion'])) : NULL;

// 3. Validación de duplicados
$check = $conn->query("SELECT id_bien FROM bienes WHERE codigo_patrimonial = '$codigo'");
if ($check->num_rows > 0) {
    echo "<script>alert('El código $codigo ya existe.'); window.history.back();</script>";
    exit();
}

// 4. Insertar en Base de Datos (Prepared Statement)
$sql = "INSERT INTO bienes (
            codigo_patrimonial, id_categoria, descripcion, marca, modelo, serie, estado_fisico, 
            procesador, ram, disco, so, ip, mac, ubicacion, fecha_registro
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sissssssssssss", 
    $codigo, $categoria, $descripcion, $marca, $modelo, $serie, $estado,
    $procesador, $ram, $disco, $so, $ip, $mac, $ubicacion
);

// EJECUTAMOS LA INSERCIÓN
if ($stmt->execute()) {
    $id_insertado = $conn->insert_id;

    // A. Generar QR (Si aplica)
    if ($tiene_libreria_qr) {
        $tempDir = '../img/qr/';
        if (!file_exists($tempDir)) mkdir($tempDir, 0777, true);
        
        $fileName = preg_replace('/[^A-Za-z0-9\-]/', '', $codigo) . '.png';
        $path = $tempDir . $fileName;
        $url = "http://" . $_SERVER['HTTP_HOST'] . "/patrimoniogore/ver_activo.php?id=" . $id_insertado;
        
        QRcode::png($url, $path, QR_ECLEVEL_L, 10, 2);
    }
    
    // B. --- AQUÍ VA LA AUDITORÍA (CORREGIDO) ---
    // Se ejecuta solo si el insert funcionó y antes de irse.
    include 'logger.php';
    $detalles = "Agregó activo: $descripcion ($marca $modelo) - Cod: $codigo";
    
    // Llamamos a la función
    registrar_auditoria($conn, 'CREAR', $detalles);
    // -------------------------------------------

    // C. Redirigir al final
    header("Location: ../inventario.php?status=success");
    exit(); // Importante poner exit después de header

} else {
    echo "Error BD: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>