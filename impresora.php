<?php
// impresoras.php
include 'conexion.php';
session_start();

if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) { header("Location: login.php"); exit; }
$page = 'inventario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Impresoras | GORE Pasco</title>
    <link rel="icon" type="image/png" href="img/logo_gore.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/inventario.css">
    <style>
        .table td { vertical-align: middle; font-size: 0.85rem; }
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
                    <h2 class="titulo-seccion mb-0"><i class="fas fa-print text-primary me-2"></i> Impresoras</h2>
                </div>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                    <i class="fas fa-plus me-1"></i> Nuevo
                </button>
            </div>

            <div class="card card-tabla p-3 shadow-sm border-0">
                <div class="table-responsive">
                    <table id="tablaImpresoras" class="table table-hover w-100">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th><i class="fas fa-barcode"></i> Código / Serie</th>
                                <th><i class="fas fa-print"></i> Equipo</th>
                                <th><i class="fas fa-palette"></i> Tipo / Color</th>
                                <th><i class="fas fa-network-wired"></i> Conexión</th>
                                <th><i class="fas fa-map-marker-alt"></i> Ubicación</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // ID CATEGORIA 2 = IMPRESORAS
                            $sql = "SELECT b.*, p.nombres, p.apellidos 
                                    FROM bienes b 
                                    LEFT JOIN personal p ON b.id_personal = p.id_personal
                                    WHERE b.id_categoria = 2 
                                    ORDER BY b.id_bien DESC";
                            $res = $conn->query($sql);

                            while ($fila = $res->fetch_assoc()):
                                // Estado
                                $estado_bd = trim($fila['estado_fisico']);
                                $clase = 'estado-regular'; 
                                if(stripos($estado_bd, 'Bueno') !== false) $clase = 'estado-bueno';
                                elseif (stripos($estado_bd, 'Malo') !== false || stripos($estado_bd, 'Baja') !== false) $clase = 'estado-malo';
                                
                                // Lógica visual para Color
                                $color_impresion = !empty($fila['color']) ? $fila['color'] : 'No definido';
                                $badge_color = 'bg-secondary';
                                if(stripos($color_impresion, 'Color') !== false) $badge_color = 'bg-info text-dark';
                                if(stripos($color_impresion, 'Monocrom') !== false || stripos($color_impresion, 'Negro') !== false) $badge_color = 'bg-dark text-white';
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo $fila['codigo_patrimonial']; ?></div>
                                    <small class="text-muted font-monospace d-block">
                                        SN: <?php echo $fila['serie'] ? $fila['serie'] : 'S/N'; ?>
                                    </small>
                                </td>

                                <td>
                                    <div class="fw-bold text-primary text-truncate" style="max-width: 200px;" title="<?php echo $fila['descripcion']; ?>">
                                        <?php echo str_replace('Printer GLPI ', '', $fila['descripcion']); ?>
                                    </div>
                                    <span class="d-block small text-dark mt-1">
                                        <?php echo $fila['marca']; ?> - <?php echo $fila['modelo']; ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="badge <?php echo $badge_color; ?> border fw-normal">
                                        <?php echo $color_impresion; ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if(!empty($fila['ip']) && $fila['ip'] != '-'): ?>
                                        <span class="badge bg-light text-primary border font-monospace">
                                            <i class="fas fa-network-wired me-1"></i><?php echo $fila['ip']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">USB / Sin Red</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="text-truncate-2 small text-secondary" title="<?php echo $fila['ubicacion']; ?>">
                                        <?php echo !empty($fila['ubicacion']) ? $fila['ubicacion'] : '<span class="text-muted fst-italic">Sin asignar</span>'; ?>
                                    </div>
                                </td>

                                <td>
                                    <span class="badge-estado <?php echo $clase; ?>">
                                        <?php echo ucfirst(strtolower($estado_bd)); ?>
                                    </span>
                                </td>

                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm border" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow">
                                            <li><a class="dropdown-item" href="ver_activo.php?id=<?php echo $fila['id_bien']; ?>"><i class="fas fa-eye text-primary me-2"></i>Ver Detalles</a></li>
                                            <li><a class="dropdown-item" href="#" onclick='cargarDatosEditar(<?php echo json_encode($fila); ?>)' data-bs-toggle="modal" data-bs-target="#modalEditar"><i class="fas fa-pen text-warning me-2"></i>Editar</a></li>
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
    
    <?php include 'modales_inventario.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script> 
        $(document).ready(function() { 
            $('#tablaImpresoras').DataTable({ 
                language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' }, 
                pageLength: 10,
                responsive: true
            }); 

            // Alerta al guardar
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('status') === 'updated') {
                Swal.fire({ icon: 'success', title: 'Guardado', text: 'Datos de impresora actualizados.', timer: 2000, showConfirmButton: false });
                window.history.replaceState(null, null, window.location.pathname);
            }
        }); 
    </script>
</body>
</html>