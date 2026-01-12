<?php
// ver_personal.php
include 'conexion.php';
session_start();

if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) { header("Location: login.php"); exit(); }

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Datos del Personal
$sql = "SELECT p.*, a.nombre_area FROM personal p 
        LEFT JOIN areas a ON p.id_area = a.id_area 
        WHERE p.id_personal = $id";
$row = $conn->query($sql)->fetch_assoc();

if (!$row) die("Personal no encontrado");

// 2. CONSULTA DE BIENES ASIGNADOS (LO NUEVO)
// Buscamos en la tabla 'bienes' todos los que tengan el id_personal de esta persona
$sql_bienes = "SELECT b.*, c.nombre as nombre_categoria 
               FROM bienes b 
               LEFT JOIN categorias c ON b.id_categoria = c.id_categoria 
               WHERE b.id_personal = $id
               ORDER BY b.descripcion ASC";
$res_bienes = $conn->query($sql_bienes);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha Personal | <?php echo $row['apellidos']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="img/logo_gore.png">
    <link rel="stylesheet" href="css/ver_activo.css">
</head>
<body>

    <div class="header-ficha bg-dark" style="background: linear-gradient(135deg, #2c3e50, #4ca1af);">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="titulo-ficha text-white"><i class="fas fa-file-signature me-2 opacity-50"></i>Hoja de Resguardo</h1>
                <a href="personal.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Volver al Directorio
                </a>
            </div>
        </div>
    </div>

    <div class="container mt-4 pb-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card card-flotante mb-4 text-center p-4">
                    <div class="mb-3">
                        <div style="width:100px; height:100px; background:#e9ecef; border-radius:50%; margin:0 auto; display:flex; align-items:center; justify-content:center; font-size:2.5rem; color:#555; border: 4px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                            <?php echo strtoupper(substr($row['nombres'],0,1).substr($row['apellidos'],0,1)); ?>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-1"><?php echo $row['nombres'] . ' ' . $row['apellidos']; ?></h4>
                    <p class="text-primary fw-bold mb-1"><?php echo $row['cargo']; ?></p>
                    <span class="badge bg-light text-dark border mb-3"><?php echo $row['nombre_area']; ?></span>
                    
                    <div class="text-start bg-light p-3 rounded mt-2">
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted small">DNI</span>
                            <span class="fw-bold"><?php echo $row['dni']; ?></span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted small">Teléfono</span>
                            <span class="fw-bold"><?php echo $row['telefono'] ? $row['telefono'] : '-'; ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Estado</span>
                            <?php if($row['estado'] == 'Activo'): ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success px-2">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-flotante">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-laptop-house me-2 text-warning"></i>Bienes Patrimoniales Asignados
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        
                        <?php if($res_bienes->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4">Código / Descripción</th>
                                            <th>Categoría</th>
                                            <th>Estado</th>
                                            <th class="text-center pe-4">Ver</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($bien = $res_bienes->fetch_assoc()): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold text-primary">
                                                        <i class="fas fa-barcode me-1 text-muted opacity-50"></i> 
                                                        <?php echo $bien['codigo_patrimonial']; ?>
                                                    </div>
                                                    <div class="text-dark small"><?php echo $bien['descripcion']; ?></div>
                                                    <div class="text-muted small fst-italic">
                                                        <?php echo $bien['marca']; ?> <?php echo $bien['modelo']; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">
                                                        <?php echo $bien['nombre_categoria']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $bg = 'bg-success';
                                                    if($bien['estado_fisico']=='Regular') $bg = 'bg-warning text-dark';
                                                    if($bien['estado_fisico']=='Malo') $bg = 'bg-danger';
                                                    ?>
                                                    <span class="badge <?php echo $bg; ?> rounded-pill" style="font-size:0.75rem;">
                                                        <?php echo $bien['estado_fisico']; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center pe-4">
                                                    <a href="ver_activo.php?id=<?php echo $bien['id_bien']; ?>" class="btn btn-sm btn-outline-primary rounded-circle" style="width:32px; height:32px; padding:0; display:inline-flex; align-items:center; justify-content:center;">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-folder-open fa-3x text-muted opacity-25 mb-3"></i>
                                <h6 class="text-muted">No tiene bienes asignados actualmente.</h6>
                                <p class="small text-muted">Puede asignar bienes desde el módulo de Inventario.</p>
                                <a href="inventario.php" class="btn btn-sm btn-primary mt-2">Ir a Inventario</a>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Detectar parámetros en la URL para mostrar alertas
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
    } else if (status === 'error') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Hubo un problema al procesar la solicitud.',
        });
    }
    
    // Limpiar la URL para que no salga la alerta al recargar
    if (status) {
        window.history.replaceState(null, null, window.location.pathname);
    }
</script>
</body>
</html>