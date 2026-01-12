<?php
// reportes.php
include 'conexion.php';
session_start();

if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: login.php");
    exit();
}

$page = 'reportes';

// --- CONSULTAS ---

// 1. Datos Agregados (Para Gráficos y Tablas Resumen)
$sql_estados = "SELECT estado_fisico, COUNT(*) as cantidad FROM bienes GROUP BY estado_fisico";
$res_estados = $conn->query($sql_estados);

// Guardamos datos en arrays para usarlos dos veces (Gráfico y Tabla)
$data_estados = [];
while($row = $res_estados->fetch_assoc()) { $data_estados[] = $row; }

$sql_cat = "SELECT c.nombre, COUNT(b.id_bien) as cantidad 
            FROM bienes b JOIN categorias c ON b.id_categoria = c.id_categoria 
            GROUP BY c.nombre";
$res_cat = $conn->query($sql_cat);

$data_categorias = [];
while($row = $res_cat->fetch_assoc()) { $data_categorias[] = $row; }

// 2. Top Gastos (Se mantiene)
$sql_gastos = "SELECT b.codigo_patrimonial, b.descripcion, SUM(m.costo) as total_gastado, COUNT(m.id_mantenimiento) as veces_reparado
               FROM mantenimientos m
               JOIN bienes b ON m.id_bien = b.id_bien
               GROUP BY b.id_bien
               ORDER BY total_gastado DESC LIMIT 5";
$res_gastos = $conn->query($sql_gastos);

// 3. NUEVA CONSULTA: LISTADO GENERAL (Solo para imprimir)
$sql_todo = "SELECT b.codigo_patrimonial, b.descripcion, b.marca, b.estado_fisico, 
             c.nombre as categoria, p.apellidos, p.nombres
             FROM bienes b 
             LEFT JOIN categorias c ON b.id_categoria = c.id_categoria
             LEFT JOIN personal p ON b.id_personal = p.id_personal
             ORDER BY b.codigo_patrimonial ASC";
$res_todo = $conn->query($sql_todo);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte General | GORE Pasco</title>
    <link rel="icon" type="image/png" href="img/logo_gore.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/reportes.css">
</head>
<body>

    <div class="membrete-impresion mb-4">
        <div class="row align-items-center border-bottom pb-3">
            <div class="col-2 text-center">
                <img src="img/logo_gore.png" width="80">
            </div>
            <div class="col-8 text-center">
                <h4 class="fw-bold mb-0">GOBIERNO REGIONAL DE PASCO</h4>
                <p class="mb-0 text-muted">Gerencia del area de TIC - Unidad de Patrimonio</p>
                <small>Informe Situacional de Activos Fijos</small>
            </div>
            <div class="col-2 text-end">
                <small class="d-block fw-bold"><?php echo date("d/m/Y"); ?></small>
                <small><?php echo date("H:i"); ?> hrs</small>
            </div>
        </div>
    </div>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            
            <div class="page-header d-print-none">
                <div>
                    <h2 class="titulo-seccion">
                        <i class="fas fa-chart-pie text-danger me-3 opacity-75"></i> Reportes y Estadísticas
                    </h2>
                    <small class="text-muted">Análisis visual del estado patrimonial</small>
                </div>
                <button onclick="window.print()" class="btn-print">
                    <i class="fas fa-print me-2"></i> Imprimir Informe Oficial
                </button>
            </div>

            <div class="row g-4 mb-4 d-print-none">
                <div class="col-md-6">
                    <div class="card card-grafico">
                        <div class="card-header-chart">Estado Físico (Gráfico)</div>
                        <div class="card-body chart-container"><canvas id="chartEstados"></canvas></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-grafico">
                        <div class="card-header-chart">Categorías (Gráfico)</div>
                        <div class="card-body chart-container"><canvas id="chartCategorias"></canvas></div>
                    </div>
                </div>
            </div>

            <div class="d-none d-print-block mb-4">
                <h5 class="fw-bold text-decoration-underline mb-3">1. Resumen Ejecutivo</h5>
                <div class="row">
                    <div class="col-6">
                        <table class="table table-bordered table-sm" style="font-size: 0.9rem;">
                            <thead class="table-light"><tr><th>Estado Físico</th><th class="text-end">Cantidad</th></tr></thead>
                            <tbody>
                                <?php foreach($data_estados as $d): ?>
                                <tr>
                                    <td><?php echo $d['estado_fisico']; ?></td>
                                    <td class="text-end fw-bold"><?php echo $d['cantidad']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-6">
                        <table class="table table-bordered table-sm" style="font-size: 0.9rem;">
                            <thead class="table-light"><tr><th>Categoría</th><th class="text-end">Cantidad</th></tr></thead>
                            <tbody>
                                <?php foreach($data_categorias as $d): ?>
                                <tr>
                                    <td><?php echo $d['nombre']; ?></td>
                                    <td class="text-end fw-bold"><?php echo $d['cantidad']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card card-tabla-gastos mb-4 break-inside-avoid">
                <div class="header-gastos py-2 px-3">
                    <i class="fas fa-money-bill-wave me-2"></i> 2. Top 5 Activos con Mayor Gasto en Mantenimiento
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0 table-sm">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th class="text-center">Intervenciones</th>
                                <th class="text-end">Gasto Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($res_gastos->num_rows > 0): ?>
                                <?php while($fila = $res_gastos->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo $fila['codigo_patrimonial']; ?></td>
                                    <td><?php echo $fila['descripcion']; ?></td>
                                    <td class="text-center"><?php echo $fila['veces_reparado']; ?></td>
                                    <td class="text-end fw-bold">S/ <?php echo number_format($fila['total_gastado'], 2); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted">Sin registros de gastos.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-none d-print-block">
                <div class="page-break-before"></div> <h5 class="fw-bold text-decoration-underline mb-3">3. Listado General de Inventario</h5>
                
                <table class="table table-bordered table-striped table-sm" style="font-size: 0.8rem;">
                    <thead class="table-dark text-white">
                        <tr>
                            <th>N°</th>
                            <th>CÓDIGO</th>
                            <th>DESCRIPCIÓN</th>
                            <th>MARCA</th>
                            <th>CUSTODIO</th>
                            <th class="text-center">ESTADO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $contador = 1;
                        if($res_todo->num_rows > 0): 
                            while($bien = $res_todo->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $contador++; ?></td>
                            <td class="fw-bold"><?php echo $bien['codigo_patrimonial']; ?></td>
                            <td><?php echo $bien['descripcion']; ?></td>
                            <td><?php echo $bien['marca']; ?></td>
                            <td>
                                <?php echo $bien['apellidos'] ? $bien['apellidos'].', '.$bien['nombres'] : '---'; ?>
                            </td>
                            <td class="text-center"><?php echo $bien['estado_fisico']; ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="6" class="text-center">No hay bienes registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <div class="row mt-5 pt-5 text-center break-inside-avoid">
                    <div class="col-4">
                        <div class="border-top border-dark mx-4 pt-2">FIRMAS DE...</div>
                    </div>
                    <div class="col-4">
                        <div class="border-top border-dark mx-4 pt-2">FIRMAS DE...</div>
                    </div>
                    <div class="col-4">
                        <div class="border-top border-dark mx-4 pt-2">FIRMAS DE...</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // CONFIGURACIÓN DE GRÁFICOS (Solo se ejecutan si existen en pantalla)
        // Reutilizamos los arrays PHP convertidos a JS
        const labelsCat = <?php echo json_encode(array_column($data_categorias, 'nombre')); ?>;
        const dataCat = <?php echo json_encode(array_column($data_categorias, 'cantidad')); ?>;
        
        const labelsEst = <?php echo json_encode(array_column($data_estados, 'estado_fisico')); ?>;
        const dataEst = <?php echo json_encode(array_column($data_estados, 'cantidad')); ?>;

        const ctxCat = document.getElementById('chartCategorias').getContext('2d');
        new Chart(ctxCat, {
            type: 'bar',
            data: { labels: labelsCat, datasets: [{ label: 'Activos', data: dataCat, backgroundColor: '#00609C' }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        const ctxEst = document.getElementById('chartEstados').getContext('2d');
        new Chart(ctxEst, {
            type: 'doughnut',
            data: { labels: labelsEst, datasets: [{ data: dataEst, backgroundColor: ['#2E7D32', '#FBC02D', '#C62828', '#555'], borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
        });
    </script>
</body>
</html>