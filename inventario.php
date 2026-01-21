<?php
// inventario.php
// CENTRO DE MANDO: VISTA DETALLADA

include 'conexion.php';
session_start();

if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: login.php");
    exit;
}

// 1. CAPTURAR CATEGORÍA DE LA URL
$cat_id = isset($_GET['categoria']) ? intval($_GET['categoria']) : null;

// 2. CONFIGURACIÓN VISUAL
$config_cats = [
    1 => ['titulo' => 'COMPUTADORAS', 'icono' => 'fa-laptop', 'color' => 'primary', 'bg' => 'bg-primary'],
    2 => ['titulo' => 'IMPRESORAS',   'icono' => 'fa-print',  'color' => 'success', 'bg' => 'bg-success'],
    3 => ['titulo' => 'MONITORES',    'icono' => 'fa-desktop','color' => 'info',    'bg' => 'bg-info']
];

$cat_actual = $cat_id ? ($config_cats[$cat_id] ?? ['titulo' => 'GENERAL', 'icono' => 'fa-box']) : null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario GORE Pasco</title>
    <link rel="icon" type="image/png" href="img/logo_gore.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/inventario.css">
    
    <style>
        /* Estilos Tarjetas Menú */
        .card-menu {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        .card-menu:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        .card-menu .icono-grande { font-size: 3rem; opacity: 0.8; }
        .bg-gradient-custom { background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%); }
        
        /* Estilos Tabla */
        .table-hover tbody tr:hover { background-color: #f8f9fa; }
        .texto-mini { font-size: 0.75rem; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include 'sidebar.php'; ?>

        <div id="page-content-wrapper" class="w-100 bg-light">
            
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-3 py-3">
                <div class="d-flex align-items-center">
                    <button class="btn btn-light me-2" id="menu-toggle"><i class="fas fa-bars"></i></button>
                    <h4 class="m-0 text-dark fw-bold">
                        <?php echo $cat_id ? '<span class="text-'.$cat_actual['color'].'"><i class="fas '.$cat_actual['icono'].' me-2"></i>' . $cat_actual['titulo'] . '</span>' : 'PANEL DE INVENTARIO'; ?>
                    </h4>
                </div>
            </nav>

            <div class="container-fluid p-4">

                <?php if (!$cat_id): ?>
                    <div class="row g-4 justify-content-center mt-2">
                        <?php foreach ($config_cats as $id => $data): 
                            $total = $conn->query("SELECT COUNT(*) as t FROM bienes WHERE id_categoria = $id")->fetch_assoc()['t'];
                        ?>
                        <div class="col-md-4">
                            <a href="inventario.php?categoria=<?php echo $id; ?>" class="text-decoration-none">
                                <div class="card card-menu text-white <?php echo $data['bg']; ?> h-100">
                                    <div class="card-body p-4 position-relative">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="card-title text-uppercase mb-1 opacity-75">Categoría</h5>
                                                <h2 class="fw-bold mb-0"><?php echo $data['titulo']; ?></h2>
                                                <span class="badge bg-white text-dark mt-3 rounded-pill px-3"><?php echo $total; ?> Equipos</span>
                                            </div>
                                            <i class="fas <?php echo $data['icono']; ?> icono-grande"></i>
                                        </div>
                                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-gradient-custom"></div>
                                    </div>
                                    <div class="card-footer bg-transparent border-0 text-center py-3">
                                        <small class="fw-bold">VER LISTADO <i class="fas fa-arrow-right ms-1"></i></small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>

                <?php else: ?>
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex gap-2">
                                <a href="inventario.php" class="btn btn-outline-secondary rounded-pill btn-sm px-3 d-flex align-items-center">
                                    <i class="fas fa-arrow-left me-1"></i> Atrás
                                </a>
                                <h5 class="m-0 align-self-center text-muted border-start ps-3">Listado General</h5>
                            </div>
                            
                            <div class="d-flex gap-2 w-50">
                                <input type="text" id="busqueda" class="form-control rounded-pill bg-light border-0" placeholder="🔍 Buscar por código, serie, persona...">
                                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                                    <i class="fas fa-plus me-1"></i> Nuevo
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-secondary small text-uppercase">
                                        <tr>
                                            <th class="ps-4">Código / Serie</th>
                                            <th>Descripción</th>
                                            <th>Custodio / Responsable</th>
                                            <th>Categoría</th> <th>Ubicación</th>
                                            <th>Estado</th>
                                            <th class="text-end pe-4">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaResultados">
                                        <?php
                                        // CONSULTA CON TODOS LOS JOINs
                                        $sql = "SELECT b.*, c.nombre as nombre_categoria, p.nombres, p.apellidos, p.oficina 
                                                FROM bienes b 
                                                INNER JOIN categorias c ON b.id_categoria = c.id_categoria
                                                LEFT JOIN personal p ON b.id_personal = p.id_personal
                                                WHERE b.id_categoria = $cat_id
                                                ORDER BY b.id_bien DESC";
                                        
                                        $resultado = $conn->query($sql);

                                        if ($resultado->num_rows > 0):
                                            while ($fila = $resultado->fetch_assoc()):
                                                $estadoColor = match ($fila['estado_fisico']) {
                                                    'Bueno' => 'success', 'Regular' => 'warning', 'Malo' => 'danger', 'Baja' => 'dark', default => 'secondary'
                                                };
                                        ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <span class="d-block fw-bold text-primary"><?php echo $fila['codigo_patrimonial']; ?></span>
                                                    <small class="text-muted font-monospace texto-mini"><?php echo $fila['serie']; ?></small>
                                                </td>

                                                <td>
                                                    <span class="d-block fw-bold text-dark"><?php echo $fila['descripcion']; ?></span>
                                                    <small class="text-muted texto-mini">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        M: <?php echo $fila['marca']; ?> / Mod: <?php echo $fila['modelo']; ?>
                                                    </small>
                                                </td>

                                                <td>
                                                    <?php if ($fila['nombres']): ?>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-circle bg-light text-primary fw-bold me-2 border" style="width:30px; height:30px; display:flex; align-items:center; justify-content:center; border-radius:50%;">
                                                                <?php echo strtoupper(substr($fila['nombres'], 0, 1)); ?>
                                                            </div>
                                                            <div style="line-height: 1.2;">
                                                                <span class="d-block small fw-bold"><?php echo $fila['nombres']; ?> <?php echo $fila['apellidos']; ?></span>
                                                                <small class="text-muted texto-mini"><?php echo $fila['oficina']; ?></small>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-muted border fw-normal">Sin Asignar</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <span class="badge bg-white text-secondary border">
                                                        <?php echo $fila['nombre_categoria']; ?>
                                                    </span>
                                                </td>

                                                <td>
                                                    <?php if(!empty($fila['ubicacion'])): ?>
                                                        <small class="fw-bold text-secondary"><i class="fas fa-map-marker-alt text-danger me-1"></i><?php echo $fila['ubicacion']; ?></small>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <span class="badge bg-<?php echo $estadoColor; ?> bg-opacity-10 text-<?php echo $estadoColor; ?> px-2 py-1 rounded-pill small">
                                                        <?php echo $fila['estado_fisico']; ?>
                                                    </span>
                                                </td>

                                                <td class="text-end pe-4">
                                                    <div class="btn-group">
                                                        <a href="ver_activo.php?id=<?php echo $fila['id_bien']; ?>" class="btn btn-sm btn-outline-primary" title="Ver Ficha">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button class="btn btn-sm btn-outline-warning text-dark" onclick="cargarDatosEditar(<?php echo htmlspecialchars(json_encode($fila)); ?>)" data-bs-toggle="modal" data-bs-target="#modalEditar" title="Editar">
                                                            <i class="fas fa-pen"></i>
                                                        </button>
                                                        <a href="phpqrcode.php?id=<?php echo $fila['id_bien']; ?>" class="btn btn-sm btn-outline-dark" target="_blank" title="QR">
                                                            <i class="fas fa-qrcode"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-5">
                                                    <div class="text-muted opacity-50">
                                                        <i class="fas fa-folder-open fa-3x mb-2"></i>
                                                        <p>No hay registros en esta categoría.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'modales_inventario.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var el = document.getElementById("wrapper");
        var toggleButton = document.getElementById("menu-toggle");
        toggleButton.onclick = function () { el.classList.toggle("toggled"); };

        // Buscador
        document.getElementById('busqueda')?.addEventListener('keyup', function() {
            let filtro = this.value.toLowerCase();
            document.querySelectorAll('#tablaResultados tr').forEach(fila => {
                fila.style.display = fila.innerText.toLowerCase().includes(filtro) ? '' : 'none';
            });
        });
    </script>
</body>
</html>