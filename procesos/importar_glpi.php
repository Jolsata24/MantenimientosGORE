<?php
// procesos/importar_glpi.php
// V31.0 - FINAL: Solución de Conflicto de IDs entre Categorías (S/N-PC-1, S/N-IMP-1)
ini_set('display_errors', 1);
error_reporting(E_ALL);
include '../conexion.php';

// --- CONFIGURACIÓN ---
$conf_categorias = [
    'Computer' => ['id_cat' => 1, 'tabla_glpi' => 'glpi_computers', 'tabla_mod' => 'glpi_computermodels', 'fk_model' => 'computermodels_id', 'icono' => '💻', 'prefijo' => 'PC'],
    'Printer' => ['id_cat' => 2, 'tabla_glpi' => 'glpi_printers', 'tabla_mod' => 'glpi_printermodels', 'fk_model' => 'printermodels_id', 'icono' => '🖨️', 'prefijo' => 'IMP'],
    'Monitor' => ['id_cat' => 3, 'tabla_glpi' => 'glpi_monitors', 'tabla_mod' => 'glpi_monitormodels', 'fk_model' => 'monitormodels_id', 'icono' => '🖥️', 'prefijo' => 'MON']
];

$db_glpi = "glpi_backup_restore"; 

echo "<h1>🚀 Importación GLPI V31.0 (Prefijos Únicos)</h1>";

// --- FUNCIONES ---
function checkTable($conn, $db, $table) {
    $res = $conn->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$db' AND TABLE_NAME = '$table'");
    return ($res && $res->num_rows > 0);
}
function checkColumn($conn, $db, $table, $column) {
    $res = $conn->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$db' AND TABLE_NAME = '$table' AND COLUMN_NAME = '$column'");
    return ($res && $res->num_rows > 0);
}
function getGLPIData($conn, $sql) {
    try {
        $res = $conn->query($sql);
        if ($res && $res->num_rows > 0) return $res->fetch_row()[0];
    } catch (Exception $e) { return null; }
    return null;
}
function formatearDisco($mb) {
    if ($mb < 1000) return "-";
    $gb_real = $mb / 1024;
    if ($gb_real > 110 && $gb_real < 118) return "120 GB";
    if ($gb_real >= 118 && $gb_real < 135) return "128 GB";
    if ($gb_real > 220 && $gb_real < 230) return "240 GB";
    if ($gb_real >= 230 && $gb_real < 245) return "250 GB";
    if ($gb_real >= 245 && $gb_real < 265) return "256 GB";
    if ($gb_real > 440 && $gb_real < 460) return "480 GB";
    if ($gb_real >= 460 && $gb_real < 472) return "500 GB";
    if ($gb_real >= 472 && $gb_real < 530) return "512 GB";
    if ($gb_real > 900 && $gb_real < 1100) return "1 TB";
    if ($gb_real > 1800 && $gb_real < 2100) return "2 TB";
    return round($gb_real) . " GB";
}

// Tablas auxiliares
$t_os = checkTable($conn, $db_glpi, 'glpi_items_operatingsystems');
$t_ip = checkTable($conn, $db_glpi, 'glpi_ipaddresses');
$t_cpu_items = checkTable($conn, $db_glpi, 'glpi_items_deviceprocessors');
$t_cpu_dev  = checkTable($conn, $db_glpi, 'glpi_deviceprocessors');
$t_ram_items = checkTable($conn, $db_glpi, 'glpi_items_devicememories');
$t_location = checkTable($conn, $db_glpi, 'glpi_locations');

// Estrategia Disco
$disk_strategy = 'NONE';
$disk_col = 'capacity';
if (checkTable($conn, $db_glpi, 'glpi_items_deviceharddrives')) {
    if (checkColumn($conn, $db_glpi, 'glpi_items_deviceharddrives', 'capacity')) { $disk_strategy = 'LINK_CAPACITY'; $disk_col = 'capacity'; }
    elseif (checkColumn($conn, $db_glpi, 'glpi_items_deviceharddrives', 'specif_capacity')) { $disk_strategy = 'LINK_CAPACITY'; $disk_col = 'specif_capacity'; }
    else {
        if (checkTable($conn, $db_glpi, 'glpi_deviceharddrives')) {
            if (checkColumn($conn, $db_glpi, 'glpi_deviceharddrives', 'capacity')) { $disk_strategy = 'DEF_CAPACITY'; $disk_col = 'capacity'; }
            elseif (checkColumn($conn, $db_glpi, 'glpi_deviceharddrives', 'totalsize')) { $disk_strategy = 'DEF_CAPACITY'; $disk_col = 'totalsize'; }
        }
    }
}

foreach ($conf_categorias as $tipo => $cfg) {
    if (empty($cfg['id_cat'])) continue;
    
    echo "<div class='card mb-3 p-3 border'>";
    echo "<h3 class='text-primary'>".$cfg['icono']." Procesando: $tipo</h3>";
    
    if (!checkTable($conn, $db_glpi, $cfg['tabla_glpi'])) { echo "<span style='color:red'>Tabla NO encontrada.</span></div>"; continue; }

    $col_inv = "'' as otherserial";
    if(checkColumn($conn, $db_glpi, $cfg['tabla_glpi'], 'otherserial')) $col_inv = "otherserial";
    $col_alt = "'' as contact_num";
    if(checkColumn($conn, $db_glpi, $cfg['tabla_glpi'], 'contact_num')) $col_alt = "contact_num";
    $col_model = $cfg['fk_model'];

    $sql_base = "SELECT id, name, serial, $col_inv, $col_alt, date_mod, manufacturers_id, locations_id, users_id, $col_model as model_id 
                 FROM $db_glpi.".$cfg['tabla_glpi']." 
                 WHERE is_deleted = 0"; 

    $res_main = $conn->query($sql_base);
    if (!$res_main) { echo "<p style='color:red'>Error SQL</p></div>"; continue; }

    $cnt_upd = 0; $cnt_new = 0;
    
    echo "<div style='max-height:300px; overflow-y:auto; background:#f8f9fa; padding:10px; border:1px solid #ddd; font-family:monospace; font-size:0.85rem;'>";

    while ($item = $res_main->fetch_assoc()) {
        $id_glpi = $item['id'];
        $id_cat = $cfg['id_cat'];
        $prefijo = $cfg['prefijo'];
        
        // 1. GENERACIÓN INTELIGENTE DE CÓDIGO (Solución del error)
        $codigo_base = "";
        if (!empty($item['otherserial'])) $codigo_base = trim($item['otherserial']);
        elseif (!empty($item['contact_num'])) $codigo_base = trim($item['contact_num']);
        
        if (empty($codigo_base)) {
            // Si no tiene código, usamos formato: S/N-PREFIJO-ID
            // Ejemplo: S/N-PC-1, S/N-IMP-1
            $codigo_base = "S/N-" . $prefijo . "-" . $id_glpi;
        }
        $codigo_final = $codigo_base;

        // Ubicación
        $ubicacion_final = '-';
        if ($item['locations_id'] > 0 && $t_location) {
            $sql_loc = "SELECT completename FROM $db_glpi.glpi_locations WHERE id = " . $item['locations_id'];
            if(!checkColumn($conn, $db_glpi, 'glpi_locations', 'completename')) $sql_loc = "SELECT name FROM $db_glpi.glpi_locations WHERE id = " . $item['locations_id'];
            $val = getGLPIData($conn, $sql_loc);
            if ($val) $ubicacion_final = $val;
        }

        // IP
        $ip_final = '-';
        if ($t_ip) {
            $val = getGLPIData($conn, "SELECT ip.name FROM $db_glpi.glpi_ipaddresses ip JOIN $db_glpi.glpi_networkports np ON ip.mainitems_id = np.id WHERE np.items_id = $id_glpi AND np.itemtype = '$tipo' AND ip.name NOT LIKE '%:%' LIMIT 1");
            if ($val) $ip_final = $val;
        }

        // SO
        $os_final = '-';
        if ($tipo == 'Computer' && $t_os) {
            $val = getGLPIData($conn, "SELECT os.name FROM $db_glpi.glpi_operatingsystems os JOIN $db_glpi.glpi_items_operatingsystems ios ON os.id = ios.operatingsystems_id WHERE ios.items_id = $id_glpi AND ios.itemtype = 'Computer' LIMIT 1");
            if ($val) $os_final = $val;
        }

        // Hardware (PC)
        $disco_final = '-';
        $procesador_final = '-';
        $ram_final = '-';
        if ($tipo == 'Computer') {
            $disk_mb = 0;
            if ($disk_strategy == 'LINK_CAPACITY') $disk_mb = getGLPIData($conn, "SELECT SUM($disk_col) FROM $db_glpi.glpi_items_deviceharddrives WHERE items_id = $id_glpi AND itemtype = 'Computer'");
            elseif ($disk_strategy == 'DEF_CAPACITY') $disk_mb = getGLPIData($conn, "SELECT SUM(d.$disk_col) FROM $db_glpi.glpi_items_deviceharddrives l INNER JOIN $db_glpi.glpi_deviceharddrives d ON l.deviceharddrives_id = d.id WHERE l.items_id = $id_glpi AND l.itemtype = 'Computer'");
            if ($disk_mb > 0) $disco_final = formatearDisco($disk_mb);

            if ($t_cpu_items && $t_cpu_dev) {
                $val = getGLPIData($conn, "SELECT d.designation FROM $db_glpi.glpi_items_deviceprocessors i INNER JOIN $db_glpi.glpi_deviceprocessors d ON i.deviceprocessors_id = d.id WHERE i.items_id = $id_glpi AND i.itemtype = 'Computer' LIMIT 1");
                if ($val) $procesador_final = $val;
            }
            if ($t_ram_items) {
                $ram_mb = getGLPIData($conn, "SELECT SUM(size) FROM $db_glpi.glpi_items_devicememories WHERE items_id = $id_glpi AND itemtype = 'Computer'");
                if ($ram_mb > 0) $ram_final = ($ram_mb >= 1024) ? round($ram_mb / 1024) . " GB" : $ram_mb . " MB";
            }
        }

        // Otros
        $marca = ($item['manufacturers_id']) ? (getGLPIData($conn, "SELECT name FROM $db_glpi.glpi_manufacturers WHERE id = " . $item['manufacturers_id']) ?? 'GENERICO') : 'GENERICO';
        $modelo = '-';
        if ($item['model_id'] && checkTable($conn, $db_glpi, $cfg['tabla_mod'])) {
            $modelo = getGLPIData($conn, "SELECT name FROM $db_glpi.".$cfg['tabla_mod']." WHERE id = " . $item['model_id']) ?? '-';
        }
        $estado_final = 'Regular';
        if (checkColumn($conn, $db_glpi, $cfg['tabla_glpi'], 'states_id')) {
            $st = strtoupper(getGLPIData($conn, "SELECT s.name FROM $db_glpi.".$cfg['tabla_glpi']." c LEFT JOIN $db_glpi.glpi_states s ON c.states_id = s.id WHERE c.id = $id_glpi") ?? '');
            if (strpos($st, 'BUEN')!==false || strpos($st, 'OPERATIV')!==false) $estado_final='Bueno';
            elseif (strpos($st, 'MAL')!==false || strpos($st, 'BAJA')!==false) $estado_final='Malo';
        }

        // Escapar datos
        $desc = $conn->real_escape_string("$tipo GLPI " . $item['name']);
        $marca = $conn->real_escape_string($marca);
        $modelo = $conn->real_escape_string($modelo);
        $os_final = $conn->real_escape_string($os_final);
        $ip_final = $conn->real_escape_string($ip_final);
        $procesador_final = $conn->real_escape_string($procesador_final);
        $ram_final = $conn->real_escape_string($ram_final);
        $ubicacion_final = $conn->real_escape_string($ubicacion_final);
        $codigo_check = $conn->real_escape_string($codigo_final);

        // --- SINCRONIZACIÓN ---
        
        $qry_id = $conn->query("SELECT id_bien FROM bienes WHERE id_glpi = $id_glpi AND id_categoria = $id_cat");
        
        if ($qry_id->num_rows > 0) {
            // ACTUALIZAR
            $id = $qry_id->fetch_assoc()['id_bien'];
            $sql_upd = "UPDATE bienes SET 
                        codigo_patrimonial = '$codigo_final',
                        ip='$ip_final', so='$os_final', disco='$disco_final', 
                        procesador='$procesador_final', ram='$ram_final', ubicacion='$ubicacion_final', 
                        marca='$marca', modelo='$modelo', estado_fisico='$estado_final' 
                        WHERE id_bien=$id";
            if ($conn->query($sql_upd)) $cnt_upd++;
        } else {
            // INSERTAR
            
            // Verificamos conflicto de Código (por si GLPI tiene duplicados reales)
            $qry_code = $conn->query("SELECT id_bien FROM bienes WHERE codigo_patrimonial = '$codigo_check'");
            if ($qry_code->num_rows > 0) {
                // Conflicto Real: Agregamos sufijo con PREFIJO para evitar choques entre categorías
                $codigo_final = $codigo_base . "-DUP-" . $prefijo . "-" . $id_glpi;
            }

            $sql_ins = "INSERT INTO bienes (
                id_glpi, codigo_patrimonial, id_categoria, descripcion, marca, modelo, serie, 
                ip, so, disco, procesador, ram, ubicacion, estado_fisico, fecha_registro
            ) VALUES (
                $id_glpi, '$codigo_final', $id_cat, '$desc', '$marca', '$modelo', '$item[serial]', 
                '$ip_final', '$os_final', '$disco_final', '$procesador_final', '$ram_final', 
                '$ubicacion_final', '$estado_final', NOW()
            )";
            
            if ($conn->query($sql_ins)) {
                $color = (strpos($codigo_final, 'DUP') !== false) ? 'purple' : 'green';
                echo "<span style='color:$color'>[NUEVO] $codigo_final</span><br>";
                $cnt_new++;
            }
        }
    }
    echo "</div><p>Resumen $tipo: Nuevos: $cnt_new | Actualizados: $cnt_upd</p></div>";
}
?>