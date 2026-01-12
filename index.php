<?php
// index.php
include 'conexion.php';

// Definimos la página actual para pintar el menú
$page = 'dashboard';

// CONSULTAS RÁPIDAS
$sql_total = "SELECT COUNT(*) as total FROM bienes";
$row_total = $conn->query($sql_total)->fetch_assoc();

$sql_malo = "SELECT COUNT(*) as total FROM bienes WHERE estado_fisico = 'Malo'";
$row_malo = $conn->query($sql_malo)->fetch_assoc();

$sql_cat = "SELECT COUNT(*) as total FROM categorias";
$row_cat = $conn->query($sql_cat)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | GORE Pasco</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .card-kpi { border: none; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.2s; }
        .card-kpi:hover { transform: translateY(-5px); }
        .icon-box { font-size: 2.5rem; opacity: 0.8; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        
        <div class="container-fluid">
            <h2 class="mb-4 text-dark border-bottom pb-2">Tablero de Control</h2>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card card-kpi bg-primary text-white h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Total Activos</h5>
                                <h1 class="display-4 fw-bold"><?php echo $row_total['total']; ?></h1>
                            </div>
                            <div class="icon-box"><i class="fas fa-laptop"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card card-kpi bg-danger text-white h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Estado Crítico</h5>
                                <h1 class="display-4 fw-bold"><?php echo $row_malo['total']; ?></h1>
                            </div>
                            <div class="icon-box"><i class="fas fa-exclamation-triangle"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card card-kpi bg-success text-white h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Categorías</h5>
                                <h1 class="display-4 fw-bold"><?php echo $row_cat['total']; ?></h1>
                            </div>
                            <div class="icon-box"><i class="fas fa-boxes"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white fw-bold">Rendimiento Mensual</div>
                        <div class="card-body text-center py-5">
                            <i class="fas fa-chart-area fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aquí podrías integrar Chart.js más adelante para ver estadísticas reales.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white fw-bold">Accesos Directos</div>
                        <div class="list-group list-group-flush">
                            <a href="inventario.php" class="list-group-item list-group-item-action"><i class="fas fa-list me-2"></i> Ver Inventario Completo</a>
                            <a href="#" class="list-group-item list-group-item-action"><i class="fas fa-print me-2"></i> Reporte PDF</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>