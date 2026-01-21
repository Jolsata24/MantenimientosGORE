<?php
// ver_activo.php
include 'conexion.php';
session_start();

// 1. LÓGICA HÍBRIDA:
// Verificamos si el usuario ya inició sesión (Admin) o es público (QR)
$es_admin = (isset($_SESSION['logeado']) && $_SESSION['logeado'] === true);

// 2. Obtener ID del bien
$id_bien = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_bien == 0) {
    die("Error: Código de activo no válido.");
}

// UNIMOS (JOIN) la tabla bienes con la tabla personal
$sql_info = "SELECT b.*, c.nombre as nombre_categoria, p.nombres, p.apellidos 
             FROM bienes b 
             LEFT JOIN categorias c ON b.id_categoria = c.id_categoria
             LEFT JOIN personal p ON b.id_personal = p.id_personal
             WHERE b.id_bien = $id_bien";

$res_info = $conn->query($sql_info);
$bien = $res_info->fetch_assoc();

if (!$bien) {
    die("<div class='alert alert-danger m-4'>El activo solicitado no existe en el sistema.</div>");
}

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
    <link rel="icon" type="image/png" href="img/logo_gore.png">
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

                <?php if ($es_admin): ?>
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
                        if (strpos($desc, 'laptop') !== false || strpos($desc, 'pc') !== false)
                            $icono = 'fa-laptop';
                        if (strpos($desc, 'silla') !== false || strpos($desc, 'sillon') !== false)
                            $icono = 'fa-chair';
                        if (strpos($desc, 'camioneta') !== false || strpos($desc, 'vehiculo') !== false)
                            $icono = 'fa-car';
                        if (strpos($desc, 'impresora') !== false)
                            $icono = 'fa-print';
                        if (strpos($desc, 'monitor') !== false)
                            $icono = 'fa-desktop';
                        ?>
                        <i class="fas <?php echo $icono; ?> icono-activo"></i>
                    </div>

                    <div class="mb-3">
                        <?php
                        $bg = 'bg-success';
                        if ($bien['estado_fisico'] == 'Regular')
                            $bg = 'bg-warning text-dark';
                        if ($bien['estado_fisico'] == 'Malo' || $bien['estado_fisico'] == 'Baja')
                            $bg = 'bg-danger';
                        ?>
                        <span class="badge rounded-pill <?php echo $bg; ?> px-3 py-2">
                            <?php echo $bien['estado_fisico']; ?>
                        </span>
                    </div>

                    <h4 class="fw-bold text-dark mb-1"><?php echo $bien['codigo_patrimonial']; ?></h4>
                    <p class="text-muted small mb-4"><?php echo $bien['descripcion']; ?></p>

                    <div class="text-start">
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="p-2 border rounded bg-light text-center">
                                    <i class="fas fa-microchip text-secondary d-block mb-1"></i>
                                    <small class="fw-bold d-block" style="font-size:0.7rem">CPU</small>
                                    <span class="d-block text-truncate" style="font-size:0.8rem">
                                        <?php echo !empty($bien['procesador']) ? $bien['procesador'] : '-'; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border rounded bg-light text-center">
                                    <i class="fas fa-memory text-secondary d-block mb-1"></i>
                                    <small class="fw-bold d-block" style="font-size:0.7rem">RAM</small>
                                    <span class="d-block" style="font-size:0.8rem">
                                        <?php echo !empty($bien['ram']) ? $bien['ram'] : '-'; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border rounded bg-light text-center">
                                    <i class="fas fa-hdd text-secondary d-block mb-1"></i>
                                    <small class="fw-bold d-block" style="font-size:0.7rem">DISCO</small>
                                    <span class="d-block" style="font-size:0.8rem">
                                        <?php echo !empty($bien['disco']) ? $bien['disco'] : '-'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-body p-2">
                                <div class="row g-0 align-items-center">
                                    <div class="col-1 text-center text-primary">
                                        <i class="fab fa-windows fa-lg"></i>
                                    </div>
                                    <div class="col-11 ps-2">
                                        <small class="text-muted d-block" style="font-size:0.7rem">SISTEMA
                                            OPERATIVO</small>
                                        <span class="fw-bold text-dark" style="font-size:0.85rem">
                                            <?php echo !empty($bien['sistema_operativo']) ? $bien['sistema_operativo'] : '-'; ?>
                                        </span>
                                    </div>
                                </div>
                                <hr class="my-2 opacity-25">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted d-block" style="font-size:0.7rem">DIRECCIÓN IP</small>
                                        <span class="font-monospace text-dark" style="font-size:0.85rem">
                                            <?php echo !empty($bien['ip']) ? $bien['ip'] : '-'; ?>
                                        </span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block" style="font-size:0.7rem">DIRECCIÓN MAC</small>
                                        <span class="font-monospace text-dark" style="font-size:0.85rem">
                                            <?php echo !empty($bien['mac']) ? $bien['mac'] : '-'; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-2 text-end">
                                    <small class="text-muted fst-italic" style="font-size:0.7rem">
                                        Última conexión:
                                        <?php echo !empty($bien['ultimo_inventario']) ? date("d/m/Y H:i", strtotime($bien['ultimo_inventario'])) : '-'; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="dato-item border-start border-4 border-primary">
                            <span class="dato-label"><i class="fas fa-map-marker-alt me-1"></i> Ubicación Física</span>
                            <span class="dato-valor text-primary">
                                <?php echo !empty($bien['ubicacion']) ? $bien['ubicacion'] : 'SIN ASIGNAR'; ?>
                            </span>
                        </div>

                        <div class="dato-item">
                            <span class="dato-label">Custodio Asignado</span>
                            <span class="dato-valor">
                                <?php
                                // Si el campo 'nombres' tiene datos, mostramos Apellidos, Nombres
                                if ($bien['nombres']) {
                                    echo $bien['apellidos'] . ', ' . $bien['nombres'];
                                } else {
                                    echo '<span class="text-muted fst-italic">-- Sin Custodio --</span>';
                                }
                                ?>
                            </span>
                        </div>

                        <div class="row g-1">
                            <div class="col-6">
                                <div class="dato-item h-100">
                                    <span class="dato-label">Marca</span>
                                    <span
                                        class="dato-valor"><?php echo !empty($bien['marca']) ? $bien['marca'] : '-'; ?></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="dato-item h-100">
                                    <span class="dato-label">Modelo</span>
                                    <span
                                        class="dato-valor"><?php echo !empty($bien['modelo']) ? $bien['modelo'] : '-'; ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="dato-item mt-1">
                            <span class="dato-label">Número de Serie</span>
                            <span class="dato-valor font-monospace text-dark">
                                <?php echo !empty($bien['serie']) ? $bien['serie'] : 'S/N'; ?>
                            </span>
                        </div>

                        <div class="dato-item">
                            <span class="dato-label">Categoría</span>
                            <span class="dato-valor">
                                <?php echo !empty($bien['nombre_categoria']) ? $bien['nombre_categoria'] : 'General'; ?>
                            </span>
                        </div>

                        <div class="dato-item">
                            <span class="dato-label">Fecha Registro</span>
                            <span class="dato-valor small">
                                <?php echo date("d/m/Y H:i", strtotime($bien['fecha_registro'])); ?>
                            </span>
                        </div>

                    </div>
                    <div class="mt-3 pt-3 border-top">
                        <h6 class="text-muted fw-bold small mb-2">CICLO DE VIDA & GARANTÍA</h6>

                        <div class="d-flex justify-content-between mb-1">
                            <span class="small text-muted">Compra:</span>
                            <span class="small fw-bold text-dark">
                                <?php echo !empty($bien['fecha_compra']) ? date("d/m/Y", strtotime($bien['fecha_compra'])) : 'Desconocida'; ?>
                            </span>
                        </div>

                        <?php if (!empty($bien['fecha_compra'])):
                            $antiguedad = date_diff(date_create($bien['fecha_compra']), date_create('today'))->y;
                            ?>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small text-muted">Antigüedad:</span>
                                <span class="badge bg-secondary"><?php echo $antiguedad; ?> Años</span>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between mb-1">
                            <span class="small text-muted">Proveedor:</span>
                            <span class="small text-dark text-end"
                                style="max-width: 150px;"><?php echo !empty($bien['proveedor']) ? $bien['proveedor'] : '-'; ?></span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="small text-muted">Garantía:</span>
                            <span
                                class="small fw-bold text-success"><?php echo !empty($bien['garantia']) ? $bien['garantia'] : '-'; ?></span>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-7 col-lg-8">
                <div class="card card-flotante p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark m-0"><i class="fas fa-history text-primary me-2"></i>Historial</h5>

                        <?php if ($es_admin): ?>
                            <button class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal"
                                data-bs-target="#modalReporte">
                                <i class="fas fa-plus"></i> <span class="d-none d-sm-inline">Reportar Incidente</span>
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if ($res_historial->num_rows > 0): ?>
                        <div class="timeline">
                            <?php while ($row = $res_historial->fetch_assoc()): ?>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-date">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        <?php echo date("d/m/Y", strtotime($row['fecha_realizacion'])); ?>
                                    </div>
                                    <div class="timeline-title"><?php echo $row['tipo_evento']; ?></div>
                                    <p class="text-muted mb-1 small"><?php echo $row['detalle_tecnico']; ?></p>

                                    <div class="d-flex align-items-center mt-2">
                                        <div class="badge bg-light text-dark border me-2">
                                            <i class="fas fa-user-tag me-1 text-secondary"></i>
                                            <?php echo $row['tecnico_responsable']; ?>
                                        </div>
                                        <?php if ($es_admin && $row['costo'] > 0): ?>
                                            <div
                                                class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
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

                    <?php if ($es_admin): ?>
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

    <div class="modal fade" id="modalReporte" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-tools me-2"></i>Reportar Incidente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesos/guardar_mantenimiento.php" method="POST">
                    <input type="hidden" name="id_bien" value="<?php echo $id_bien; ?>">

                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">TIPO DE EVENTO</label>
                            <select name="tipo_evento" class="form-select" required>
                                <option value="Mantenimiento Preventivo">Mantenimiento Preventivo</option>
                                <option value="Mantenimiento Correctivo">Mantenimiento Correctivo</option>
                                <option value="Falla Reportada">Falla Reportada</option>
                                <option value="Instalación de Software">Instalación de Software</option>
                                <option value="Cambio de Componente">Cambio de Componente</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">FECHA DEL SUCESO</label>
                            <input type="date" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">DETALLE TÉCNICO</label>
                            <textarea name="detalle" class="form-control" rows="3"
                                placeholder="Describa el trabajo realizado o la falla..." required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">TÉCNICO RESPONSABLE</label>
                                <input type="text" name="tecnico" class="form-control" placeholder="Nombre del técnico"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">COSTO (S/)</label>
                                <input type="number" step="0.01" name="costo" class="form-control" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Guardar Reporte</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        if (status === 'success') {
            Swal.fire({
                icon: 'success',
                title: '¡Operación Exitosa!',
                text: 'Los datos se guardaron correctamente.',
                confirmButtonColor: '#00609C'
            });
        } else if (status === 'updated') {
            Swal.fire({
                icon: 'info',
                title: 'Actualizado',
                text: 'La información ha sido modificada con éxito.',
                confirmButtonColor: '#FDB913',
                confirmButtonText: 'Genial'
            });
        }
        if (status) window.history.replaceState(null, null, window.location.pathname);
    </script>
</body>

</html>