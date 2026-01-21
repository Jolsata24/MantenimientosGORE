<?php
// procesos/importar_glpi.php
// V8.0 - UNIVERSAL: IMPORTA PC, IMPRESORAS Y MONITORES
ini_set('display_errors', 1);
error_reporting(E_ALL);
include '../conexion.php';

// --- ⚙️ CONFIGURACIÓN DE CATEGORÍAS (¡EDITA ESTO!) ---
// Pon aquí el ID que tiene cada categoría en tu tabla 'categorias'
$conf_categorias = [
    'Computer' => [
        'id_cat'      => 1,   // ID para Computadoras en tu sistema
        'tabla_glpi'  => 'glpi_computers',
        'tabla_mod'   => 'glpi_computermodels',
        'fk_model'    => 'computermodels_id',
        'icono'       => '💻'
    ],
    'Printer' => [
        'id_cat'      => 2,   // ID para Impresoras (¡VERIFICA TU BD!)
        'tabla_glpi'  => 'glpi_printers',
        'tabla_mod'   => 'glpi_printermodels',
        'fk_model'    => 'printermodels_id',
        'icono'       => '🖨️'
    ],
    'Monitor' => [
        'id_cat'      => 3,   // ID para Monitores (¡VERIFICA TU BD!)
        'tabla_glpi'  => 'glpi_monitors',
        'tabla_mod'   => 'glpi_monitormodels',
        'fk_model'    => 'monitormodels_id',
        'icono'       => '🖥️'
    ]
];

$nombre_db_glpi = "glpi_backup_restore"; 

echo "<h1>🚀 Sincronización Universal GLPI</h1>";

// Función para verificar tablas
function tablaExiste($conn, $db, $tabla) {
    $res = $conn->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$db' AND TABLE_NAME = '$tabla'");
    return ($res && $res->num_rows > 0);
}

// Verificar tablas auxiliares globales
$t_cpu = tablaExiste($conn, $nombre_db_glpi, 'glpi_items_deviceprocessors');
$t_ram = tablaExiste($conn, $nombre_db_glpi, 'glpi_items_devicememories');
$t_disco = tablaExiste($conn, $nombre_db_glpi, 'glpi_items_diskdrives');
$t_net = tablaExiste($conn, $nombre_db_glpi, 'glpi_networkports');
$t_infocom = tablaExiste($conn, $nombre_db_glpi, 'glpi_infocoms');

// --- BUCLE PRINCIPAL (Recorre cada tipo de equipo) ---
foreach ($conf_categorias as $tipo_glpi => $cfg) {
    
    // Si el usuario puso ID 0 o null, saltamos este tipo
    if (empty($cfg['id_cat'])) continue;

    echo "<hr><h3>".$cfg['icono']." Procesando: $tipo_glpi...</h3>";
    
    if (!tablaExiste($conn, $nombre_db_glpi, $cfg['tabla_glpi'])) {
        echo "<p style='color:gray'>⚠️ Tabla ".$cfg['tabla_glpi']." no encontrada. Saltando.</p>";
        continue;
    }

    // 1. CONSTRUCCIÓN DE SUB-CONSULTAS DINÁMICAS
    // Hardware (Solo aplica a Computers, para otros ponemos NULL)
    $sql_cpu = ($tipo_glpi == 'Computer' && $t_cpu) ? "(SELECT designation FROM $nombre_db_glpi.glpi_deviceprocessors dp JOIN $nombre_db_glpi.glpi_items_deviceprocessors idp ON dp.id = idp.deviceprocessors_id WHERE idp.items_id = c.id AND idp.itemtype = 'Computer' LIMIT 1)" : "'N/A'";
    
    $sql_ram = ($tipo_glpi == 'Computer' && $t_ram) ? "(SELECT SUM(size) FROM $nombre_db_glpi.glpi_items_devicememories WHERE items_id = c.id AND itemtype = 'Computer')" : "0";
    
    $sql_disco = ($tipo_glpi == 'Computer' && $t_disco) ? "(SELECT CONCAT(capacity, ' MB') FROM $nombre_db_glpi.glpi_items_diskdrives WHERE items_id = c.id AND itemtype = 'Computer' LIMIT 1)" : "'N/A'";

    // Red (Aplica a todos los que tengan puerto de red)
    $sql_mac = $t_net ? "(SELECT mac FROM $nombre_db_glpi.glpi_networkports WHERE items_id = c.id AND itemtype = '$tipo_glpi' AND mac IS NOT NULL AND mac != '' LIMIT 1)" : "'N/A'";
    
    // Datos Financieros (Aplica a todos)
    $sql_compra = $t_infocom ? "(SELECT buy_date FROM $nombre_db_glpi.glpi_infocoms WHERE items_id = c.id AND itemtype = '$tipo_glpi' LIMIT 1)" : "NULL";
    $sql_garantia = $t_infocom ? "(SELECT warranty_duration FROM $nombre_db_glpi.glpi_infocoms WHERE items_id = c.id AND itemtype = '$tipo_glpi' LIMIT 1)" : "'0'";

    // Oficina (Grupo)
    $sql_oficina = "(SELECT g.name FROM $nombre_db_glpi.glpi_groups g JOIN $nombre_db_glpi.glpi_groups_users gu ON g.id = gu.groups_id WHERE gu.users_id = c.users_id LIMIT 1)";

    // 2. CONSULTA MAESTRA
    $tabla_main = $nombre_db_glpi . "." . $cfg['tabla_glpi'];
    $tabla_model = $nombre_db_glpi . "." . $cfg['tabla_mod'];
    $col_model_id = $cfg['fk_model'];

    $sql = "
        SELECT 
            c.name AS nombre_pc,
            c.otherserial AS codigo_inventario,
            c.serial AS serie,
            c.date_mod AS ultimo_contacto,
            m.name AS modelo,
            manu.name AS marca,
            loc.completename AS ubicacion_glpi,
            u.firstname AS nombres,
            u.realname AS apellidos,
            
            $sql_cpu as cpu,
            $sql_ram as ram_mb,
            $sql_disco as disco_raw,
            $sql_mac as mac,
            $sql_oficina as nombre_oficina,
            $sql_compra as fecha_compra,
            $sql_garantia as garantia_meses

        FROM $tabla_main c
        LEFT JOIN $tabla_model m ON c.$col_model_id = m.id
        LEFT JOIN $nombre_db_glpi.glpi_manufacturers manu ON c.manufacturers_id = manu.id
        LEFT JOIN $nombre_db_glpi.glpi_locations loc ON c.locations_id = loc.id
        LEFT JOIN $nombre_db_glpi.glpi_users u ON c.users_id = u.id
        WHERE c.is_deleted = 0 AND c.is_template = 0
    ";

    $res = $conn->query($sql);
    if(!$res) { echo "<p style='color:red'>Error SQL en $tipo_glpi: ".$conn->error."</p>"; continue; }

    echo "<ul>";
    $cont_new = 0; $cont_upd = 0;

    while($fila = $res->fetch_assoc()) {
        
        // --- PROCESAMIENTO COMÚN ---
        $codigo = strtoupper(trim($fila['codigo_inventario'] ?? $fila['nombre_pc']));
        if(empty($codigo)) $codigo = "S/C-" . rand(10000,99999);
        
        $marca = strtoupper(trim($fila['marca'] ?? 'GENERICO'));
        $modelo = strtoupper(trim($fila['modelo'] ?? ''));
        $serie = strtoupper(trim($fila['serie'] ?? ''));
        $mac = trim($fila['mac'] ?? '-');
        $fec = $fila['ultimo_contacto'];
        $fecha_compra = !empty($fila['fecha_compra']) ? "'".$fila['fecha_compra']."'" : "NULL";
        $garantia = (intval($fila['garantia_meses'] ?? 0) > 0) ? intval($fila['garantia_meses'])." Meses" : "Sin Garantía";
        
        // Ubicación
        $ubicacion = "SIN UBICACION";
        if (!empty($fila['ubicacion_glpi'])) {
            $parts = explode(' > ', $fila['ubicacion_glpi']);
            $ubicacion = strtoupper(trim(end($parts)));
        }

        // Hardware (Solo PC)
        $cpu = ($tipo_glpi == 'Computer') ? trim($fila['cpu'] ?? 'GENERICO') : '-';
        $ram = '-'; $disco = '-';
        if($tipo_glpi == 'Computer') {
            $r_mb = intval($fila['ram_mb']); $ram = ($r_mb > 0) ? round($r_mb/1024)." GB" : "-";
            $d_val = intval($fila['disco_raw'] ?? 0); $disco = ($d_val > 1000) ? round($d_val/1024)." GB" : "-";
        }

        // Descripción automática
        $tipo_esp = ($tipo_glpi=='Computer')?'COMPUTADORA':(($tipo_glpi=='Printer')?'IMPRESORA':'MONITOR');
        $desc = "$tipo_esp " . strtoupper($fila['nombre_pc']);

        // Personal (Oficina)
        $oficina_real = strtoupper(trim($fila['nombre_oficina'] ?? 'SIN ASIGNAR'));
        if(empty($oficina_real)) $oficina_real = 'SIN ASIGNAR';
        
        $nom = strtoupper(trim($fila['nombres'] ?? '')); $ape = strtoupper(trim($fila['apellidos'] ?? ''));
        $id_personal = "NULL";
        
        if (!empty($nom) && !empty($ape)) {
            $q_per = $conn->query("SELECT id_personal FROM personal WHERE apellidos = '$ape' AND nombres = '$nom' LIMIT 1");
            if ($q_per && $q_per->num_rows > 0) {
                $id_personal = $q_per->fetch_assoc()['id_personal'];
                $conn->query("UPDATE personal SET oficina = '$oficina_real' WHERE id_personal = $id_personal AND oficina = 'SIN ASIGNAR'");
            } else {
                $conn->query("INSERT INTO personal (dni, nombres, apellidos, cargo, oficina, estado) VALUES (NULL, '$nom', '$ape', 'USUARIO GLPI', '$oficina_real', 'Activo')");
                $id_personal = $conn->insert_id;
            }
        }

        // INSERT / UPDATE
        $check = $conn->query("SELECT id_bien FROM bienes WHERE codigo_patrimonial = '$codigo'");
        $id_cat_destino = $cfg['id_cat']; // ID Correcto para este tipo

        if($check->num_rows == 0) {
            $sql_ins = "INSERT INTO bienes (codigo_patrimonial, id_categoria, id_personal, descripcion, marca, modelo, serie, ubicacion, procesador, ram, disco, mac, ultimo_inventario, fecha_compra, garantia, estado_fisico, fecha_registro) 
                        VALUES ('$codigo', $id_cat_destino, $id_personal, '$desc', '$marca', '$modelo', '$serie', '$ubicacion', '$cpu', '$ram', '$disco', '$mac', '$fec', $fecha_compra, '$garantia', 'Bueno', NOW())";
            if($conn->query($sql_ins)) { echo "<li style='color:green'>Nuevo: $codigo</li>"; $cont_new++; }
        } else {
            $set_per = ($id_personal != "NULL") ? ", id_personal = $id_personal" : "";
            $sql_upd = "UPDATE bienes SET id_categoria=$id_cat_destino, ubicacion='$ubicacion', procesador='$cpu', ram='$ram', disco='$disco', mac='$mac', ultimo_inventario='$fec', fecha_compra=$fecha_compra, garantia='$garantia' $set_per WHERE codigo_patrimonial='$codigo'";
            if($conn->query($sql_upd)) { echo "<li style='color:orange'>Act: $codigo</li>"; $cont_upd++; }
        }
    }
    echo "</ul><p>Total $tipo_glpi: Nuevos $cont_new | Act $cont_upd</p>";
}
?>