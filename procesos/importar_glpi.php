<?php
// procesos/importar_glpi.php
// V37.0 - CONEXIÓN REMOTA EXITOSA (Usuario soporte)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Conexión a TU sistema local (Gore Patrimonio - Destino)
include '../conexion.php'; 
// $conn es la conexión local (donde se guardan los datos)

// --- 2. CONFIGURACIÓN GLPI PRODUCCIÓN (ORIGEN) ---
$glpi_host = "128.168.1.150";  // IP Confirmada
$glpi_user = "soporte";        // Usuario autorizado remotamente
$glpi_pass = "Peru2050";       // Contraseña correcta
$glpi_db   = "glpi";           // Base de datos real

// Crear conexión al GLPI Remoto
$conn_glpi = new mysqli($glpi_host, $glpi_user, $glpi_pass, $glpi_db);

if ($conn_glpi->connect_error) {
    die("❌ Error conectando al GLPI: " . $conn_glpi->connect_error);
}
$conn_glpi->set_charset("utf8");

// --- CONFIGURACIÓN DE CATEGORÍAS (Mapeo) ---
$conf_categorias = [
    'Computer' => [
        'id_cat' => 1, 
        'tabla_glpi' => 'glpi_computers', 
        'tabla_mod' => 'glpi_computermodels', 'fk_model' => 'computermodels_id', 
        'tabla_type' => 'glpi_computertypes', 'fk_type' => 'computertypes_id',
        'icono' => '💻', 'prefijo' => 'PC'
    ],
    'Printer' => [
        'id_cat' => 2, 
        'tabla_glpi' => 'glpi_printers', 
        'tabla_mod' => 'glpi_printermodels', 'fk_model' => 'printermodels_id',
        'tabla_type' => 'glpi_printertypes', 'fk_type' => 'printertypes_id',
        'icono' => '🖨️', 'prefijo' => 'IMP'
    ],
    'Monitor' => [
        'id_cat' => 3, 
        'tabla_glpi' => 'glpi_monitors', 
        'tabla_mod' => 'glpi_monitormodels', 'fk_model' => 'monitormodels_id',
        'tabla_type' => 'glpi_monitortypes', 'fk_type' => 'monitortypes_id',
        'icono' => '🖥️', 'prefijo' => 'MON'
    ]
];

// --- DICCIONARIO DE TRADUCCIÓN (Inglés -> Español) ---
$diccionario_tipos = [
    'Desktop' => 'Computadora', 'Notebook' => 'Laptop', 'Laptop' => 'Laptop',
    'Mini Tower' => 'Mini Torre', 'Micro Tower' => 'Micro Torre', 'Tower' => 'Torre',
    'Low Profile Desktop' => 'PC Slim', 'Docking Station' => 'Estación Dock',
    'Server' => 'Servidor', 'All-in-One' => 'Todo en Uno', 'Rackable' => 'Rackeable',
    'Blade' => 'Servidor Blade'
];

echo "<h1>🚀 Sincronizando con GLPI ($glpi_host)...</h1>";

// --- FUNCIONES AUXILIARES (Usan la conexión remota $conn_glpi) ---
function checkTable($c_glpi, $db_name, $table) {
    $sql = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$db_name' AND TABLE_NAME = '$table'";
    $res = $c_glpi->query($sql);
    return ($res && $res->num_rows > 0);
}
function checkColumn($c_glpi, $db_name, $table, $column) {
    $sql = "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$db_name' AND TABLE_NAME = '$table' AND COLUMN_NAME = '$column'";
    $res = $c_glpi->query($sql);
    return ($res && $res->num_rows > 0);
}
function getGLPIData($c_glpi, $sql) {
    try {
        $res = $c_glpi->query($sql);
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

// Pre-chequeo de tablas en la base de datos REMOTA
$t_os = checkTable($conn_glpi, $glpi_db, 'glpi_items_operatingsystems');
$t_ip = checkTable($conn_glpi, $glpi_db, 'glpi_ipaddresses');
$t_cpu_items = checkTable($conn_glpi, $glpi_db, 'glpi_items_deviceprocessors');
$t_cpu_dev  = checkTable($conn_glpi, $glpi_db, 'glpi_deviceprocessors');
$t_ram_items = checkTable($conn_glpi, $glpi_db, 'glpi_items_devicememories');
$t_location = checkTable($conn_glpi, $glpi_db, 'glpi_locations');

// Estrategia de Disco (Detectar versión de GLPI remota)
$disk_strategy = 'NONE';
$disk_col = 'capacity';
if (checkTable($conn_glpi, $glpi_db, 'glpi_items_deviceharddrives')) {
    if (checkColumn($conn_glpi, $glpi_db, 'glpi_items_deviceharddrives', 'capacity')) { $disk_strategy = 'LINK_CAPACITY'; $disk_col = 'capacity'; }
    elseif (checkColumn($conn_glpi, $glpi_db, 'glpi_items_deviceharddrives', 'specif_capacity')) { $disk_strategy = 'LINK_CAPACITY'; $disk_col = 'specif_capacity'; }
    else {
        if (checkTable($conn_glpi, $glpi_db, 'glpi_deviceharddrives')) {
            if (checkColumn($conn_glpi, $glpi_db, 'glpi_deviceharddrives', 'capacity')) { $disk_strategy = 'DEF_CAPACITY'; $disk_col = 'capacity'; }
            elseif (checkColumn($conn_glpi, $glpi_db, 'glpi_deviceharddrives', 'totalsize')) { $disk_strategy = 'DEF_CAPACITY'; $disk_col = 'totalsize'; }
        }
    }
}

$codigos_basura = ['Default string', 'To be filled by O.E.M.', 'System Serial Number', 'N/A', 'None', 'serial', 'Not Specified', '0123456789'];

// --- BUCLE PRINCIPAL POR CATEGORÍA ---
foreach ($conf_categorias as $tipo => $cfg) {
    if (empty($cfg['id_cat'])) continue;
    
    echo "<div class='card mb-3 p-3 border'>";
    echo "<h3 class='text-primary'>".$cfg['icono']." Procesando: $tipo</h3>";
    
    // Verificar tabla principal remota
    if (!checkTable($conn_glpi, $glpi_db, $cfg['tabla_glpi'])) { 
        echo "<span style='color:red'>Tabla $cfg[tabla_glpi] NO encontrada en la BD remota.</span></div>"; 
        continue; 
    }

    // Configurar columnas dinámicas
    $col_inv = "'' as otherserial";
    if(checkColumn($conn_glpi, $glpi_db, $cfg['tabla_glpi'], 'otherserial')) $col_inv = "otherserial";
    $col_alt = "'' as contact_num";
    if(checkColumn($conn_glpi, $glpi_db, $cfg['tabla_glpi'], 'contact_num')) $col_alt = "contact_num";
    
    $col_model = $cfg['fk_model'];
    $col_type = "'' as type_id"; 
    if (isset($cfg['fk_type']) && checkColumn($conn_glpi, $glpi_db, $cfg['tabla_glpi'], $cfg['fk_type'])) {
        $col_type = $cfg['fk_type'] . " as type_id";
    }

    // QUERY AL GLPI REMOTO
    $sql_base = "SELECT id, name, serial, $col_inv, $col_alt, date_mod, manufacturers_id, locations_id, users_id, 
                 $col_model as model_id, $col_type 
                 FROM $glpi_db.".$cfg['tabla_glpi']." 
                 WHERE is_deleted = 0"; 

    $res_main = $conn_glpi->query($sql_base);
    
    if (!$res_main) { echo "<p style='color:red'>Error SQL Remoto: " . $conn_glpi->error . "</p></div>"; continue; }

    $cnt_upd = 0; $cnt_new = 0;
    
    echo "<div style='max-height:300px; overflow-y:auto; background:#f8f9fa; padding:10px; border:1px solid #ddd; font-family:monospace; font-size:0.85rem;'>";

    while ($item = $res_main->fetch_assoc()) {
        $id_glpi = $item['id'];
        $id_cat = $cfg['id_cat'];
        $prefijo = $cfg['prefijo'];
        
        // 1. Determinar CÓDIGO PATRIMONIAL
        $codigo_base = "";
        if (!empty($item['otherserial']) && !in_array(trim($item['otherserial']), $codigos_basura)) {
            $codigo_base = trim($item['otherserial']);
        } elseif (!empty($item['contact_num']) && !in_array(trim($item['contact_num']), $codigos_basura)) {
            $codigo_base = trim($item['contact_num']);
        }
        if (empty($codigo_base)) $codigo_base = "S/N-" . $prefijo . "-" . $id_glpi;
        $codigo_final = $codigo_base;

        // 2. Obtener UBICACIÓN (Remoto)
        $ubicacion_final = '-';
        if ($item['locations_id'] > 0 && $t_location) {
            $sql_loc = "SELECT completename FROM $glpi_db.glpi_locations WHERE id = " . $item['locations_id'];
            if(!checkColumn($conn_glpi, $glpi_db, 'glpi_locations', 'completename')) 
                $sql_loc = "SELECT name FROM $glpi_db.glpi_locations WHERE id = " . $item['locations_id'];
            $val = getGLPIData($conn_glpi, $sql_loc);
            if ($val) $ubicacion_final = $val;
        }

        // 3. Obtener IP y SO (Remoto)
        $ip_final = '-';
        if ($t_ip) {
            $val = getGLPIData($conn_glpi, "SELECT ip.name FROM $glpi_db.glpi_ipaddresses ip JOIN $glpi_db.glpi_networkports np ON ip.mainitems_id = np.id WHERE np.items_id = $id_glpi AND np.itemtype = '$tipo' AND ip.name NOT LIKE '%:%' LIMIT 1");
            if ($val) $ip_final = $val;
        }
        $os_final = '-';
        if ($tipo == 'Computer' && $t_os) {
            $val = getGLPIData($conn_glpi, "SELECT os.name FROM $glpi_db.glpi_operatingsystems os JOIN $glpi_db.glpi_items_operatingsystems ios ON os.id = ios.operatingsystems_id WHERE ios.items_id = $id_glpi AND ios.itemtype = 'Computer' LIMIT 1");
            if ($val) $os_final = $val;
        }

        // 4. Hardware (Disco, CPU, RAM) (Remoto)
        $disco_final = '-'; $procesador_final = '-'; $ram_final = '-';
        if ($tipo == 'Computer') {
            $disk_mb = 0;
            if ($disk_strategy == 'LINK_CAPACITY') $disk_mb = getGLPIData($conn_glpi, "SELECT SUM($disk_col) FROM $glpi_db.glpi_items_deviceharddrives WHERE items_id = $id_glpi AND itemtype = 'Computer'");
            elseif ($disk_strategy == 'DEF_CAPACITY') $disk_mb = getGLPIData($conn_glpi, "SELECT SUM(d.$disk_col) FROM $glpi_db.glpi_items_deviceharddrives l INNER JOIN $glpi_db.glpi_deviceharddrives d ON l.deviceharddrives_id = d.id WHERE l.items_id = $id_glpi AND l.itemtype = 'Computer'");
            if ($disk_mb > 0) $disco_final = formatearDisco($disk_mb);

            if ($t_cpu_items && $t_cpu_dev) {
                $val = getGLPIData($conn_glpi, "SELECT d.designation FROM $glpi_db.glpi_items_deviceprocessors i INNER JOIN $glpi_db.glpi_deviceprocessors d ON i.deviceprocessors_id = d.id WHERE i.items_id = $id_glpi AND i.itemtype = 'Computer' LIMIT 1");
                if ($val) $procesador_final = $val;
            }
            if ($t_ram_items) {
                $ram_mb = getGLPIData($conn_glpi, "SELECT SUM(size) FROM $glpi_db.glpi_items_devicememories WHERE items_id = $id_glpi AND itemtype = 'Computer'");
                if ($ram_mb > 0) $ram_final = ($ram_mb >= 1024) ? round($ram_mb / 1024) . " GB" : $ram_mb . " MB";
            }
        }

        // 5. Marca, Modelo y Tipo
        $marca = ($item['manufacturers_id']) ? (getGLPIData($conn_glpi, "SELECT name FROM $glpi_db.glpi_manufacturers WHERE id = " . $item['manufacturers_id']) ?? 'GENERICO') : 'GENERICO';
        $modelo = '-';
        if ($item['model_id'] && checkTable($conn_glpi, $glpi_db, $cfg['tabla_mod'])) {
            $modelo = getGLPIData($conn_glpi, "SELECT name FROM $glpi_db.".$cfg['tabla_mod']." WHERE id = " . $item['model_id']) ?? '-';
        }

        $tipo_equipo = '-';
        if (!empty($item['type_id']) && isset($cfg['tabla_type']) && checkTable($conn_glpi, $glpi_db, $cfg['tabla_type'])) {
            $tipo_ingles = getGLPIData($conn_glpi, "SELECT name FROM $glpi_db.".$cfg['tabla_type']." WHERE id = " . $item['type_id']) ?? '-';
            $tipo_equipo = array_key_exists($tipo_ingles, $diccionario_tipos) ? $diccionario_tipos[$tipo_ingles] : $tipo_ingles;
        }

        // 6. Estado Físico
        $estado_final = 'Regular';
        if (checkColumn($conn_glpi, $glpi_db, $cfg['tabla_glpi'], 'states_id')) {
            $st = strtoupper(getGLPIData($conn_glpi, "SELECT s.name FROM $glpi_db.".$cfg['tabla_glpi']." c LEFT JOIN $glpi_db.glpi_states s ON c.states_id = s.id WHERE c.id = $id_glpi") ?? '');
            if (strpos($st, 'BUEN')!==false || strpos($st, 'OPERATIV')!==false) $estado_final='Bueno';
            elseif (strpos($st, 'MAL')!==false || strpos($st, 'BAJA')!==false) $estado_final='Malo';
        }

        // PREPARAR PARA GUARDAR (Escapar caracteres para BD Local)
        $desc = $conn->real_escape_string("$tipo GLPI " . $item['name']);
        $marca = $conn->real_escape_string($marca);
        $modelo = $conn->real_escape_string($modelo);
        $tipo_equipo = $conn->real_escape_string($tipo_equipo);
        $os_final = $conn->real_escape_string($os_final);
        $ip_final = $conn->real_escape_string($ip_final);
        $procesador_final = $conn->real_escape_string($procesador_final);
        $ram_final = $conn->real_escape_string($ram_final);
        $ubicacion_final = $conn->real_escape_string($ubicacion_final);
        $codigo_check = $conn->real_escape_string($codigo_final);

        // --- INSERTAR O ACTUALIZAR EN TU BD LOCAL ($conn) ---
        $qry_id = $conn->query("SELECT id_bien FROM bienes WHERE id_glpi = $id_glpi AND id_categoria = $id_cat");
        
        if ($qry_id && $qry_id->num_rows > 0) {
            // -- ACTUALIZACIÓN --
            $id = $qry_id->fetch_assoc()['id_bien'];
            
            // Verificar si el código nuevo duplicaría otro bien existente
            $chk_dup = $conn->query("SELECT id_bien FROM bienes WHERE codigo_patrimonial = '$codigo_final' AND id_bien != $id");
            if ($chk_dup && $chk_dup->num_rows > 0) {
                $codigo_final = $codigo_final . "-DUP-" . $prefijo . "-" . $id_glpi;
            }

            $sql_upd = "UPDATE bienes SET 
                        codigo_patrimonial = '$codigo_final',
                        ip='$ip_final', so='$os_final', disco='$disco_final', 
                        procesador='$procesador_final', ram='$ram_final', ubicacion='$ubicacion_final', 
                        marca='$marca', modelo='$modelo', tipo_equipo='$tipo_equipo', 
                        estado_fisico='$estado_final' 
                        WHERE id_bien=$id";
            if ($conn->query($sql_upd)) $cnt_upd++;
            else echo "<span style='color:red'>Error Update: " . $conn->error . "</span><br>";

        } else {
            // -- INSERCIÓN --
            $qry_code = $conn->query("SELECT id_bien FROM bienes WHERE codigo_patrimonial = '$codigo_check'");
            if ($qry_code && $qry_code->num_rows > 0) {
                $codigo_final = $codigo_base . "-DUP-" . $prefijo . "-" . $id_glpi;
            }

            $sql_ins = "INSERT INTO bienes (
                id_glpi, codigo_patrimonial, id_categoria, descripcion, marca, modelo, tipo_equipo, serie, 
                ip, so, disco, procesador, ram, ubicacion, estado_fisico, fecha_registro
            ) VALUES (
                $id_glpi, '$codigo_final', $id_cat, '$desc', '$marca', '$modelo', '$tipo_equipo', '$item[serial]', 
                '$ip_final', '$os_final', '$disco_final', '$procesador_final', '$ram_final', 
                '$ubicacion_final', '$estado_final', NOW()
            )";
            
            if ($conn->query($sql_ins)) {
                $color = (strpos($codigo_final, 'DUP') !== false) ? 'purple' : 'green';
                echo "<span style='color:$color'>[NUEVO] $codigo_final ($tipo_equipo)</span><br>";
                $cnt_new++;
            } else {
                 echo "<span style='color:red'>Error Insert: " . $conn->error . "</span><br>";
            }
        }
    }
    echo "</div><p>Resumen $tipo: Nuevos: $cnt_new | Actualizados: $cnt_upd</p></div>";
}
?>