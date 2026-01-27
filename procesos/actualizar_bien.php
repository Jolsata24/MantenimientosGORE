<?php
// procesos/actualizar_bien.php - CON REDIRECCIÓN INTELIGENTE
session_start();
include '../conexion.php';

// 1. Recibir Datos
$id_bien = $_POST['id_bien'];
$codigo = strtoupper(trim($_POST['codigo']));
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

// 2. Lógica del Archivo (Informe de Baja)
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

// 3. ACTUALIZACIÓN EN BASE DE DATOS

if ($subio_archivo) {
    // OPCIÓN A: Con archivo nuevo
    $sql = "UPDATE bienes SET 
            id_categoria = ?, 
            codigo_patrimonial = ?,  
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
            informe_baja = ?
            WHERE id_bien = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssssissssssssi", 
        $categoria, $codigo, $descripcion, $color, $marca, $modelo, $serie, $estado, $ubicacion, $id_personal,
        $procesador, $ram, $disco, $so, $ip, $mac, 
        $nombre_archivo, 
        $id_bien
    );

} else {
    // OPCIÓN B: Sin archivo (Mantiene el anterior)
    $sql = "UPDATE bienes SET 
            id_categoria = ?, 
            codigo_patrimonial = ?, 
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
    $stmt->bind_param("isssssssisssssssi", 
        $categoria, $codigo, $descripcion, $color, $marca, $modelo, $serie, $estado, $ubicacion, $id_personal,
        $procesador, $ram, $disco, $so, $ip, $mac, 
        $id_bien
    );
}

// 4. EJECUTAR Y REDIRIGIR INTELIGENTE
if ($stmt->execute()) {
    if (file_exists('logger.php')) {
        include 'logger.php';
        registrar_auditoria($conn, 'EDITAR', "Editó activo ID: $id_bien. Cod: $codigo");
    }

    // --- LÓGICA DE REDIRECCIÓN ---
    $pagina_destino = '../inventario.php'; // Por defecto

    switch ($categoria) {
        case 1: // Computadoras
            $pagina_destino = '../computacion.php';
            break;
        case 2: // Impresoras
            $pagina_destino = '../impresora.php';
            break;
        case 3: // Monitores
            $pagina_destino = '../monitor.php';
            break;
        // Puedes agregar más casos aquí si creas más páginas (ej. case 4 para proyectores)
    }

    // Redirigimos a la página correcta manteniendo el mensaje de éxito
    header("Location: " . $pagina_destino . "?status=updated");

} else {
    echo "Error al guardar: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>