<link rel="stylesheet" href="css/estilos.css">

<div class="d-flex flex-column flex-shrink-0 text-white sidebar-container">
    
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
            <li>
                <a href="usuarios.php" class="nav-link <?php echo (isset($page) && $page == 'usuarios') ? 'active' : ''; ?>">
                    <i class="fas fa-users-cog me-3 text-center" style="width:20px;"></i> Usuarios
                </a>
            </li>
        </ul>
    </div>
    
    <div class="mt-auto p-3 border-top border-secondary">
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="rounded-circle d-flex justify-content-center align-items-center me-2 bg-white text-primary fw-bold" style="width: 32px; height: 32px;">
                    A
                </div>
                <strong>Admin</strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                <li><a class="dropdown-item" href="#">Configuración</a></li>
                <li><hr class="dropdown-divider"></li>
                <a class="dropdown-item text-warning" href="logout.php">Cerrar Sesión</a>
            </ul>
        </div>
    </div>
</div>