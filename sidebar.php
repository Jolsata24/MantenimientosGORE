<style>
    /* --- ESTILOS GLOBALES DEL SIDEBAR Y LAYOUT --- */
    .sidebar-container {
        width: 280px;
        height: 100vh; /* Ocupa todo el alto */
        position: fixed; /* Se queda fijo al hacer scroll */
        top: 0;
        left: 0;
        z-index: 1000;
        overflow-y: auto;
        background: linear-gradient(180deg, #212529 0%, #343a40 100%); /* Degradado sutil */
        transition: all 0.3s;
    }

    /* Esto es lo que faltaba: Empuja el contenido a la derecha */
    .main-content {
        margin-left: 280px;
        padding: 30px;
        width: calc(100% - 280px);
        min-height: 100vh;
        transition: all 0.3s;
    }

    /* Enlace activo (Rojo GORE) */
    .nav-pills .nav-link.active, .nav-pills .show > .nav-link {
        background-color: #8B0000 !important; /* Rojo Institucional */
        color: white !important;
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
    }
    
    .nav-link {
        color: #e0e0e0;
        margin-bottom: 5px;
        border-radius: 8px; /* Bordes redondeados en los botones */
    }
    .nav-link:hover {
        background-color: rgba(255,255,255,0.1);
        color: white;
    }

    /* RESPONSIVE: En celulares, el menú se oculta o cambia */
    @media (max-width: 768px) {
        .sidebar-container {
            margin-left: -280px; /* Se esconde a la izquierda */
        }
        .sidebar-active {
            margin-left: 0;
        }
        .main-content {
            margin-left: 0;
            width: 100%;
            padding: 15px;
        }
    }
</style>

<div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark sidebar-container">
    <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <img src="img/logo_gore.png" alt="GORE Logo" width="50" height="auto" class="me-3 bg-white rounded-circle p-1">
        <div class="lh-1">
            <span class="fs-6 fw-bold d-block">GORE PASCO</span>
            <small class="text-white-50" style="font-size: 0.75rem;">SISTEMA PATRIMONIO</small>
        </div>
    </a>
    <hr class="text-secondary">
    
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="index.php" class="nav-link <?php echo (isset($page) && $page == 'dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-home me-3" style="width: 20px; text-align: center;"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="inventario.php" class="nav-link <?php echo (isset($page) && $page == 'inventario') ? 'active' : ''; ?>">
                <i class="fas fa-boxes me-3" style="width: 20px; text-align: center;"></i> Inventario
            </a>
        </li>
        <li>
            <a href="personal.php" class="nav-link <?php echo (isset($page) && $page == 'personal') ? 'active' : ''; ?>">
                <i class="fas fa-id-card-alt me-3" style="width: 20px; text-align: center;"></i> Personal
            </a>
        </li>
        <li>
            <a href="reportes.php" class="nav-link <?php echo (isset($page) && $page == 'reportes') ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie me-3" style="width: 20px; text-align: center;"></i> Reportes
            </a>
        </li>
        <li>
            <a href="usuarios.php" class="nav-link <?php echo (isset($page) && $page == 'usuarios') ? 'active' : ''; ?>">
                <i class="fas fa-users-cog me-3" style="width: 20px; text-align: center;"></i> Usuarios Sistema
            </a>
        </li>
    </ul>
    
    <hr class="text-secondary">
    
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="bg-danger rounded-circle d-flex justify-content-center align-items-center me-2" style="width: 32px; height: 32px;">
                <i class="fas fa-user"></i>
            </div>
            <strong>Admin</strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
            <li><a class="dropdown-item" href="#">Perfil</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="login.php">Cerrar Sesión</a></li>
        </ul>
    </div>
</div>