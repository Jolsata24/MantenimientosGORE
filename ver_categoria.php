<?php
// ver_categoria.php (VERSIÓN INTELIGENTE)
include 'conexion.php';
session_start();

if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) { header("Location: login.php"); exit; }

$id_cat = isset($_GET['id']) ? intval($_GET['id']) : 0;
// Evitar colisión de variables usando un nombre único
$categoria_actual = $conn->query("SELECT * FROM categorias WHERE id_categoria = $id_cat")->fetch_assoc();

if(!$categoria_actual) { header("Location: inventario.php"); exit; }

$page = 'inventario';
// Variable para saber si debemos mostrar columnas extra (Solo para categoría 1 - Computadoras)
$es_computadora = ($id_cat == 1); 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $categoria_actual['nombre']; ?> | GORE Pasco</title>
    <link rel="icon" type="image/png" href="img/logo_gore.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/inventario.css">
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">

            <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center">
                    <a href="inventario.php" class="text-muted me-3"><i class="fas fa-arrow-left"></i></a>
                    <h2 class="titulo-seccion mb-0">
                        <i class="fas <?php echo $categoria_actual['icono']; ?> me-2" style="color: <?php echo $categoria_actual['color']; ?>;"></i> 
                        <?php echo $categoria_actual['nombre']; ?>
                    </h2>
                </div>
                <button class="btn-nuevo" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                    <i class="fas fa-plus me-2"></i> Nuevo Activo
                </button>
            </div>
            
            <div class="card card-tabla p-3 mt-3">
                <div class="table-responsive">
                    <table id="tablaGenerica" class="table align-middle w-100 table-hover table-sm">
                        <thead class="bg-light">
                            <tr>
                                <th>Inventario</th>
                                <th>Descripción</th>
                                <th>Marca/Modelo</th>
                                
                                <?php if($es_computadora): ?>
                                    <th>Specs</th>
                                <?php endif; ?>

                                <th>Custodio</th>
                                <th>Ubicación</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT b.*, p.nombres, p.apellidos 
                                    FROM bienes b 
                                    LEFT JOIN personal p ON b.id_personal = p.id_personal
                                    WHERE b.id_categoria = $id_cat ORDER BY b.id_bien DESC";
                            $res = $conn->query($sql);
                            
                            while ($fila = $res->fetch_assoc()):
                                $estado = trim($fila['estado_fisico']);
                                $clase = 'estado-regular';
                                if(stripos($estado, 'Bueno') !== false) $clase = 'estado-bueno';
                                if(stripos($estado, 'Malo') !== false || stripos($estado, 'Baja') !== false) $clase = 'estado-malo';
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark small"><?php echo $fila['codigo_patrimonial']; ?></div>
                                    <small class="text-muted font-monospace" style="font-size:0.75rem"><?php echo $fila['serie'] ?: 'S/N'; ?></small>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark text-truncate" style="max-width:200px;" title="<?php echo $fila['descripcion']; ?>">
                                        <?php echo $fila['descripcion']; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="d-block text-dark fw-bold small"><?php echo $fila['marca']; ?></span>
                                    <span class="d-block text-muted small"><?php echo $fila['modelo']; ?></span>
                                </td>

                                <?php if($es_computadora): ?>
                                <td>
                                    <div style="font-size: 0.75rem;">
                                        <span class="d-block text-truncate" style="max-width: 120px;" title="<?php echo $fila['procesador']; ?>">
                                            <i class="fas fa-microchip text-secondary me-1"></i><?php echo $fila['procesador']; ?>
                                        </span>
                                        <span class="d-block">
                                            <i class="fas fa-memory text-secondary me-1"></i><?php echo $fila['ram']; ?> | 
                                            <i class="fas fa-hdd text-secondary me-1"></i><?php echo $fila['disco']; ?>
                                        </span>
                                    </div>
                                </td>
                                <?php endif; ?>

                                <td>
                                    <?php if ($fila['nombres']): ?>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border me-2" 
                                                 style="width:25px; height:25px; font-weight:bold; font-size:0.65rem; color: <?php echo $categoria_actual['color']; ?>;">
                                                <?php echo strtoupper(substr($fila['nombres'],0,1).substr($fila['apellidos'],0,1)); ?>
                                            </div>
                                            <small class="fw-bold text-dark"><?php echo $fila['apellidos']; ?></small>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-light text-secondary border fw-normal">Libre</span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-dark"><?php echo $fila['ubicacion'] ?: '-'; ?></small></td>
                                <td><span class="badge-estado <?php echo $clase; ?>"><?php echo $estado; ?></span></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="ver_activo.php?id=<?php echo $fila['id_bien']; ?>" class="btn-action btn-ver"><i class="fas fa-eye"></i></a>
                                        <button class="btn-action btn-editar" onclick='cargarDatosEditar(<?php echo json_encode($fila); ?>)' data-bs-toggle="modal" data-bs-target="#modalEditar"><i class="fas fa-pen"></i></button>
                                        <a href="phpqrcode.php?id=<?php echo $fila['id_bien']; ?>" class="btn-action btn-qr" target="_blank"><i class="fas fa-qrcode"></i></a>
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
    <script>
        $(document).ready(function() {
            $('#tablaGenerica').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
                pageLength: 10,
                ordering: false
            });
            // Preseleccionar categoría en modal
            $('#modalAgregar').on('show.bs.modal', function () {
                var selectCat = $(this).find('select[name="categoria"]');
                if(selectCat.length > 0) selectCat.val("<?php echo $id_cat; ?>");
            });
        });
    </script>
</body>
</html>