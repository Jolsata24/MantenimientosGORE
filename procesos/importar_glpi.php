<?php
// procesos/importar_glpi.php
// --- MODO DEPURACIÓN ACTIVADO ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../conexion.php';

// 1. CONFIGURACIÓN
$nombre_db_glpi = "glpi_backup_restore"; // Asegúrate que este nombre sea correcto
$id_categoria_computadoras = 1;

echo "<h1>Migración con Ubicaciones...</h1>";

// 2. CONSULTA MEJORADA (Ahora trae glpi_locations)
$sql_origen = "
    SELECT 
        c.name AS nombre_pc,
        c.otherserial AS codigo_inventario,
        c.serial AS serie,
        m.name AS modelo,
        manu.name AS marca,
        loc.completename AS ubicacion_glpi
    FROM $nombre_db_glpi.glpi_computers c
    LEFT JOIN $nombre_db_glpi.glpi_computermodels m ON c.computermodels_id = m.id
    LEFT JOIN $nombre_db_glpi.glpi_manufacturers manu ON c.manufacturers_id = manu.id
    LEFT JOIN $nombre_db_glpi.glpi_locations loc ON c.locations_id = loc.id
    WHERE c.is_deleted = 0 AND c.is_template = 0
";

$resultado_glpi = $conn->query($sql_origen);

if (!$resultado_glpi) { die("Error SQL GLPI: " . $conn->error); }

$contador = 0;
$actualizados = 0;

while($fila = $resultado_glpi->fetch_assoc()) {
    
    // Limpieza de datos
    $codigo = !empty($fila['codigo_inventario']) ? $fila['codigo_inventario'] : $fila['nombre_pc'];
    $codigo = strtoupper(trim($codigo));
    $descripcion = "COMPUTADORA " . strtoupper($fila['nombre_pc']);
    $marca = strtoupper(trim($fila['marca']));
    $modelo = strtoupper(trim($fila['modelo']));
    $serie = strtoupper(trim($fila['serie']));
    
    // LIMPIEZA DE UBICACIÓN (GLPI suele poner 'Entidad > Sede > Oficina', nos quedamos con lo último)
    $ubicacion_full = $fila['ubicacion_glpi'];
    $partes_ubicacion = explode(' > ', $ubicacion_full);
    $ubicacion_corta = end($partes_ubicacion); // Toma solo la última parte (ej: "Logística")
    $ubicacion = strtoupper(trim($ubicacion_corta));

    if(empty($marca)) $marca = "GENERICO";
    if(empty($ubicacion)) $ubicacion = "SIN UBICACION";

    // VERIFICAR SI YA EXISTE
    $check = $conn->query("SELECT id_bien FROM bienes WHERE codigo_patrimonial = '$codigo'");
    
    if($check->num_rows == 0) {
        // INSERTAR NUEVO
        $stmt = $conn->prepare("INSERT INTO bienes (codigo_patrimonial, id_categoria, descripcion, marca, modelo, serie, ubicacion, estado_fisico, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, 'Bueno', NOW())");
        $stmt->bind_param("sisssss", $codigo, $id_categoria_computadoras, $descripcion, $marca, $modelo, $serie, $ubicacion);
        if($stmt->execute()) $contador++;
    } else {
        // ACTUALIZAR (Si ya existe, le actualizamos la ubicación por si acaso)
        $conn->query("UPDATE bienes SET ubicacion = '$ubicacion' WHERE codigo_patrimonial = '$codigo'");
        $actualizados++;
    }
}

echo "<p style='color:green; font-weight:bold;'>Proceso Terminado:</p>";
echo "<ul><li>Nuevos importados: $contador</li>";
echo "<li>Existentes actualizados (Ubicación): $actualizados</li></ul>";
echo "<a href='../inventario.php'>Volver al Inventario</a>";
?>