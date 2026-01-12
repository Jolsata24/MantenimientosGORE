<?php
// ver_activo.php
include 'conexion.php';
session_start();

// 1. LÓGICA HÍBRIDA:
// Verificamos si el usuario ya inició sesión, pero NO lo obligamos.
$es_admin = (isset($_SESSION['logeado']) && $_SESSION['logeado'] === true);

// 2. Obtener ID del bien
$id_bien = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_bien == 0) { die("Error: Código de activo no válido."); }

// 3. Consultas a la Base de Datos
$sql_info = "SELECT * FROM bienes WHERE id_bien = $id_bien";
$res_info = $conn->query($sql_info);
$bien = $res_info->fetch_assoc();

if (!$bien) { die("<div class='alert alert-danger m-4'>El activo solicitado no existe en el sistema.</div>"); }

// Traer historial
$sql_historial = "SELECT * FROM mantenimientos WHERE id_bien = $id_bien ORDER BY fecha_realizacion DESC";
$res_historial = $conn->query($sql_historial);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha: <?php echo $bien['codigo_patrimonial']; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/ver_activo.css">
</head>
<body>

    <div class="header-ficha">
        <div class="container">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h1 class="titulo-ficha"><i class="fas fa-qrcode me-2 opacity-50"></i>Ficha Digital</h1>
                    <small class="opacity-75">Gobierno Regional de Pasco</small>
                </div>
                
                <?php if($es_admin): ?>
                    <a href="inventario.php" class="btn btn-outline-light btn-sm d-none d-md-inline-block">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light btn-sm opacity-50">
                        <i class="fas fa-lock"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            
            <div class="col-md-5 col-lg-4">
                <div class="card card-flotante text-center p-4">
                    
                    <div class="icono-activo-wrapper">
                        <?php 
                            $icono = 'fa-box';
                            $desc = strtolower($bien['descripcion']);
                            if(strpos($desc, 'laptop')!==false || strpos($desc, 'pc')!==false) $icono = 'fa-laptop';
                            if(strpos($desc, 'silla')!==false || strpos($desc, 'sillon')!==false) $icono = 'fa-chair';
                            if(strpos($desc, 'camioneta')!==false || strpos($desc, 'vehiculo')!==false) $icono = 'fa-car';
                            if(strpos($desc, 'impresora')!==false) $icono = 'fa-print';
                        ?>
                        <i class="fas <?php echo $icono; ?> icono-activo"></i>
                    </div>

                    <div class="mb-3">
                        <?php 
                            $bg = 'bg-success';
                            if($bien['estado_fisico']=='Regular') $bg = 'bg-warning text-dark';
                            if($bien['estado_fisico']=='Malo') $bg = 'bg-danger';
                        ?>
                        <span class="badge rounded-pill <?php echo $bg; ?> px-3 py-2">
                            <?php echo $bien['estado_fisico']; ?>
                        </span>
                    </div>

                    <h4 class="fw-bold text-dark mb-1"><?php echo $bien['codigo_patrimonial']; ?></h4>
                    <p class="text-muted small mb-4"><?php echo $bien['descripcion']; ?></p>

                    <div class="text-start">
                        <div class="dato-item">
                            <span class="dato-label">Marca / Modelo</span>
                            <span class="dato-valor">
                                <?php echo ($bien['marca'] ? $bien['marca'] : '-') . ' / ' . ($bien['modelo'] ? $bien['modelo'] : '-'); ?>
                            </span>
                        </div>
                        <div class="dato-item">
                            <span class="dato-label">Serie</span>
                            <span class="dato-valor font-monospace"><?php echo $bien['serie'] ? $bien['serie'] : 'S/N'; ?></span>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-md-7 col-lg-8">
                <div class="card card-flotante p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark m-0"><i class="fas fa-history text-primary me-2"></i>Historial</h5>
                        
                        <?php if($es_admin): ?>
                            <button class="btn btn-sm btn-outline-primary rounded-pill">
                                <i class="fas fa-plus"></i> <span class="d-none d-sm-inline">Reportar Incidente</span>
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if ($res_historial->num_rows > 0): ?>
                        <div class="timeline">
                            <?php while($row = $res_historial->fetch_assoc()): ?>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-date">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        <?php echo date("d/m/Y h:i A", strtotime($row['fecha_realizacion'])); ?>
                                    </div>
                                    <div class="timeline-title"><?php echo $row['tipo_evento']; ?></div>
                                    <p class="text-muted mb-1 small"><?php echo $row['detalle_tecnico']; ?></p>
                                    
                                    <div class="d-flex align-items-center mt-2">
                                        <div class="badge bg-light text-dark border me-2">
                                            <i class="fas fa-user-tag me-1 text-secondary"></i> <?php echo $row['tecnico_responsable']; ?>
                                        </div>
                                        <?php if($es_admin && $row['costo'] > 0): ?>
                                            <div class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                                S/ <?php echo number_format($row['costo'], 2); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-clipboard-check fa-3x mb-3 text-success opacity-50"></i>
                            <p>Sin historial de mantenimiento registrado.</p>
                        </div>
                    <?php endif; ?>

                    <?php if($es_admin): ?>
                        <div class="mt-4 d-block d-md-none">
                            <a href="index.php" class="btn-action-big d-block text-center text-decoration-none">
                                Volver al Dashboard
                            </a>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>