<?php
// procesos/importar_glpi.php
// V10.0 - SOLUCIÓN MAESTRA: ID ÚNICO DE GLPI
ini_set('display_errors', 1);
error_reporting(E_ALL);
include '../conexion.php';

// --- CONFIGURACIÓN ---
$conf_categorias = [
    'Computer' => [
        'id_cat'      => 1, 
        'tabla_glpi'  => 'glpi_computers', 
        'tabla_mod'   => 'glpi_computermodels',
        'fk_model'    => 'computermodels_id', 
        'icono'       => '💻',
        'prefijo'     => 'PC'
    ],
    'Printer' => [
        'id_cat'      => 2, 
        'tabla_glpi'  => 'glpi_printers', 
        'tabla_mod'   => 'glpi_printermodels',
        'fk_model'    => 'printermodels_id', 
        'icono'       => '🖨️',
        'prefijo'     => 'IMP'
    ],
    'Monitor' => [
        'id_cat'      => 3, 
        'tabla_glpi'  => 'glpi_monitors', 
        'tabla_mod'   => 'glpi_monitormodels',
        'fk_model'    => 'monitormodels_id', 
        'icono'       => '🖥️',
        'prefijo'     => 'MON'
    ]
];

$nombre_db_glpi = "glpi_backup_restore"; 

echo "<h1>🚀 Sincronización Maestra (IDs Estables) V10.0</h1>";

function tablaExiste($conn, $db, $tabla) {
    $res = $conn->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$db' AND TABLE_NAME = '$tabla'");
    return ($res && $res->num_rows > 0);
}

// Verificar tablas auxiliares
$t_cpu = tablaExiste($conn, $nombre_db_glpi, 'glpi_items_deviceprocessors');
$t_ram = tablaExiste($conn, $nombre_db_glpi, 'glpi_items_devicememories');
$t_disco = tablaExiste($conn, $nombre_db_glpi, 'glpi_items_diskdrives');
$t_net = tablaExiste($conn, $nombre_db_glpi, 'glpi_networkports');
$t_infocom = tablaExiste($conn, $nombre_db_glpi, 'glpi_infocoms');

// Lista de códigos y series que sabemos que son "basura" o genéricos
$blacklist_codigos = ['DEFAULT STRING', 'S/N', 'SN', '0', '00', '000', 'SIN CODIGO', 'NO TIENE', 'NA', 'N/A', 'NO ASSET TAG', 'To be filled by O.E.M.'];
$blacklist_series = ['System Serial Number', 'To be filled by O.E.M.', 'Default String', '0', '123456789'];

foreach ($conf_categorias as $tipo_glpi => $cfg) {
    if (empty($cfg['id_cat'])) continue;
    echo "<hr><h3>".$cfg['icono']." Procesando: $tipo_glpi...</h3>";
    
    if (!tablaExiste($conn, $nombre_db_glpi, $cfg['tabla_glpi'])) {
        echo "Tabla principal no encontrada. Saltando.";
        continue;
    }

    // Consultas auxiliares
    $sql_cpu = ($tipo_glpi == 'Computer' && $t_cpu) ? "(SELECT designation FROM $nombre_db_glpi.glpi_deviceprocessors dp JOIN $nombre_db_glpi.glpi_items_deviceprocessors idp ON dp.id = idp.deviceprocessors_id WHERE idp.items_id = c.id AND idp.itemtype = 'Computer' LIMIT 1)" : "'N/A'";
    $sql_ram = ($tipo_glpi == 'Computer' && $t_ram) ? "(SELECT SUM(size) FROM $nombre_db_glpi.glpi_items_devicememories WHERE items_id = c.id AND itemtype = 'Computer')" : "0";
    $sql_disco = ($tipo_glpi == 'Computer' && $t_disco) ? "(SELECT CONCAT(capacity, ' MB') FROM $nombre_db_glpi.glpi_items_diskdrives WHERE items_id = c.id AND itemtype = 'Computer' LIMIT 1)" : "'N/A'";
    $sql_mac = $t_net ? "(SELECT mac FROM $nombre_db_glpi.glpi_networkports WHERE items_id = c.id AND itemtype = '$tipo_glpi' AND mac IS NOT NULL AND mac != '' LIMIT 1)" : "'N/A'";
    $sql_compra = $t_infocom ? "(SELECT buy_date FROM $nombre_db_glpi.glpi_infocoms WHERE items_id = c.id AND itemtype = '$tipo_glpi' LIMIT 1)" : "NULL";
    $sql_garantia = $t_infocom ? "(SELECT warranty_duration FROM $nombre_db_glpi.glpi_infocoms WHERE items_id = c.id AND itemtype = '$tipo_glpi' LIMIT 1)" : "'0'";
    $sql_oficina = "(SELECT g.name FROM $nombre_db_glpi.glpi_groups g JOIN $nombre_db_glpi.glpi_groups_users gu ON g.id = gu.groups_id WHERE gu.users_id = c.users_id LIMIT 1)";

    // Filtro especial para PC
    $filtro_extra = ($tipo_glpi == 'Computer') ? "AND c.is_dynamic = 1" : "";

    // AGREGAMOS c.id A LA CONSULTA PRINCIPAL
    $sql = "SELECT c.id as id_glpi, c.name, c.otherserial, c.serial, c.date_mod, m.name as modelo, manu.name as marca, loc.completename as ubicacion_glpi, u.firstname, u.realname, 
            $sql_cpu as cpu, $sql_ram as ram_mb, $sql_disco as disco_raw, $sql_mac as mac, $sql_oficina as nombre_oficina, $sql_compra as fecha_compra, $sql_garantia as garantia_meses
            FROM $nombre_db_glpi.".$cfg['tabla_glpi']." c
            LEFT JOIN $nombre_db_glpi.".$cfg['tabla_mod']." m ON c.".$cfg['fk_model']." = m.id
            LEFT JOIN $nombre_db_glpi.glpi_manufacturers manu ON c.manufacturers_id = manu.id
            LEFT JOIN $nombre_db_glpi.glpi_locations loc ON c.locations_id = loc.id
            LEFT JOIN $nombre_db_glpi.glpi_users u ON c.users_id = u.id
            WHERE c.is_deleted = 0 AND c.is_template = 0 $filtro_extra";

    $res = $conn->query($sql);
    
    if(!$res) { echo "<p style='color:red'>Error SQL: " . $conn->error . "</p>"; continue; }

    $cont_new = 0; $cont_upd = 0;

    echo "<ul style='font-size:0.8rem; max-height:300px; overflow-y:scroll;'>";
    
    while($fila = $res->fetch_assoc()) {
        
        // --- 1. DETERGENTE DE CÓDIGOS ---
        // Obtenemos el código original
        // --- 1. DETERGENTE DE CÓDIGOS (MODIFICADO V11) ---
        // Obtenemos el código original
        $codigo_raw = strtoupper(trim($fila['otherserial'] ?? $fila['name']));
        
        
        $codigo_final = "GLPI-" . $cfg['prefijo'] . "-" . $fila['id_glpi'];

        // (Opcional) Guardamos el código original en la descripción o serie para no perderlo
        $codigo_original = strtoupper(trim($fila['otherserial'] ?? $fila['name']));
        if (!empty($codigo_original) && $serie == '') {
             $serie = $codigo_original; // Si no tiene serie, ponemos el código original ahí como referencia
        }

        // Datos complementarios
        $serie = strtoupper(trim($fila['serial'] ?? ''));
        $marca = strtoupper(trim($fila['marca'] ?? 'GENERICO'));
        $modelo = strtoupper(trim($fila['modelo'] ?? ''));
        $mac = trim($fila['mac'] ?? '-');
        $fec = $fila['date_mod'];
        $fecha_compra = !empty($fila['fecha_compra']) ? "'".$fila['fecha_compra']."'" : "NULL";
        $garantia = (intval($fila['garantia_meses']) > 0) ? intval($fila['garantia_meses'])." Meses" : "Sin Garantía";
        
        $ubicacion = "SIN UBICACION";
        if (!empty($fila['ubicacion_glpi'])) { $parts = explode(' > ', $fila['ubicacion_glpi']); $ubicacion = strtoupper(trim(end($parts))); }
        
        $oficina_real = strtoupper(trim($fila['nombre_oficina'] ?? 'SIN ASIGNAR'));
        $nom = strtoupper(trim($fila['firstname'] ?? '')); $ape = strtoupper(trim($fila['realname'] ?? ''));
        
        // Gestión Personal (Simplificado)
        $id_personal = "NULL";
        if (!empty($nom) && !empty($ape)) {
            $q_per = $conn->query("SELECT id_personal FROM personal WHERE apellidos = '$ape' AND nombres = '$nom' LIMIT 1");
            if ($q_per && $q_per->num_rows > 0) { $id_personal = $q_per->fetch_assoc()['id_personal']; }
            else { 
                $conn->query("INSERT INTO personal (dni, nombres, apellidos, cargo, oficina, estado) VALUES (NULL, '$nom', '$ape', 'USUARIO GLPI', '$oficina_real', 'Activo')");
                $id_personal = $conn->insert_id; 
            }
        }

        // Hardware
        $cpu = '-'; $ram = '-'; $disco = '-';
        if($tipo_glpi == 'Computer') {
            $cpu = trim($fila['cpu'] ?? 'GENERICO');
            $r_mb = intval($fila['ram_mb']); $ram = ($r_mb > 0) ? round($r_mb/1024)." GB" : "-";
            $d_val = intval($fila['disco_raw']); $disco = ($d_val > 1000) ? round($d_val/1024)." GB" : "-";
        }
        $desc = ($tipo_glpi=='Computer'?'COMPUTADORA':($tipo_glpi=='Printer'?'IMPRESORA':'MONITOR')) . " " . $fila['name'];

        // --- 2. OPERACIÓN SIMPLE (INSERT O UPDATE) ---
        // Ahora confiamos ciegamente en $codigo_final porque si era genérico, lo hicimos único.
        $id_cat_destino = $cfg['id_cat'];
        
        $check = $conn->query("SELECT id_bien FROM bienes WHERE codigo_patrimonial = '$codigo_final'");

        if ($check->num_rows == 0) {
            // INSERTAR NUEVO
            $sql_ins = "INSERT INTO bienes (codigo_patrimonial, id_categoria, id_personal, descripcion, marca, modelo, serie, ubicacion, procesador, ram, disco, mac, ultimo_inventario, fecha_compra, garantia, estado_fisico, fecha_registro) 
                        VALUES ('$codigo_final', $id_cat_destino, $id_personal, '$desc', '$marca', '$modelo', '$serie', '$ubicacion', '$cpu', '$ram', '$disco', '$mac', '$fec', $fecha_compra, '$garantia', 'Bueno', NOW())";
            if($conn->query($sql_ins)) { 
                echo "<li style='color:green'>➕ Nuevo: $codigo_final</li>"; 
                $cont_new++; 
            }
        } else {
            // ACTUALIZAR EXISTENTE
            $id_existente = $check->fetch_assoc()['id_bien'];
            $set_per = ($id_personal != "NULL") ? ", id_personal = $id_personal" : "";
            $sql_upd = "UPDATE bienes SET id_categoria=$id_cat_destino, ubicacion='$ubicacion', procesador='$cpu', ram='$ram', disco='$disco', mac='$mac', ultimo_inventario='$fec', fecha_compra=$fecha_compra, garantia='$garantia' $set_per WHERE id_bien=$id_existente";
            if($conn->query($sql_upd)) { 
                echo "<li style='color:blue'>🔄 Act: $codigo_final</li>"; 
                $cont_upd++; 
            }
        }
    }
    echo "</ul><p><b>Resumen $tipo_glpi:</b> Nuevos: $cont_new | Actualizados: $cont_upd</p>";
}
?>