<?php
// procesos/guardar_bien.php

session_start();
include '../conexion.php';

// Verificamos si tienes la librería instalada.
// Si no la tienes, el sistema guardará el bien PERO NO generará la imagen para evitar errores fatales.
$tiene_libreria_qr = file_exists('../phpqrcode/qrlib.php');
if ($tiene_libreria_qr) {
    include '../phpqrcode/qrlib.php';
}

// 1. Recibir datos del formulario
$codigo = trim($_POST['codigo']);
$categoria = $_POST['categoria'];
$descripcion = strtoupper(trim($_POST['descripcion'])); // Convertimos a Mayúsculas
$marca = strtoupper(trim($_POST['marca']));
$modelo = strtoupper(trim($_POST['modelo']));
$serie = strtoupper(trim($_POST['serie']));
$estado = $_POST['estado'];

// 2. Validación: ¿El código patrimonial ya existe?
$sql_check = "SELECT id_bien FROM bienes WHERE codigo_patrimonial = '$codigo'";
$result_check = $conn->query($sql_check);

if ($result_check->num_rows > 0) {
    echo "<script>
            alert('ERROR: El código patrimonial $codigo ya existe. Intente con otro.');
            window.history.back();
          </script>";
    exit();
}

// 3. Insertar en Base de Datos
$sql = "INSERT INTO bienes (codigo_patrimonial, id_categoria, descripcion, marca, modelo, serie, estado_fisico, fecha_registro) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sisssss", $codigo, $categoria, $descripcion, $marca, $modelo, $serie, $estado);

if ($stmt->execute()) {
    
    // Obtenemos el ID que acaba de crear la base de datos (Ej: 15)
    $id_insertado = $conn->insert_id;

    // 4. GENERACIÓN DEL CÓDIGO QR
    if ($tiene_libreria_qr) {
        
        // A. Definimos la carpeta donde se guardará
        $tempDir = '../img/qr/';
        if (!file_exists($tempDir)) mkdir($tempDir);

        // B. Definimos el nombre del archivo (Ej: GORE-001.png)
        // Limpiamos caracteres raros del código para que sea un nombre de archivo válido
        $fileName = preg_replace('/[^A-Za-z0-9\-]/', '', $codigo) . '.png';
        $pngAbsoluteFilePath = $tempDir . $fileName;

        // C. Definimos el CONTENIDO del QR (El enlace mágico)
        // Detectamos la IP del servidor automáticamente para que funcione en red local
        $ip_servidor = $_SERVER['HTTP_HOST']; // Ej: localhost o 192.168.1.50
        
        // URL FINAL: http://192.168.1.X/patrimoniogore/ver_activo.php?id=15
        $url_qr = "http://" . $ip_servidor . "/patrimoniogore/ver_activo.php?id=" . $id_insertado;

        // D. Generar la imagen
        // QRcode::png(contenido, archivo, nivel_correccion, tamaño, margen)
        QRcode::png($url_qr, $pngAbsoluteFilePath, QR_ECLEVEL_L, 4, 2);
    }

    // 5. Redireccionar con éxito
    header("Location: ../inventario.php?status=success");

} else {
    echo "Error al guardar en BD: " . $conn->error;
}

$stmt->close();
$conn->close();
?>  