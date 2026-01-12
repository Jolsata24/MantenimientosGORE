<?php
// reportes.php
include 'conexion.php';
$page = 'reportes'; // Para iluminar el sidebar

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

// 3. Tabla: Top 5 Equipos con más gastos en mantenimiento
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
    
    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .main-card { border-radius: 15px; border: none; }
        
        /* ESTILOS PARA IMPRESIÓN (Cuando presionas Ctrl+P) */
        @media print {
            .sidebar-container, .btn-print, .navbar-toggler { display: none !important; }
            .main-content { margin-left: 0 !important; width: 100% !important; }
            .card { border: 1px solid #ccc !important; box-shadow: none !important; }
        }
    </style>
</head>
<body>
    <div class="d-none d-print-block text-center mb-4">
    <img src="img/logo_gore.png" width="100">
    <h3 class="mt-2">GOBIERNO REGIONAL DE PASCO</h3>
    <h5 class="text-muted">Gerencia de Administración - Unidad de Patrimonio</h5>
    <hr>
</div>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark"><i class="fas fa-chart-pie me-2 text-danger"></i>Reportes y Estadísticas</h2>
                    <p class="text-muted">Análisis visual del estado patrimonial</p>
                </div>
                <button onclick="window.print()" class="btn btn-dark btn-print shadow-sm">
                    <i class="fas fa-print me-2"></i> Imprimir Reporte
                </button>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card main-card shadow h-100">
                        <div class="card-header bg-white fw-bold border-bottom-0">
                            Estado Físico de los Bienes
                        </div>
                        <div class="card-body">
                            <canvas id="chartEstados"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="card main-card shadow h-100">
                        <div class="card-header bg-white fw-bold border-bottom-0">
                            Distribución por Categorías
                        </div>
                        <div class="card-body">
                            <canvas id="chartCategorias"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card main-card shadow">
                        <div class="card-header bg-danger text-white">
                            <i class="fas fa-money-bill-wave me-2"></i> Top 5 Activos con Mayor Gasto en Reparaciones
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Código</th>
                                            <th>Descripción</th>
                                            <th class="text-center">Veces Reparado</th>
                                            <th class="text-end">Costo Total Acumulado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if($res_gastos->num_rows > 0): ?>
                                            <?php while($fila = $res_gastos->fetch_assoc()): ?>
                                            <tr>
                                                <td class="fw-bold"><?php echo $fila['codigo_patrimonial']; ?></td>
                                                <td><?php echo $fila['descripcion']; ?></td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary rounded-pill">
                                                        <?php echo $fila['veces_reparado']; ?>
                                                    </span>
                                                </td>
                                                <td class="text-end fw-bold text-danger">
                                                    S/ <?php echo number_format($fila['total_gastado'], 2); ?>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="text-center py-3">Aún no hay registros de mantenimientos con costos.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
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
        // 1. CONFIGURACIÓN GRÁFICO DE ESTADOS (PASTEL)
        const ctxEstado = document.getElementById('chartEstados').getContext('2d');
        new Chart(ctxEstado, {
            type: 'doughnut', // Tipo Dona
            data: {
                labels: <?php echo json_encode($labels_estado); ?>,
                datasets: [{
                    data: <?php echo json_encode($data_estado); ?>,
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#6c757d'], // Colores: Verde, Amarillo, Rojo, Gris
                    borderWidth: 1
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // 2. CONFIGURACIÓN GRÁFICO DE CATEGORÍAS (BARRAS)
        const ctxCat = document.getElementById('chartCategorias').getContext('2d');
        new Chart(ctxCat, {
            type: 'bar', // Tipo Barras
            data: {
                labels: <?php echo json_encode($labels_cat); ?>,
                datasets: [{
                    label: 'Cantidad de Bienes',
                    data: <?php echo json_encode($data_cat); ?>,
                    backgroundColor: '#8B0000', // Rojo Institucional
                    borderRadius: 5
                }]
            },
            options: { 
                responsive: true, 
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
</body>
</html>