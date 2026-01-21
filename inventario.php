<?php
// inventario.php
include 'conexion.php';
session_start();

if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: login.php");
    exit;
}

$page = 'inventario'; // Para activar el sidebar

// 1. CAPTURAR CATEGORÍA DE LA URL
$cat_id = isset($_GET['categoria']) ? intval($_GET['categoria']) : null;

// 2. CONFIGURACIÓN VISUAL DE CATEGORÍAS
$config_cats = [
    1 => ['titulo' => 'Computadoras', 'icono' => 'fa-laptop', 'color' => '#00609C'],
    2 => ['titulo' => 'Impresoras',   'icono' => 'fa-print',  'color' => '#2E7D32'],
    3 => ['titulo' => 'Monitores',    'icono' => 'fa-desktop','color' => '#F57F17']
];

$cat_actual = $cat_id ? ($config_cats[$cat_id] ?? ['titulo' => 'General', 'icono' => 'fa-box', 'color' => '#555']) : null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario | GORE Pasco</title>
    <link rel="icon" type="image/png" href="img/logo_gore.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/inventario.css">
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="overlay" id="overlay"></div>

    <div class="main-content">
        <div class="container-fluid">

            <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center">
                    <button class="btn btn-primary d-md-none me-3" id="btnMenu">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div>
                        <h2 class="titulo-seccion mb-0">
                            <?php if ($cat_id): ?>
                                <a href="inventario.php" class="text-decoration-none text-muted me-2" title="Volver al inicio"><i class="fas fa-arrow-left small"></i></a>
                                <i class="fas <?php echo $cat_actual['icono']; ?> text-secondary me-2 opacity-50"></i>
                                <?php echo $cat_actual['titulo']; ?>
                            <?php else: ?>
                                <i class="fas fa-boxes text-secondary me-3 opacity-50"></i> Inventario General
                            <?php endif; ?>
                        </h2>
                        <small class="text-muted">Gestión de bienes patrimoniales y asignaciones</small>
                    </div>
                </div>

                <button class="btn-nuevo" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                    <i class="fas fa-plus me-2"></i> Nuevo Registro
                </button>
            </div>

            <?php if (!$cat_id): ?>
                <div class="row g-4 mt-2">
                    <?php foreach ($config_cats as $id => $data): 
                        // Conteo rápido
                        $total = $conn->query("SELECT COUNT(*) as t FROM bienes WHERE id_categoria = $id")->fetch_assoc()['t'];
                    ?>
                    <div class="col-md-4">
                        <a href="inventario.php?categoria=<?php echo $id; ?>" class="text-decoration-none">
                            <div class="card card-gore h-100 border-0" style="border-left: 5px solid <?php echo $data['color']; ?> !important;">
                                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted text-uppercase small fw-bold mb-1">Categoría</h6>
                                        <h3 class="fw-bold text-dark mb-0"><?php echo $data['titulo']; ?></h3>
                                        <div class="mt-3">
                                            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                                <i class="fas fa-layer-group me-1"></i> <?php echo $total; ?> Equipos
                                            </span>
                                        </div>
                                    </div>
                                    <i class="fas <?php echo $data['icono']; ?> fa-4x opacity-25" style="color: <?php echo $data['color']; ?>;"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($cat_id): ?>
                <div class="card card-tabla p-3 mt-4">
                    <div class="table-responsive">
                        <table id="tablaInventario" class="table align-middle w-100 table-hover">
                            <thead>
                                <tr>
                                    <th>Activo</th>
                                    <th>Detalles</th>
                                    <th>Custodio</th>
                                    <th>Ubicación</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT b.*, c.nombre as nombre_categoria, p.nombres, p.apellidos, p.oficina 
                                        FROM bienes b 
                                        INNER JOIN categorias c ON b.id_categoria = c.id_categoria
                                        LEFT JOIN personal p ON b.id_personal = p.id_personal
                                        WHERE b.id_categoria = $cat_id
                                        ORDER BY b.id_bien DESC";
                                $resultado = $conn->query($sql);

                                while ($fila = $resultado->fetch_assoc()):
                                    // Colores de estado
                                    $clase_estado = 'estado-regular';
                                    if($fila['estado_fisico'] == 'Bueno') $clase_estado = 'estado-bueno';
                                    if($fila['estado_fisico'] == 'Malo' || $fila['estado_fisico'] == 'Baja') $clase_estado = 'estado-malo';
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-primary"><?php echo $fila['codigo_patrimonial']; ?></div>
                                        <small class="text-muted font-monospace"><i class="fas fa-barcode me-1"></i><?php echo $fila['serie'] ? $fila['serie'] : 'S/N'; ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 200px;"><?php echo $fila['descripcion']; ?></div>
                                        <small class="text-muted"><?php echo $fila['marca']; ?> <?php echo $fila['modelo']; ?></small>
                                    </td>
                                    <td>
                                        <?php if ($fila['nombres']): ?>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center border me-2" style="width:30px; height:30px; font-weight:bold; font-size:0.75rem;">
                                                    <?php echo strtoupper(substr($fila['nombres'], 0, 1) . substr($fila['apellidos'], 0, 1)); ?>
                                                </div>
                                                <div class="lh-1">
                                                    <span class="d-block small fw-bold text-dark"><?php echo $fila['apellidos']; ?></span>
                                                    <small class="text-muted" style="font-size: 0.7rem;">Oficina</small>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-light text-secondary border fw-normal">Sin Asignar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-dark"><i class="fas fa-map-marker-alt text-danger me-1"></i><?php echo $fila['ubicacion'] ? $fila['ubicacion'] : '-'; ?></small>
                                    </td>
                                    <td>
                                        <span class="badge-estado <?php echo $clase_estado; ?>">
                                            <?php echo $fila['estado_fisico']; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="ver_activo.php?id=<?php echo $fila['id_bien']; ?>" class="btn-action btn-ver" title="Ver Ficha">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn-action btn-editar" title="Editar" 
                                                onclick="cargarDatosEditar(<?php echo htmlspecialchars(json_encode($fila)); ?>)" 
                                                data-bs-toggle="modal" data-bs-target="#modalEditar">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <a href="phpqrcode.php?id=<?php echo $fila['id_bien']; ?>" class="btn-action btn-qr" target="_blank" title="Código QR">
                                                <i class="fas fa-qrcode"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <?php include 'modales_inventario.php'; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Inicializar DataTables con configuración en español
        $(document).ready(function() {
            $('#tablaInventario').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                lengthChange: false, // Ocultar "mostrar X registros" para más limpieza
                pageLength: 10,
                ordering: false // Desactivar orden por defecto para mantener el orden SQL (ID DESC)
            });
        });

        // Lógica del Menú Móvil (Copiada de index.php para consistencia)
        const btnMenu = document.getElementById('btnMenu');
        const sidebar = document.querySelector('.sidebar-container');
        const overlay = document.getElementById('overlay');

        if(btnMenu){
            btnMenu.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            });

            overlay.addEventListener('click', () => {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });
        }

        // Alertas de SweetAlert
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        if (status === 'success') {
            Swal.fire({ icon: 'success', title: 'Registrado', text: 'El activo se guardó correctamente.', confirmButtonColor: '#00609C' });
        } else if (status === 'updated') {
            Swal.fire({ icon: 'info', title: 'Actualizado', text: 'Los datos fueron modificados.', confirmButtonColor: '#FDB913', confirmButtonText: 'Genial' });
        }
        if (status) window.history.replaceState(null, null, window.location.pathname);
    </script>

</body>
</html>