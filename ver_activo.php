<?php
include 'conexion.php';

// 1. Obtener el ID del bien desde la URL (ej: ver_activo.php?id=1)
$id_bien = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_bien == 0) {
    die("Error: No se especificó un activo.");
}

// 2. Consulta de DATOS GENERALES
$sql_info = "SELECT * FROM bienes WHERE id_bien = $id_bien";
$res_info = $conn->query($sql_info);
$bien = $res_info->fetch_assoc();

if (!$bien) { die("Activo no encontrado."); }

// 3. Consulta de ÚLTIMO MANTENIMIENTO (Para el resumen rápido)
$sql_last = "SELECT fecha_realizacion FROM mantenimientos WHERE id_bien = $id_bien ORDER BY fecha_realizacion DESC LIMIT 1";
$res_last = $conn->query($sql_last);
$ultimo_mant = $res_last->fetch_assoc();
$fecha_ultimo = ($ultimo_mant) ? date("d/m/Y", strtotime($ultimo_mant['fecha_realizacion'])) : "Nunca";

// 4. Consulta del HISTORIAL COMPLETO DE MANTENIMIENTOS
$sql_historial = "SELECT * FROM mantenimientos WHERE id_bien = $id_bien ORDER BY fecha_realizacion DESC";
$res_historial = $conn->query($sql_historial);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha Técnica | <?php echo $bien['codigo_patrimonial']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

<div class="container mt-4">
    <a href="index.php" class="btn btn-outline-secondary mb-3 d-none d-md-block">
    <i class="fas fa-arrow-left"></i> Volver al Dashboard
</a>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white text-center">
                    <h5 class="mb-0">Ficha Patrimonial</h5>
                </div>
                <div class="card-body text-center">
                    <i class="fas fa-laptop fa-5x text-secondary mb-3"></i>
                    
                    <h3 class="fw-bold"><?php echo $bien['codigo_patrimonial']; ?></h3>
                    <p class="text-muted"><?php echo $bien['descripcion']; ?></p>
                    
                    <hr>
                    <div class="text-start">
                        <p><strong>Marca:</strong> <?php echo $bien['marca']; ?></p>
                        <p><strong>Modelo:</strong> <?php echo $bien['modelo']; ?></p>
                        <p><strong>Serie:</strong> <?php echo $bien['serie']; ?></p>
                        <p><strong>Estado:</strong> 
                            <span class="badge bg-<?php echo ($bien['estado_fisico']=='Bueno')?'success':'warning'; ?>">
                                <?php echo $bien['estado_fisico']; ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-warning">
                <div class="card-body text-center">
                    <h6 class="text-muted">Último Mantenimiento</h6>
                    <h2 class="text-warning fw-bold"><?php echo $fecha_ultimo; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><i class="fas fa-history me-2"></i> Trazabilidad Técnica</h4>
                <button class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Registrar Nuevo Evento</button>
            </div>

            <?php if ($res_historial->num_rows > 0): ?>
                <div class="list-group">
                    <?php while($row = $res_historial->fetch_assoc()): ?>
                        <div class="list-group-item list-group-item-action flex-column align-items-start mb-2 shadow-sm border-0 rounded">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1 text-primary">
                                    <i class="fas fa-tools"></i> <?php echo $row['tipo_evento']; ?>
                                </h5>
                                <small class="text-muted">
                                    <i class="far fa-calendar-alt"></i> 
                                    <?php echo date("d/m/Y H:i", strtotime($row['fecha_realizacion'])); ?>
                                </small>
                            </div>
                            <p class="mb-1 mt-2"><?php echo $row['detalle_tecnico']; ?></p>
                            <div class="mt-2">
                                <small class="text-muted fw-bold">Técnico: <?php echo $row['tecnico_responsable']; ?></small>
                                <?php if($row['costo'] > 0): ?>
                                    <span class="badge bg-danger float-end">S/ <?php echo $row['costo']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Esta máquina no tiene historial de mantenimientos registrado.
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>