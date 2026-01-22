<?php
// inventario.php
// CORRECCIÓN IMPORTANTE: La conexión debe ir PRIMERO para evitar el error "Call to query() on null"
include 'conexion.php'; 
session_start();

// Validación de seguridad
if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: login.php");
    exit;
}

$page = 'inventario'; // Variable para marcar activo el sidebar
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
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/inventario.css">
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">

            <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h2 class="titulo-seccion mb-0">
                        <i class="fas fa-boxes text-secondary me-3 opacity-50"></i> Inventario General
                    </h2>
                    <small class="text-muted">Gestión de activos y patrimonio</small>
                </div>
                
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaCategoria">
                        <i class="fas fa-folder-plus me-2"></i> Crear Categoría
                    </button>
                    
                    <button class="btn-nuevo" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                        <i class="fas fa-plus me-2"></i> Nuevo Activo
                    </button>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <?php
                // CONSULTA DINÁMICA: Trae todas las categorías activas de la BD
                // Esto hará que aparezcan solas las nuevas categorías que crees.
                $sql_cats = "SELECT * FROM categorias WHERE estado = 'Activo' ORDER BY id_categoria ASC";
                $res_cats = $conn->query($sql_cats);

                if ($res_cats && $res_cats->num_rows > 0):
                    while ($cat = $res_cats->fetch_assoc()):
                        $id = $cat['id_categoria'];
                        
                        // Contar cuántos equipos hay en esta categoría
                        $sql_count = "SELECT COUNT(*) as t FROM bienes WHERE id_categoria = $id";
                        $total = $conn->query($sql_count)->fetch_assoc()['t'];
                        
                        // Definir a qué archivo redirigir
                        // Las categorías 1, 2 y 3 tienen archivos personalizados.
                        // Las nuevas usarán el archivo genérico "ver_categoria.php".
                        $link = "ver_categoria.php?id=$id";
                        if($id == 1) $link = "computacion.php";
                        if($id == 2) $link = "impresora.php";
                        if($id == 3) $link = "monitor.php";
                        // Si creaste proyectores.php manualmente para el ID 4, descomenta esto:
                        // if($id == 4) $link = "proyectores.php"; 
                ?>
                <div class="col-md-3">
                    <a href="<?php echo $link; ?>" class="text-decoration-none">
                        <div class="card card-gore h-100 border-0 shadow-sm" style="border-left: 5px solid <?php echo $cat['color']; ?> !important;">
                            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted text-uppercase small fw-bold mb-1">Categoría</h6>
                                    <h3 class="fw-bold text-dark mb-0"><?php echo $cat['nombre']; ?></h3>
                                    <div class="mt-3">
                                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                            <i class="fas <?php echo $cat['icono']; ?> me-1"></i> <?php echo $total; ?> Equipos
                                        </span>
                                    </div>
                                </div>
                                <i class="fas <?php echo $cat['icono']; ?> fa-3x opacity-25" style="color: <?php echo $cat['color']; ?>;"></i>
                            </div>
                        </div>
                    </a>
                </div>
                <?php 
                    endwhile; 
                else: 
                ?>
                    <div class="col-12 text-center text-muted py-5">
                        <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                        <p>No hay categorías registradas. ¡Crea una nueva!</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <?php include 'modales_inventario.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Lógica del Menú Móvil
        const btnMenu = document.getElementById('btnMenu');
        const sidebar = document.querySelector('.sidebar-container');
        const overlay = document.getElementById('overlay'); // Asegúrate de tener este div en el HTML si usas sidebar móvil

        if(btnMenu){
            btnMenu.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                if(overlay) overlay.classList.toggle('active');
            });
        }

        // Alertas de SweetAlert según parámetros URL
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');

        if (status === 'success') {
            Swal.fire({ icon: 'success', title: 'Registrado', text: 'El activo se guardó correctamente.', confirmButtonColor: '#00609C' });
        } else if (status === 'updated') {
            Swal.fire({ icon: 'info', title: 'Actualizado', text: 'Los datos fueron modificados.', confirmButtonColor: '#FDB913' });
        } else if (status === 'cat_created') {
            Swal.fire({ icon: 'success', title: 'Categoría Creada', text: 'La nueva categoría ya está disponible.', confirmButtonColor: '#28a745' });
        }
        
        // Limpiar URL
        if (status) window.history.replaceState(null, null, window.location.pathname);
    </script>

</body>
</html>