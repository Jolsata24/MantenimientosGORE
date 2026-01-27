<?php
// index.php
include 'conexion.php';
session_start();

if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: login.php");
    exit();
}

$page = 'dashboard';

// --- CONSULTAS KPI ---
$sql_total = "SELECT COUNT(*) as total FROM bienes";
$row_total = $conn->query($sql_total)->fetch_assoc();

$sql_malo = "SELECT COUNT(*) as total FROM bienes WHERE estado_fisico = 'Malo' OR estado_fisico = 'Baja'";
$row_malo = $conn->query($sql_malo)->fetch_assoc();

$sql_cat_total = "SELECT COUNT(*) as total FROM categorias";
$row_cat = $conn->query($sql_cat_total)->fetch_assoc();

// --- DATOS GRÁFICOS ---
$sql_estados = "SELECT estado_fisico, COUNT(*) as cantidad FROM bienes GROUP BY estado_fisico";
$res_estados = $conn->query($sql_estados);
$labels_estado = []; $data_estado = [];
while($row = $res_estados->fetch_assoc()) {
    $labels_estado[] = $row['estado_fisico'];
    $data_estado[] = $row['cantidad'];
}

$sql_graf_cat = "SELECT c.nombre, COUNT(b.id_bien) as cantidad 
                 FROM bienes b JOIN categorias c ON b.id_categoria = c.id_categoria 
                 GROUP BY c.nombre ORDER BY cantidad DESC LIMIT 5";
$res_graf_cat = $conn->query($sql_graf_cat);
$labels_cat = []; $data_cat = [];
while($row = $res_graf_cat->fetch_assoc()) {
    $labels_cat[] = $row['nombre'];
    $data_cat[] = $row['cantidad'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | GORE Pasco</title>
    <link rel="icon" type="image/png" href="img/logo_gore.png"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
            
            <div class="d-flex justify-content-between align-items-center page-header mb-4">
                <div class="d-flex align-items-center">
                    <button class="btn btn-primary d-md-none me-3" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2 class="fw-bold mb-0">Tablero de Control</h2>
                </div>
                <span class="text-muted small d-none d-sm-inline">
                    <i class="far fa-calendar-alt me-1"></i> <?php echo date("d/m/Y"); ?>
                </span>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card kpi-card kpi-azul h-100">
                        <div class="card-body p-4">
                            <div class="kpi-label">Total Activos</div>
                            <div class="kpi-value"><?php echo $row_total['total']; ?></div>
                            <i class="fas fa-laptop icon-bg"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card kpi-card kpi-rojo h-100">
                        <div class="card-body p-4">
                            <div class="kpi-label">Atención Requerida</div>
                            <div class="kpi-value"><?php echo $row_malo['total']; ?></div>
                            <i class="fas fa-tools icon-bg"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card kpi-card kpi-dorado h-100">
                        <div class="card-body p-4">
                            <div class="kpi-label">Categorías</div>
                            <div class="kpi-value"><?php echo $row_cat['total']; ?></div>
                            <i class="fas fa-layer-group icon-bg"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card content-card h-100">
                        <div class="card-header-custom d-flex justify-content-between">
                            <span><i class="fas fa-chart-bar me-2"></i> Distribución por Categorías (Top 5)</span>
                        </div>
                        <div class="card-body">
                            <div style="height: 300px;">
                                <canvas id="chartCategorias"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card content-card h-100">
                        <div class="card-header-custom">
                            <i class="fas fa-chart-pie me-2"></i> Estado Físico
                        </div>
                        <div class="card-body d-flex align-items-center justify-content-center">
                            <div style="height: 250px; width: 100%;">
                                <canvas id="chartEstados"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4 mb-4">
                <div class="col-12">
                    <div class="card content-card">
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush list-group-horizontal-md">
                                <a href="inventario.php" class="list-group-item list-group-item-action p-4 text-center border-0">
                                    <i class="fas fa-barcode fa-2x text-primary mb-2"></i>
                                    <h6 class="mb-0">Inventario</h6>
                                </a>
                                <a href="personal.php" class="list-group-item list-group-item-action p-4 text-center border-0 border-start">
                                    <i class="fas fa-users fa-2x text-success mb-2"></i>
                                    <h6 class="mb-0">Personal</h6>
                                </a>
                                <a href="reportes.php" class="list-group-item list-group-item-action p-4 text-center border-0 border-start">
                                    <i class="fas fa-file-pdf fa-2x text-danger mb-2"></i>
                                    <h6 class="mb-0">Reportes</h6>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // CONFIGURACIÓN DE GRÁFICOS
        const ctxCat = document.getElementById('chartCategorias').getContext('2d');
        new Chart(ctxCat, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels_cat); ?>,
                datasets: [{
                    label: 'Cantidad',
                    data: <?php echo json_encode($data_cat); ?>,
                    backgroundColor: '#00609C',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        const ctxEst = document.getElementById('chartEstados').getContext('2d');
        new Chart(ctxEst, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($labels_estado); ?>,
                datasets: [{
                    data: <?php echo json_encode($data_estado); ?>,
                    backgroundColor: ['#2E7D32', '#FBC02D', '#C62828', '#555'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>
</body>
</html>