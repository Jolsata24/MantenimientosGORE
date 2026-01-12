<?php
// index.php
include 'conexion.php';
session_start(); // ¡Importante para seguridad!

// Verificar si está logeado (Seguridad básica)
if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: login.php");
    exit();
}

$page = 'dashboard';

// CONSULTAS SQL
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            
            <div class="d-flex justify-content-between align-items-center page-header">
                <h2 class="fw-bold mb-0">Tablero de Control</h2>
                <span class="text-muted small">
                    <i class="far fa-calendar-alt me-1"></i> <?php echo date("d/m/Y"); ?>
                </span>
            </div>

            <div class="row g-4">
                
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
                            <div class="kpi-label">Para Mantenimiento</div>
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

            <div class="row mt-4 g-4">
                
                <div class="col-lg-8">
                    <div class="card content-card">
                        <div class="card-header-custom">
                            <i class="fas fa-chart-area me-2"></i> Resumen de Movimientos
                        </div>
                        <div class="card-body text-center py-5">
                            <i class="fas fa-chart-bar fa-3x text-muted opacity-25 mb-3"></i>
                            <p class="text-muted">No hay movimientos recientes para mostrar en el gráfico.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card content-card">
                        <div class="card-header-custom">
                            <i class="fas fa-bolt me-2 text-warning"></i> Accesos Directos
                        </div>
                        <div class="list-group list-group-flush shortcut-list">
                            <a href="inventario.php" class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-center">
                                    <div class="shortcut-icon"><i class="fas fa-list"></i></div>
                                    <span>Ver Inventario</span>
                                </div>
                                <i class="fas fa-chevron-right small"></i>
                            </a>
                            <a href="reportes.php" class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-center">
                                    <div class="shortcut-icon"><i class="fas fa-file-pdf"></i></div>
                                    <span>Generar Reportes</span>
                                </div>
                                <i class="fas fa-chevron-right small"></i>
                            </a>
                            <a href="personal.php" class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-center">
                                    <div class="shortcut-icon"><i class="fas fa-users"></i></div>
                                    <span>Gestionar Personal</span>
                                </div>
                                <i class="fas fa-chevron-right small"></i>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>