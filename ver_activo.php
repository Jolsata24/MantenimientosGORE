<?php
// ver_activo.php
include 'conexion.php';
session_start();

if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: login.php");
    exit;
}

$id_bien = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Datos del Activo
$sql = "SELECT b.*, c.nombre as categoria, p.nombres, p.apellidos, p.oficina 
        FROM bienes b 
        INNER JOIN categorias c ON b.id_categoria = c.id_categoria
        LEFT JOIN personal p ON b.id_personal = p.id_personal
        WHERE b.id_bien = $id_bien";
$res = $conn->query($sql);

if ($res->num_rows == 0) { echo "Activo no encontrado."; exit; }
$fila = $res->fetch_assoc();

// 2. Historial y Métricas
$sql_hist = "SELECT * FROM mantenimientos WHERE id_bien = $id_bien ORDER BY fecha_mantenimiento DESC";
$res_hist = $conn->query($sql_hist);

$total_gastado = 0;
$conteo_fallas = 0;
$historial = [];
while($h = $res_hist->fetch_assoc()){
    $total_gastado += $h['costo'];
    $conteo_fallas++;
    $historial[] = $h;
}

// 3. NUEVO: Obtener lista de usuarios para el select de técnicos
$sql_users = "SELECT nombre_usuario FROM usuarios_sistema ORDER BY nombre_usuario ASC";
$res_users = $conn->query($sql_users);

// Colores de estado visual
$estado = trim($fila['estado_fisico']);
$badge_color = 'bg-warning text-dark';
if (strcasecmp($estado, 'Bueno') == 0) $badge_color = 'bg-success';
if (strcasecmp($estado, 'Malo') == 0 || strcasecmp($estado, 'Baja') == 0) $badge_color = 'bg-danger';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha Técnica | <?php echo $fila['codigo_patrimonial']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        body { background-color: #f4f6f9; }
        .card-detail { border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-radius: 12px; background: white; margin-bottom: 20px; }
        .card-header { background: white; border-bottom: none; padding-top: 1.5rem; padding-left: 1.5rem; }
        .label-dato { font-size: 0.75rem; color: #8898aa; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
        .valor-dato { font-size: 1rem; color: #32325d; font-weight: 600; }
        .icon-box { width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        
        .timeline { border-left: 2px solid #e9ecef; margin-left: 10px; padding-left: 20px; position: relative; }
        .timeline-item { position: relative; margin-bottom: 1.5rem; }
        .timeline-dot { width: 12px; height: 12px; background: #fff; border: 2px solid #00609C; border-radius: 50%; position: absolute; left: -27px; top: 5px; }
        .timeline-date { font-size: 0.8rem; color: #8898aa; font-weight: 600; }
        
        .metric-box { background: #f8f9fa; border-radius: 10px; padding: 15px; text-align: center; margin-bottom: 10px; }
        .metric-value { font-size: 1.2rem; font-weight: 800; color: #32325d; }
        .metric-label { font-size: 0.7rem; text-transform: uppercase; color: #8898aa; letter-spacing: 0.5px; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container py-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0 fw-bold text-dark">
                    <a href="javascript:history.back()" class="text-decoration-none text-muted me-2"><i class="fas fa-arrow-left"></i></a>
                    Ficha del Activo
                </h3>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMantenimiento">
                    <i class="fas fa-plus-circle me-2"></i> Nueva Intervención
                </button>
            </div>

            <div class="row">
                <div class="col-lg-4">
                    <div class="card card-detail text-center p-4">
                        <?php 
                            $icono = 'fa-box';
                            if($fila['id_categoria']==1) $icono='fa-laptop';
                            if($fila['id_categoria']==2) $icono='fa-print';
                            if($fila['id_categoria']==3) $icono='fa-desktop';
                        ?>
                        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center bg-primary text-white rounded-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                            <i class="fas <?php echo $icono; ?>"></i>
                        </div>
                        
                        <h4 class="fw-bold mb-1"><?php echo $fila['codigo_patrimonial']; ?></h4>
                        <p class="text-muted mb-3"><?php echo $fila['categoria']; ?></p>
                        
                        <span class="badge <?php echo $badge_color; ?> px-3 py-2 rounded-pill fs-6 mb-4">
                            <?php echo $fila['estado_fisico']; ?>
                        </span>

                        <hr class="my-3">

                        <div class="row g-2">
                            <div class="col-6">
                                <div class="metric-box">
                                    <div class="metric-value text-danger">S/. <?php echo number_format($total_gastado, 0); ?></div>
                                    <div class="metric-label">Inversión Total</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="metric-box">
                                    <div class="metric-value text-dark"><?php echo $conteo_fallas; ?></div>
                                    <div class="metric-label">Intervenciones</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <?php 
                            $qr_file = "img/qr/" . preg_replace('/[^A-Za-z0-9\-]/', '', $fila['codigo_patrimonial']) . ".png";
                            if(file_exists($qr_file)): ?>
                                <img src="<?php echo $qr_file; ?>" alt="QR" class="img-fluid" style="max-width: 120px;">
                            <?php else: ?>
                                <span class="text-muted small d-block my-3">Sin QR generado</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    
                    <div class="card card-detail">
                        <div class="card-header">
                            <h5 class="fw-bold text-primary mb-0"><i class="fas fa-info-circle me-2"></i>Información General</h5>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <div class="label-dato">Descripción / Hostname</div>
                                    <div class="valor-dato"><?php echo $fila['descripcion']; ?></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="label-dato">Marca</div>
                                    <div class="valor-dato"><?php echo $fila['marca']; ?></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="label-dato">Modelo</div>
                                    <div class="valor-dato"><?php echo $fila['modelo']; ?></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="label-dato">N° Serie</div>
                                    <div class="valor-dato font-monospace"><?php echo $fila['serie']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if($fila['id_categoria'] == 1): ?>
                    <div class="card card-detail">
                        <div class="card-header">
                            <h5 class="fw-bold text-success mb-0"><i class="fas fa-microchip me-2"></i>Hardware & Software</h5>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box bg-light text-success me-3"><i class="fas fa-microchip"></i></div>
                                        <div>
                                            <div class="label-dato">Procesador</div>
                                            <div class="valor-dato" style="font-size:0.9rem"><?php echo $fila['procesador'] ?: '-'; ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box bg-light text-success me-3"><i class="fas fa-memory"></i></div>
                                        <div>
                                            <div class="label-dato">Memoria RAM</div>
                                            <div class="valor-dato"><?php echo $fila['ram'] ?: '-'; ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box bg-light text-success me-3"><i class="fas fa-hdd"></i></div>
                                        <div>
                                            <div class="label-dato">Almacenamiento</div>
                                            <div class="valor-dato"><?php echo $fila['disco'] ?: '-'; ?></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12"><hr class="text-muted opacity-25"></div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box bg-light text-info me-3"><i class="fab fa-windows"></i></div>
                                        <div>
                                            <div class="label-dato">Sistema Operativo</div>
                                            <div class="valor-dato"><?php echo $fila['so'] ?: 'No registrado'; ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box bg-light text-dark me-3"><i class="fas fa-network-wired"></i></div>
                                        <div>
                                            <div class="label-dato">Dirección IP</div>
                                            <div class="valor-dato font-monospace"><?php echo $fila['ip'] ?: '-'; ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="card card-detail">
                        <div class="card-header">
                            <h5 class="fw-bold text-warning mb-0"><i class="fas fa-map-marker-alt me-2"></i>Ubicación y Custodio</h5>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="label-dato">Ubicación Física</div>
                                    <div class="valor-dato text-dark"><?php echo $fila['ubicacion'] ?: 'Sin ubicación'; ?></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="label-dato">Custodio Asignado</div>
                                    <?php if($fila['nombres']): ?>
                                        <div class="valor-dato text-dark">
                                            <?php echo $fila['nombres'] . ' ' . $fila['apellidos']; ?>
                                        </div>
                                        <small class="text-muted"><?php echo $fila['oficina']; ?></small>
                                    <?php else: ?>
                                        <div class="valor-dato text-muted">Sin asignar</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-detail">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold text-danger mb-0"><i class="fas fa-file-medical-alt me-2"></i>Historial de Reparaciones</h5>
                        </div>
                        <div class="card-body px-4 pb-4">
                            
                            <?php if(empty($historial)): ?>
                                <div class="text-center py-3 text-muted">
                                    <i class="fas fa-check-circle text-success mb-2 fs-4"></i>
                                    <p class="mb-0 small">Sin historial de fallas reportado.</p>
                                </div>
                            <?php else: ?>
                                <div class="timeline mt-2">
                                    <?php foreach($historial as $h): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-dot" style="border-color: <?php echo ($h['tipo_mantenimiento']=='Correctivo')?'#dc3545':'#198754'; ?>"></div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="timeline-date"><?php echo date('d/m/Y', strtotime($h['fecha_mantenimiento'])); ?></span>
                                            <span class="badge bg-light text-dark border"><?php echo $h['tipo_mantenimiento']; ?></span>
                                        </div>
                                        <div class="bg-light p-3 rounded">
                                            <p class="mb-1 text-dark small"><?php echo nl2br($h['descripcion']); ?></p>
                                            
                                            <div class="d-flex justify-content-between align-items-center mt-2 border-top pt-2">
                                                <small class="text-muted fw-bold"><i class="fas fa-user-tie me-1"></i> <?php echo $h['tecnico_responsable'] ?: 'Técnico Externo'; ?></small>
                                                <?php if($h['costo'] > 0): ?>
                                                    <span class="fw-bold text-danger small">S/. <?php echo number_format($h['costo'], 2); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalMantenimiento" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Registrar Intervención</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesos/guardar_mantenimiento.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_bien" value="<?php echo $id_bien; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">TIPO DE SERVICIO</label>
                            <select name="tipo" class="form-select" required>
                                <option value="Correctivo">Correctivo (Reparación)</option>
                                <option value="Preventivo">Preventivo (Limpieza/Software)</option>
                                <option value="Mejora">Mejora (Upgrade)</option>
                                <option value="Diagnostico">Solo Diagnóstico</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold small text-muted">FECHA</label>
                                <input type="date" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold small text-muted">COSTO (S/.)</label>
                                <input type="number" name="costo" class="form-control" step="0.01" value="0.00">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">DESCRIPCIÓN DEL TRABAJO</label>
                            <textarea name="descripcion" class="form-control" rows="3" placeholder="Detalle la falla y la solución aplicada..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">TÉCNICO RESPONSABLE</label>
                            <select name="tecnico" class="form-select" required>
                                <option value="" selected disabled>Seleccione un técnico...</option>
                                <?php 
                                if ($res_users && $res_users->num_rows > 0) {
                                    // Aseguramos que el puntero esté al inicio
                                    $res_users->data_seek(0);
                                    while($u = $res_users->fetch_assoc()) {
                                        echo '<option value="'.$u['nombre_usuario'].'">'.$u['nombre_usuario'].'</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Historial</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('status') === 'saved') {
            Swal.fire({ icon: 'success', title: 'Registrado', text: 'La intervención se ha guardado en la historia clínica.', confirmButtonColor: '#00609C' });
            window.history.replaceState(null, null, window.location.pathname + "?id=<?php echo $id_bien; ?>");
        }
    </script>
</body>
</html>