<?php
include 'conexion.php';
// ... (Mantén tu lógica PHP de arriba igual, solo cambia el HTML) ...
// Copia desde la línea 1 hasta el cierre de PHP ?> del archivo original
$id_bien = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_bien == 0) { die("Error: No se especificó un activo."); }
$sql_info = "SELECT * FROM bienes WHERE id_bien = $id_bien";
$res_info = $conn->query($sql_info);
$bien = $res_info->fetch_assoc();
if (!$bien) { die("Activo no encontrado."); }
$sql_last = "SELECT fecha_realizacion FROM mantenimientos WHERE id_bien = $id_bien ORDER BY fecha_realizacion DESC LIMIT 1";
$res_last = $conn->query($sql_last);
$ultimo_mant = $res_last->fetch_assoc();
$fecha_ultimo = ($ultimo_mant) ? date("d/m/Y", strtotime($ultimo_mant['fecha_realizacion'])) : "Sin registro";
$sql_historial = "SELECT * FROM mantenimientos WHERE id_bien = $id_bien ORDER BY fecha_realizacion DESC";
$res_historial = $conn->query($sql_historial);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Ficha <?php echo $bien['codigo_patrimonial']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #e9ecef; }
        .header-ficha {
            background: linear-gradient(45deg, #8B0000, #c0392b);
            color: white;
            padding: 30px 20px;
            border-radius: 0 0 25px 25px;
            margin-bottom: -40px; /* Efecto flotante */
            box-shadow: 0 4px 15px rgba(139, 0, 0, 0.3);
        }
        .card-flotante {
            border-radius: 15px;
            border: none;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        .timeline-item {
            border-left: 3px solid #8B0000;
            padding-left: 20px;
            margin-bottom: 20px;
            position: relative;
        }
        .timeline-item::before {
            content: '';
            width: 12px; height: 12px;
            background: #8B0000;
            border-radius: 50%;
            position: absolute;
            left: -7px; top: 5px;
        }
    </style>
</head>
<body>

    <div class="header-ficha text-center">
        <h4 class="fw-bold mb-0">Ficha Patrimonial</h4>
        <small class="opacity-75">Gobierno Regional de Pasco</small>
    </div>

    <div class="container pb-5">
        <div class="card card-flotante mb-4 mt-3">
            <div class="card-body text-center p-4">
                <div class="mb-3 position-relative d-inline-block">
                    <div class="bg-light rounded-circle p-4 d-inline-block shadow-sm">
                        <i class="fas fa-laptop fa-4x text-secondary"></i>
                    </div>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-<?php echo ($bien['estado_fisico']=='Bueno')?'success':'warning'; ?> border border-white">
                        <?php echo $bien['estado_fisico']; ?>
                    </span>
                </div>

                <h2 class="fw-bold text-dark mb-0"><?php echo $bien['codigo_patrimonial']; ?></h2>
                <p class="text-muted mb-3"><?php echo $bien['descripcion']; ?></p>

                <div class="row g-2 text-start bg-light p-3 rounded">
                    <div class="col-6"><small class="text-muted d-block">Marca</small><strong><?php echo $bien['marca']; ?></strong></div>
                    <div class="col-6"><small class="text-muted d-block">Modelo</small><strong><?php echo $bien['modelo']; ?></strong></div>
                    <div class="col-12 mt-2 border-top pt-2"><small class="text-muted d-block">Serie</small><span class="font-monospace"><?php echo $bien['serie']; ?></span></div>
                </div>
            </div>
        </div>

        <h5 class="ms-2 mb-3 text-secondary"><i class="fas fa-history me-2"></i>Historial Técnico</h5>
        
        <div class="card card-flotante">
            <div class="card-body">
                <?php if ($res_historial->num_rows > 0): ?>
                    <div class="mt-2">
                        <?php while($row = $res_historial->fetch_assoc()): ?>
                            <div class="timeline-item">
                                <div class="d-flex justify-content-between">
                                    <strong class="text-danger"><?php echo $row['tipo_evento']; ?></strong>
                                    <small class="text-muted"><?php echo date("d/m/Y", strtotime($row['fecha_realizacion'])); ?></small>
                                </div>
                                <p class="mb-1 text-dark"><?php echo $row['detalle_tecnico']; ?></p>
                                <small class="text-muted fst-italic">Téc: <?php echo $row['tecnico_responsable']; ?></small>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                        <p>Sin incidentes reportados.</p>
                    </div>
                <?php endif; ?>
                
                <div class="d-grid mt-3">
                    <button class="btn btn-outline-danger">
                        <i class="fas fa-plus me-2"></i> Reportar Incidente
                    </button>
                    <a href="index.php" class="btn btn-link text-muted mt-2">Volver al Dashboard</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>