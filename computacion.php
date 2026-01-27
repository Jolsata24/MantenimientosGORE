<?php
// computacion.php
include 'conexion.php';
session_start();

if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: login.php");
    exit;
}
$page = 'inventario';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Computadoras | GORE Pasco</title>
    <link rel="icon" type="image/png" href="img/logo_gore.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/inventario.css">

    <style>
        .table td {
            vertical-align: middle;
            font-size: 0.85rem;
        }

        .badge-spec {
            font-size: 0.75rem;
            font-weight: 500;
        }

        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            max-width: 200px;
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">

            <div class="page-header d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center">
                    <a href="inventario.php" class="text-muted me-3"><i class="fas fa-arrow-left"></i></a>
                    <h2 class="titulo-seccion mb-0"><i class="fas fa-laptop text-primary me-2"></i> Computadoras</h2>
                </div>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                    <i class="fas fa-plus me-1"></i> Nuevo
                </button>
            </div>

            <div class="card card-tabla p-3 shadow-sm border-0">
                <div class="table-responsive">
                    <table id="tablaComputo" class="table table-hover w-100">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th><i class="fas fa-barcode"></i> Código / Serie</th>
                                <th><i class="fas fa-network-wired"></i> Hostname</th>
                                <th><i class="fas fa-laptop"></i> Equipo / Nombre</th>
                                <th><i class="fas fa-microchip"></i> Hardware</th>
                                <th><i class="fas fa-map-marker-alt"></i> Ubicación</th>
                                <th><i class="fas fa-user"></i> Custodio</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT b.*, p.nombres, p.apellidos 
                                    FROM bienes b 
                                    LEFT JOIN personal p ON b.id_personal = p.id_personal
                                    WHERE b.id_categoria = 1 
                                    ORDER BY b.id_bien DESC";
                            $res = $conn->query($sql);

                            while ($fila = $res->fetch_assoc()):
                                $estado_bd = trim($fila['estado_fisico']);
                                $clase = 'estado-regular';
                                if (stripos($estado_bd, 'Bueno') !== false) $clase = 'estado-bueno';
                                elseif (stripos($estado_bd, 'Malo') !== false || stripos($estado_bd, 'Baja') !== false) $clase = 'estado-malo';
                            ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo $fila['codigo_patrimonial']; ?></div>
                                        <small class="text-muted font-monospace d-block">
                                            SN: <?php echo $fila['serie'] ? $fila['serie'] : 'S/N'; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-primary text-truncate" style="max-width: 150px;" title="<?php echo $fila['descripcion']; ?>">
                                            <?php echo str_replace('Computer GLPI ', '', $fila['descripcion']); ?>
                                        </div>
                                        <span class="d-block small text-dark mt-1">
                                            <?php echo $fila['marca']; ?> - <?php echo $fila['modelo']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($fila['tipo_equipo']) && $fila['tipo_equipo'] != '-'): ?>
                                            <span class="badge bg-info text-dark border"><i class="fas fa-desktop me-1"></i> <?php echo $fila['tipo_equipo']; ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <span class="badge bg-light text-dark border text-start fw-normal" title="Procesador">
                                                <i class="fas fa-microchip text-secondary me-1"></i>
                                                <?php echo !empty($fila['procesador']) ? substr($fila['procesador'], 0, 20) . '...' : '-'; ?>
                                            </span>
                                            <div class="d-flex gap-1">
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success px-2">RAM: <?php echo !empty($fila['ram']) ? $fila['ram'] : '-'; ?></span>
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2">HD: <?php echo !empty($fila['disco']) ? $fila['disco'] : '-'; ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-truncate-2 small text-secondary" title="<?php echo $fila['ubicacion']; ?>">
                                            <?php echo !empty($fila['ubicacion']) ? $fila['ubicacion'] : '<span class="text-muted fst-italic">Sin asignar</span>'; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($fila['nombres']): ?>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2 shadow-sm" style="width:28px; height:28px; font-size:0.7rem;">
                                                    <?php echo strtoupper(substr($fila['nombres'], 0, 1) . substr($fila['apellidos'], 0, 1)); ?>
                                                </div>
                                                <div class="lh-1">
                                                    <span class="d-block small fw-bold"><?php echo explode(' ', $fila['nombres'])[0]; ?></span>
                                                    <span class="d-block small text-muted" style="font-size:0.7rem"><?php echo explode(' ', $fila['apellidos'])[0]; ?></span>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-25 text-secondary border">Libre</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge-estado <?php echo $clase; ?>">
                                            <?php echo ucfirst(strtolower($estado_bd)); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm border" type="button" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                                <li>
                                                    <a class="dropdown-item" href="ver_activo.php?id=<?php echo $fila['id_bien']; ?>">
                                                        <i class="fas fa-eye text-primary me-2"></i>Ver Detalles
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick='cargarDatosEditar(<?php echo json_encode($fila); ?>)' data-bs-toggle="modal" data-bs-target="#modalEditar">
                                                        <i class="fas fa-pen text-warning me-2"></i>Editar
                                                    </a>
                                                </li>

                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>

                                                <li>
                                                    <a class="dropdown-item" href="phpqrcode.php?id=<?php echo $fila['id_bien']; ?>" target="_blank">
                                                        <i class="fas fa-qrcode text-dark me-2"></i>Imprimir QR
                                                    </a>
                                                </li>

                                                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador'): ?>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" onclick="confirmarEliminar(<?php echo $fila['id_bien']; ?>)">
                                                            <i class="fas fa-trash-alt me-2"></i>Eliminar
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php include 'modales_inventario.php'; ?>

    <script>
        // --- FUNCIÓN GLOBAL DE ELIMINAR (Debe estar fuera del ready) ---
        function confirmarEliminar(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esto! El activo se borrará permanentemente.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminarlo',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'procesos/eliminar_bien.php?id=' + id;
                }
            })
        }

        $(document).ready(function() { 
            // Inicializar DataTable
            $('#tablaComputo').DataTable({ 
                language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' }, 
                pageLength: 10, 
                ordering: false, 
                responsive: true 
            }); 
            
            // ALERTAS
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');

            if (status === 'updated') {
                Swal.fire({ icon: 'success', title: 'Guardado', text: 'Datos actualizados.', timer: 2000, showConfirmButton: false });
            } else if (status === 'deleted') {
                Swal.fire({ icon: 'success', title: 'Eliminado', text: 'La computadora ha sido eliminada.', confirmButtonColor: '#d33' });
            }

            if (status) window.history.replaceState(null, null, window.location.pathname);
        }); 
    </script>
</body>
</html>
</body>

</html>