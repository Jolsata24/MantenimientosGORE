<?php
// reportes.php
include 'conexion.php';
session_start();

// Seguridad
if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: login.php");
    exit();
}

$page = 'reportes';

// --- CONSULTAS SQL PARA LOS GRÁFICOS ---

// 1. Datos para Gráfico de ESTADOS (Pastel)
$sql_estados = "SELECT estado_fisico, COUNT(*) as cantidad FROM bienes GROUP BY estado_fisico";
$res_estados = $conn->query($sql_estados);

$labels_estado = [];
$data_estado = [];
while($row = $res_estados->fetch_assoc()) {
    $labels_estado[] = $row['estado_fisico'];
    $data_estado[] = $row['cantidad'];
}

// 2. Datos para Gráfico de CATEGORÍAS (Barras)
$sql_cat = "SELECT c.nombre, COUNT(b.id_bien) as cantidad 
            FROM bienes b 
            JOIN categorias c ON b.id_categoria = c.id_categoria 
            GROUP BY c.nombre";
$res_cat = $conn->query($sql_cat);

$labels_cat = [];
$data_cat = [];
while($row = $res_cat->fetch_assoc()) {
    $labels_cat[] = $row['nombre'];
    $data_cat[] = $row['cantidad'];
}

// 3. Tabla: Top 5 Equipos con más gastos (Para la tabla roja)
$sql_gastos = "SELECT b.codigo_patrimonial, b.descripcion, SUM(m.costo) as total_gastado, COUNT(m.id_mantenimiento) as veces_reparado
               FROM mantenimientos m
               JOIN bienes b ON m.id_bien = b.id_bien
               GROUP BY b.id_bien
               ORDER BY total_gastado DESC
               LIMIT 5";
$res_gastos = $conn->query($sql_gastos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes Gerenciales | GORE Pasco</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/reportes.css">
</head>
<body>

    <div class="membrete-impresion">
        <img src="img/logo_gore.png" width="80" style="vertical-align: middle;">
        <div style="display: inline-block; vertical-align: middle; margin-left: 15px; text-align: left;">
            <h3 style="margin: 0; font-weight: bold;">GOBIERNO REGIONAL DE PASCO</h3>
            <h5 style="margin: 0; color: #555;">Gerencia de Administración - Unidad de Patrimonio</h5>
            <small>Reporte generado el: <?php echo date("d/m/Y H:i"); ?></small>
        </div>
    </div>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            
            <div class="page-header">
                <div>
                    <h2 class="titulo-seccion">
                        <i class="fas fa-chart-pie text-danger me-3 opacity-75"></i> Reportes y Estadísticas
                    </h2>
                    <small class="text-muted">Análisis visual del estado patrimonial</small>
                </div>
                <button onclick="window.print()" class="btn-print">
                    <i class="fas fa-print me-2"></i> Imprimir Informe
                </button>
            </div>

            <div class="row g-4 mb-4">
                
                <div class="col-md-6">
                    <div class="card card-grafico">
                        <div class="card-header-chart">
                            Estado Físico de los Bienes
                        </div>
                        <div class="card-body chart-container">
                            <canvas id="chartEstados"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-grafico">
                        <div class="card-header-chart">
                            Distribución por Categorías
                        </div>
                        <div class="card-body chart-container">
                            <canvas id="chartCategorias"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-tabla-gastos">
                <div class="header-gastos">
                    <i class="fas fa-money-bill-wave me-2"></i> Top 5 Activos con Mayor Gasto en Reparaciones
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-gastos table-striped mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Intervenciones</th>
                                    <th class="text-end">Gasto Acumulado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($res_gastos->num_rows > 0): ?>
                                    <?php while($fila = $res_gastos->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?php echo $fila['codigo_patrimonial']; ?></td>
                                        <td><?php echo $fila['descripcion']; ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary rounded-pill">
                                                <?php echo $fila['veces_reparado']; ?> veces
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="monto-gasto">
                                                S/ <?php echo number_format($fila['total_gastado'], 2); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="fas fa-info-circle me-1"></i> Aún no hay registros de mantenimientos con costos.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // -- CONFIGURACIÓN DE LOS GRÁFICOS --
        
        // 1. GRÁFICO DE ESTADOS (Dona)
        const ctxEstado = document.getElementById('chartEstados').getContext('2d');
        new Chart(ctxEstado, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($labels_estado); ?>,
                datasets: [{
                    data: <?php echo json_encode($data_estado); ?>,
                    backgroundColor: [
                        '#2E7D32', // Verde (Bueno)
                        '#FBC02D', // Amarillo (Regular)
                        '#C62828', // Rojo (Malo)
                        '#455A64'  // Gris (Baja/Otro)
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: { 
                    legend: { position: 'right' } 
                } 
            }
        });

        // 2. GRÁFICO DE CATEGORÍAS (Barras)
        const ctxCat = document.getElementById('chartCategorias').getContext('2d');
        new Chart(ctxCat, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels_cat); ?>,
                datasets: [{
                    label: 'Cantidad de Bienes',
                    data: <?php echo json_encode($data_cat); ?>,
                    backgroundColor: '#00609C', // Azul Institucional
                    borderRadius: 4
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { 
                    y: { beginAtZero: true, grid: { color: '#f0f0f0' } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</body>
</html>