<?php
// procesos/backup.php
session_start();
include '../conexion.php';

// 1. SEGURIDAD: Solo rol 'sistemas' puede descargar backups
if (!isset($_SESSION['rol']) || strtolower($_SESSION['rol']) != 'sistemas') {
    die("Acceso denegado.");
}

// Configuración básica
$fecha = date("d-m-Y_H-i-s");
$nombre_archivo = "Backup_GORE_" . $fecha . ".sql";
$salida_sql = "";

// Encabezado del archivo SQL
$salida_sql .= "-- Copia de seguridad del sistema GORE PATRIMONIO\n";
$salida_sql .= "-- Fecha: " . date("d/m/Y H:i:s") . "\n";
$salida_sql .= "-- Base de datos: " . $base_datos . "\n\n";
$salida_sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
$salida_sql .= "SET time_zone = \"+00:00\";\n\n";

// 2. OBTENER LISTA DE TABLAS
$tablas = array();
$resultado = $conn->query("SHOW TABLES");
while ($fila = $resultado->fetch_row()) {
    $tablas[] = $fila[0];
}

// 3. RECORRER TABLAS Y GENERAR CÓDIGO SQL
foreach ($tablas as $tabla) {
    // A. Estructura de la tabla (CREATE TABLE)
    $res_create = $conn->query("SHOW CREATE TABLE $tabla");
    $fila_create = $res_create->fetch_row();
    
    $salida_sql .= "\n\n-- --------------------------------------------------------\n";
    $salida_sql .= "-- Estructura de tabla para la tabla `$tabla`\n";
    $salida_sql .= "-- --------------------------------------------------------\n\n";
    $salida_sql .= "DROP TABLE IF EXISTS `$tabla`;\n";
    $salida_sql .= $fila_create[1] . ";\n\n";

    // B. Datos de la tabla (INSERT INTO)
    $res_select = $conn->query("SELECT * FROM $tabla");
    $num_campos = $res_select->field_count;
    $num_filas = $res_select->num_rows;

    if ($num_filas > 0) {
        $salida_sql .= "-- Volcado de datos para la tabla `$tabla`\n";
        
        while ($row = $res_select->fetch_row()) {
            $salida_sql .= "INSERT INTO `$tabla` VALUES(";
            for ($j = 0; $j < $num_campos; $j++) {
                $row[$j] = addslashes($row[$j]);
                $row[$j] = str_replace("\n", "\\n", $row[$j]);
                
                if (isset($row[$j])) {
                    $salida_sql .= '"' . $row[$j] . '"';
                } else {
                    $salida_sql .= '""';
                }
                
                if ($j < ($num_campos - 1)) {
                    $salida_sql .= ',';
                }
            }
            $salida_sql .= ");\n";
        }
    }
}

// 4. FORZAR DESCARGA DEL ARCHIVO
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($nombre_archivo) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . strlen($salida_sql));

// Limpiar buffer por si acaso hay espacios en blanco antes
ob_clean();
flush();

echo $salida_sql;
exit;
?>