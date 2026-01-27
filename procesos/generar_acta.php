<?php
// procesos/generar_acta.php
ini_set('memory_limit', '1024M');
require('../conexion.php');
require('fpdf/fpdf.php'); // Asegúrate que la ruta sea correcta
define('FPDF_FONTPATH', dirname(__FILE__) . '/fpdf/font/');

if (!isset($_GET['id'])) { die("Error: Faltan parámetros."); }

$id_bien = intval($_GET['id']);

// 1. OBTENER DATOS COMPLETOS
$sql = "SELECT b.*, c.nombre as nombre_categoria, p.nombres, p.apellidos, p.dni, p.cargo, p.oficina
        FROM bienes b 
        LEFT JOIN personal p ON b.id_personal = p.id_personal
        LEFT JOIN categorias c ON b.id_categoria = c.id_categoria
        WHERE b.id_bien = $id_bien";

$res = $conn->query($sql);
if ($res->num_rows == 0) { die("Activo no encontrado"); }
$bien = $res->fetch_assoc();

if (empty($bien['id_personal'])) {
    die("<h1>Error: Este activo no tiene custodio asignado.</h1><p>Primero asigna un usuario en el sistema.</p>");
}

// 2. CONFIGURACIÓN DEL PDF
class PDF extends FPDF {
    function Header() {
        // Logo: Asegúrate que AMBOS digan .jpg
        $ruta_logo = '../img/logo_gore.png'; // <--- Define la ruta aquí
        
        if (file_exists($ruta_logo)) {
            $this->Image($ruta_logo, 10, 8, 25);
        } else {
            // Esto evita que el sistema explote si olvidas la imagen
            $this->SetFont('Arial', 'B', 8);
            $this->Cell(25, 10, '[SIN LOGO]', 1, 0, 'C');
        }
        
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// TÍTULO
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('ACTA DE ASIGNACIÓN DE EQUIPO'), 0, 1, 'C');
$pdf->Ln(5);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 10, utf8_decode('Fecha de emisión: ' . date('d/m/Y')), 0, 1, 'R');
$pdf->Ln(5);

// CUERPO DEL TEXTO
$pdf->SetFont('Arial', '', 11);
$texto = "Por medio del presente documento, se hace entrega formal del equipo informático descrito a continuación al servidor público " . 
         strtoupper($bien['nombres'] . " " . $bien['apellidos']) . 
         ", identificado con DNI N° " . ($bien['dni'] ? $bien['dni'] : '__________') . 
         ", quien se desempeña en el cargo de " . strtoupper($bien['cargo'] ? $bien['cargo'] : '__________') . 
         " en la oficina de " . strtoupper($bien['oficina']) . ".";

$pdf->MultiCell(0, 6, utf8_decode($texto));
$pdf->Ln(10);

$pdf->MultiCell(0, 6, utf8_decode("El usuario asume la responsabilidad por el cuidado, custodia y buen uso del bien asignado, comprometiéndose a reportar cualquier falla o desperfecto a la oficina de Soporte Técnico."));
$pdf->Ln(10);

// DETALLES DEL EQUIPO (TABLA)
$pdf->SetFillColor(240, 240, 240);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 10, utf8_decode('  CARACTERÍSTICAS DEL BIEN'), 1, 1, 'L', true);

$pdf->SetFont('Arial', '', 10);
$ancho_col1 = 50;
$ancho_col2 = 140;

function filaTabla($pdf, $titulo, $valor, $w1, $w2) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($w1, 8, utf8_decode($titulo), 1);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($w2, 8, utf8_decode($valor), 1);
    $pdf->Ln();
}

filaTabla($pdf, " Código Patrimonial", $bien['codigo_patrimonial'], $ancho_col1, $ancho_col2);
filaTabla($pdf, " Tipo de Bien", $bien['nombre_categoria'] . " / " . $bien['tipo_equipo'], $ancho_col1, $ancho_col2);
filaTabla($pdf, " Marca", $bien['marca'], $ancho_col1, $ancho_col2);
filaTabla($pdf, " Modelo", $bien['modelo'], $ancho_col1, $ancho_col2);
filaTabla($pdf, " Serie (S/N)", $bien['serie'], $ancho_col1, $ancho_col2);
filaTabla($pdf, " Color", $bien['color'] ? $bien['color'] : 'Negro/Gris', $ancho_col1, $ancho_col2);
filaTabla($pdf, " Estado Físico", $bien['estado_fisico'], $ancho_col1, $ancho_col2);

// Specs si es PC
if ($bien['id_categoria'] == 1) { // Computadoras
    filaTabla($pdf, " Procesador", $bien['procesador'], $ancho_col1, $ancho_col2);
    filaTabla($pdf, " Memoria RAM", $bien['ram'], $ancho_col1, $ancho_col2);
    filaTabla($pdf, " Disco Duro", $bien['disco'], $ancho_col1, $ancho_col2);
    filaTabla($pdf, " Nombre de Equipo", $bien['descripcion'], $ancho_col1, $ancho_col2);
}

$pdf->Ln(20);

// FIRMAS
$y_firmas = $pdf->GetY() + 10;
$pdf->Line(20, $y_firmas, 90, $y_firmas);
$pdf->Line(120, $y_firmas, 190, $y_firmas);

$pdf->SetXY(20, $y_firmas + 2);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(70, 5, utf8_decode("ENTREGUÉ CONFORME"), 0, 1, 'C');
$pdf->SetX(20);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(70, 5, utf8_decode("Soporte Técnico / Patrimonio"), 0, 1, 'C');

$pdf->SetXY(120, $y_firmas + 2);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(70, 5, utf8_decode("RECIBÍ CONFORME"), 0, 1, 'C');
$pdf->SetX(120);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(70, 5, utf8_decode($bien['nombres'] . " " . $bien['apellidos']), 0, 1, 'C');
$pdf->SetX(120);
$pdf->Cell(70, 5, utf8_decode("DNI: " . $bien['dni']), 0, 1, 'C');

$pdf->Output('I', 'Acta_'.$bien['codigo_patrimonial'].'.pdf');
?>