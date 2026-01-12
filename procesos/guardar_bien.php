<?php
// procesos/guardar_bien.php

session_start();
include '../conexion.php';

// --- CORRECCIÓN IMPORTANTE ---
// Verificamos dónde está la librería. Según tus archivos, está en la raíz como 'phpqrcode.php'
if (file_exists('../phpqrcode.php')) {
    include '../phpqrcode.php'; // Cargamos el archivo único
    $tiene_libreria_qr = true;
} elseif (file_exists('../phpqrcode/qrlib.php')) {
    include '../phpqrcode/qrlib.php'; // Por si acaso tienes la carpeta
    $tiene_libreria_qr = true;
} else {
    $tiene_libreria_qr = false;
}

// 1. Recibir datos del formulario
$codigo = trim($_POST['codigo']);
$categoria = $_POST['categoria'];
$descripcion = strtoupper(trim($_POST['descripcion'])); // Mayúsculas
$marca = strtoupper(trim($_POST['marca']));
$modelo = strtoupper(trim($_POST['modelo']));
$serie = strtoupper(trim($_POST['serie']));
$estado = $_POST['estado'];

// 2. Validación: Evitar duplicados
$sql_check = "SELECT id_bien FROM bienes WHERE codigo_patrimonial = '$codigo'";
$result_check = $conn->query($sql_check);

if ($result_check->num_rows > 0) {
    echo "<script>
            alert('ERROR: El código patrimonial $codigo ya existe.');
            window.history.back();
          </script>";
    exit();
}

// 3. Insertar en Base de Datos
$sql = "INSERT INTO bienes (codigo_patrimonial, id_categoria, descripcion, marca, modelo, serie, estado_fisico, fecha_registro) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

// Asegúrate que los tipos coincidan con tu tabla (s=string, i=integer)
$stmt = $conn->prepare($sql);
$stmt->bind_param("sisssss", $codigo, $categoria, $descripcion, $marca, $modelo, $serie, $estado);

if ($stmt->execute()) {
    
    // Obtenemos el ID generado
    $id_insertado = $conn->insert_id;

    // 4. GENERACIÓN DEL CÓDIGO QR
    if ($tiene_libreria_qr) {
        
        // A. Crear carpeta si no existe
        $tempDir = '../img/qr/';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true); // Permisos de escritura
        }

        // B. Nombre del archivo (limpiamos caracteres raros)
        $fileName = preg_replace('/[^A-Za-z0-9\-]/', '', $codigo) . '.png';
        $pngAbsoluteFilePath = $tempDir . $fileName;

        // C. URL del QR (Usamos la IP del servidor para que funcione en celulares)
        $ip_servidor = $_SERVER['HTTP_HOST']; 
        $url_qr = "http://" . $ip_servidor . "/patrimoniogore/ver_activo.php?id=" . $id_insertado;

        // D. Generar Imagen
        // QR_ECLEVEL_L = Nivel de corrección bajo (QR más simple y legible)
        QRcode::png($url_qr, $pngAbsoluteFilePath, QR_ECLEVEL_L, 10, 2);
    }

    // 5. Éxito
    header("Location: ../inventario.php?status=success");

} else {
    echo "Error al guardar en BD: " . $conn->error;
}

$stmt->close();
$conn->close();
?>