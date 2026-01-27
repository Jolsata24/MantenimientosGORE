<link rel="stylesheet" href="css/estilos.css">

<?php 
// Aseguramos que la variable rol exista para evitar errores
$rol = isset($_SESSION['rol']) ? strtolower($_SESSION['rol']) : ''; 
?>

<div class="d-flex flex-column flex-shrink-0 text-white sidebar sidebar-container">
    
    <div class="d-md-none text-end p-2">
        <button class="btn btn-sm text-white" onclick="toggleSidebar()"><i class="fas fa-times fa-lg"></i></button>
    </div>

    <a href="index.php" class="d-flex align-items-center text-white text-decoration-none sidebar-logo-area">
        <img src="img/logo_gore.png" alt="GORE Pasco" width="50" height="50" class="me-3 bg-white rounded-circle p-1 shadow-sm" style="object-fit: contain;">
        <div class="lh-1">
            <span class="fs-6 fw-bold d-block text-uppercase">GORE PASCO</span>
            <small style="font-size: 0.75rem; color: var(--color-gore-dorado);">Patrimonio v1.0</small>
        </div>
    </a>

    <div class="p-3">
        <ul class="nav nav-pills flex-column mb-auto">
            
            <li class="nav-item">
                <a href="index.php" class="nav-link <?php echo (isset($page) && $page == 'dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-home me-3 text-center" style="width:20px;"></i> Dashboard
                </a>
            </li>

            <li>
                <a href="inventario.php" class="nav-link <?php echo (isset($page) && $page == 'inventario') ? 'active' : ''; ?>">
                    <i class="fas fa-boxes me-3 text-center" style="width:20px;"></i> Inventario
                </a>
            </li>

            <li>
                <a href="personal.php" class="nav-link <?php echo (isset($page) && $page == 'personal') ? 'active' : ''; ?>">
                    <i class="fas fa-id-card-alt me-3 text-center" style="width:20px;"></i> Personal
                </a>
            </li>

            <li>
                <a href="reportes.php" class="nav-link <?php echo (isset($page) && $page == 'reportes') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-pie me-3 text-center" style="width:20px;"></i> Reportes
                </a>
            </li>

            <?php if($rol == 'administrador' || $rol == 'sistemas'): ?>
                
                <li class="nav-item mt-3 mb-2">
                    <span class="text-white-50 small text-uppercase fw-bold ps-3">Administración</span>
                </li>

                <li>
                    <a href="usuarios.php" class="nav-link <?php echo (isset($page) && $page == 'usuarios') ? 'active' : ''; ?>">
                        <i class="fas fa-users-cog me-3 text-center" style="width:20px;"></i> Usuarios
                    </a>
                </li>

                <?php if ($rol == 'sistemas'): ?>
                    <li class="nav-item">
                        <a href="auditoria.php" class="nav-link <?php echo (isset($page) && $page == 'auditoria') ? 'active' : ''; ?>">
                            <i class="fas fa-shield-alt me-3 text-center" style="width:20px;"></i> Auditoría
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="procesos/importar_glpi.php" target="_blank" class="nav-link text-white-50">
                            <i class="fas fa-file-import me-3 text-center" style="width:20px;"></i> GLPI Import
                        </a>
                    </li>
                <?php endif; ?>

            <?php endif; ?>

        </ul>
    </div>

    <div class="mt-auto p-3 border-top border-secondary">
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="rounded-circle d-flex justify-content-center align-items-center me-2 bg-white text-primary fw-bold" style="width: 32px; height: 32px;">
                    <?php echo strtoupper(substr($_SESSION['nombre_usuario'] ?? 'U', 0, 1)); ?>
                </div>
                <strong><?php echo $_SESSION['nombre_usuario'] ?? 'Usuario'; ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                <li><a class="dropdown-item" href="#">Perfil</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-warning" href="procesos/logout.php">Cerrar Sesión</a></li>
            </ul>
        </div>
    </div>
</div>

<div id="overlay-sidebar" onclick="toggleSidebar()"></div>

<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('overlay-sidebar');
        
        // Verificamos si los elementos existen antes de intentar usarlos
        if (sidebar && overlay) {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        } else {
            console.error("No se encontraron los elementos sidebar u overlay");
        }
    }
</script>