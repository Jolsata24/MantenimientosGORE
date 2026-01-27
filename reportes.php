<?php
// reportes.php - CON COLUMNA HARDWARE Y UBICACIÓN
include 'conexion.php';
session_start();

if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: login.php");
    exit();
}

$page = 'reportes';

// --- CONSULTAS ---

// 1. Datos Agregados (Para Gráficos)
$sql_estados = "SELECT estado_fisico, COUNT(*) as cantidad FROM bienes GROUP BY estado_fisico";
$res_estados = $conn->query($sql_estados);
$data_estados = [];
while($row = $res_estados->fetch_assoc()) { $data_estados[] = $row; }

$sql_cat = "SELECT c.nombre, COUNT(b.id_bien) as cantidad 
            FROM bienes b JOIN categorias c ON b.id_categoria = c.id_categoria 
            GROUP BY c.nombre";
$res_cat = $conn->query($sql_cat);
$data_categorias = [];
while($row = $res_cat->fetch_assoc()) { $data_categorias[] = $row; }

// 2. Top Gastos
$sql_gastos = "SELECT b.codigo_patrimonial, b.descripcion, SUM(m.costo) as total_gastado, COUNT(m.id_mantenimiento) as veces_reparado
               FROM mantenimientos m
               JOIN bienes b ON m.id_bien = b.id_bien
               GROUP BY b.id_bien
               ORDER BY total_gastado DESC LIMIT 5";
$res_gastos = $conn->query($sql_gastos);

// 3. LISTADO GENERAL (Agregamos campos de Hardware y Ubicación)
$sql_todo = "SELECT b.id_categoria, b.codigo_patrimonial, b.descripcion, b.marca, b.estado_fisico, 
             b.procesador, b.ram, b.disco, b.ubicacion,
             c.nombre as categoria
             FROM bienes b 
             LEFT JOIN categorias c ON b.id_categoria = c.id_categoria
             ORDER BY b.id_categoria ASC, b.codigo_patrimonial ASC";
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

    <style>
        /* Estilos específicos para impresión */
        @media print {
            .sidebar, .btn-group, .page-header button, .no-print { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; }
            body { background: white; -webkit-print-color-adjust: exact; }
            #seccion-tabla-impresion { display: block !important; }
            #seccion-graficos { display: none !important; }
            
            table { width: 100% !important; border-collapse: collapse; font-size: 10px; } /* Fuente más pequeña para que quepa el hardware */
            th, td { border: 1px solid #000 !important; padding: 4px; vertical-align: middle; }
            
            /* Ajuste de anchos para que entre todo */
            th:nth-child(1) { width: 3%; } /* N */
            th:nth-child(2) { width: 10%; } /* Codigo */
            th:nth-child(3) { width: 20%; } /* Descripcion */
            th:nth-child(5) { width: 25%; } /* Hardware */
        }
    </style>
</head>
<body>

    <div class="membrete-impresion d-none d-print-block mb-4">
        <div class="row align-items-center border-bottom pb-3">
            <div class="col-2 text-center">
                <img src="img/logo_gore.png" width="80">
            </div>
            <div class="col-8 text-center">
                <h4 class="fw-bold mb-0">GOBIERNO REGIONAL DE PASCO</h4>
                <p class="mb-0 text-muted">Gerencia del area de TIC - Unidad de Patrimonio</p>
                <small id="titulo-impresion-dinamico">Informe Situacional de Activos Fijos</small>
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
            
            <div class="page-header d-print-none d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h2 class="titulo-seccion">
                        <i class="fas fa-chart-pie text-danger me-3 opacity-75"></i> Reportes
                    </h2>
                    <small class="text-muted">Estadísticas y Exportación</small>
                </div>
                
                <div class="btn-group shadow-sm" role="group">
                    <button type="button" class="btn btn-dark" onclick="imprimirReporte('todo')">
                        <i class="fas fa-print me-2"></i>Todo
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="imprimirReporte(1)">
                        <i class="fas fa-laptop me-1"></i>Computación
                    </button>
                    <button type="button" class="btn btn-outline-success" onclick="imprimirReporte(2)">
                        <i class="fas fa-print me-1"></i>Impresoras
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="imprimirReporte(3)">
                        <i class="fas fa-desktop me-1"></i>Monitores
                    </button>
                </div>
            </div>

            <div id="seccion-graficos" class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card card-grafico h-100 shadow-sm">
                        <div class="card-header bg-white fw-bold">Estado Físico del Inventario</div>
                        <div class="card-body chart-container" style="height: 250px;">
                            <canvas id="chartEstados"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-grafico h-100 shadow-sm">
                        <div class="card-header bg-white fw-bold">Distribución por Categorías</div>
                        <div class="card-body chart-container" style="height: 250px;">
                            <canvas id="chartCategorias"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 mt-4">
                     <div class="card shadow-sm border-0">
                        <div class="card-header bg-danger bg-opacity-10 text-danger fw-bold">
                            <i class="fas fa-money-bill-wave me-2"></i> Top 5 Activos con Mayor Gasto
                        </div>
                        <div class="card-body p-0">
                             <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead class="small text-muted">
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
                                                <td class="fw-bold small"><?php echo $fila['codigo_patrimonial']; ?></td>
                                                <td class="small"><?php echo $fila['descripcion']; ?></td>
                                                <td class="text-center small"><?php echo $fila['veces_reparado']; ?></td>
                                                <td class="text-end fw-bold text-danger">S/ <?php echo number_format($fila['total_gastado'], 2); ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="text-center text-muted">Sin registros.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                             </div>
                        </div>
                     </div>
                </div>
            </div>

            <div id="seccion-tabla-impresion" class="d-none d-print-block">
                
                <h5 class="fw-bold text-decoration-underline mb-3 mt-4">Detalle de Activos</h5>
                
                <table class="table table-bordered table-striped table-sm" style="font-size: 0.8rem;">
                    <thead class="table-dark text-white">
                        <tr>
                            <th>N°</th>
                            <th>CÓDIGO</th>
                            <th>DESCRIPCIÓN</th>
                            <th>MARCA</th>
                            <th>HARDWARE</th>
                            <th>UBICACIÓN</th>
                            <th class="text-center">ESTADO</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-reporte">
                        <?php 
                        $contador = 1;
                        if($res_todo->num_rows > 0): 
                            while($bien = $res_todo->fetch_assoc()):
                        ?>
                        <tr data-cat="<?php echo $bien['id_categoria']; ?>">
                            <td class="text-center"><?php echo $contador++; ?></td>
                            <td class="fw-bold"><?php echo $bien['codigo_patrimonial']; ?></td>
                            <td><?php echo $bien['descripcion']; ?></td>
                            <td><?php echo $bien['marca']; ?></td>
                            
                            <td class="small">
                                <?php if($bien['id_categoria'] == 1): ?>
                                    <div style="font-size: 0.85em; line-height: 1.2;">
                                        <span class="d-block"><strong>Proc:</strong> <?php echo $bien['procesador'] ?: '-'; ?></span>
                                        <span class="d-block"><strong>RAM:</strong> <?php echo $bien['ram'] ?: '-'; ?> | <strong>Disco:</strong> <?php echo $bien['disco'] ?: '-'; ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted text-center d-block">-</span>
                                <?php endif; ?>
                            </td>

                            <td><?php echo !empty($bien['ubicacion']) ? $bien['ubicacion'] : 'Sin asignar'; ?></td>
                            
                            <td class="text-center"><?php echo $bien['estado_fisico']; ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="7" class="text-center">No hay bienes registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <div class="row mt-5 pt-5 text-center break-inside-avoid">
                    <div class="col-4"><div class="border-top border-dark mx-4 pt-2">Responsable TIC</div></div>
                    <div class="col-4"><div class="border-top border-dark mx-4 pt-2">Patrimonio</div></div>
                    <div class="col-4"><div class="border-top border-dark mx-4 pt-2">V°B° Administración</div></div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // CONFIGURACIÓN DE GRÁFICOS
        const labelsCat = <?php echo json_encode(array_column($data_categorias, 'nombre')); ?>;
        const dataCat = <?php echo json_encode(array_column($data_categorias, 'cantidad')); ?>;
        
        const labelsEst = <?php echo json_encode(array_column($data_estados, 'estado_fisico')); ?>;
        const dataEst = <?php echo json_encode(array_column($data_estados, 'cantidad')); ?>;

        if(document.getElementById('chartCategorias')){
            new Chart(document.getElementById('chartCategorias'), {
                type: 'bar',
                data: { labels: labelsCat, datasets: [{ label: 'Activos', data: dataCat, backgroundColor: '#0d6efd', borderRadius: 5 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        }

        if(document.getElementById('chartEstados')){
            new Chart(document.getElementById('chartEstados'), {
                type: 'doughnut',
                data: { labels: labelsEst, datasets: [{ data: dataEst, backgroundColor: ['#198754', '#ffc107', '#dc3545', '#6c757d'], borderWidth: 0 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });
        }

        // --- FUNCIÓN DE IMPRESIÓN PERSONALIZADA ---
        function imprimirReporte(filtro) {
            const filas = document.querySelectorAll('#tbody-reporte tr');
            const titulo = document.getElementById('titulo-impresion-dinamico');
            
            filas.forEach(fila => {
                if (filtro === 'todo') {
                    fila.style.display = '';
                    titulo.innerText = "INFORME GENERAL DE ACTIVOS FIJOS (TODO)";
                } else {
                    if (fila.getAttribute('data-cat') == filtro) {
                        fila.style.display = '';
                    } else {
                        fila.style.display = 'none';
                    }
                    
                    if(filtro == 1) titulo.innerText = "REPORTE DE EQUIPOS DE CÓMPUTO";
                    else if(filtro == 2) titulo.innerText = "REPORTE DE IMPRESORAS";
                    else if(filtro == 3) titulo.innerText = "REPORTE DE MONITORES";
                }
            });

            window.print();
        }
    </script>
</body>
</html>