<div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark sidebar-container">
    <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        
        <img src="img/logo_gore.png" alt="GORE Logo" width="50" height="auto" class="me-3">
        
        <div class="lh-1">
            <span class="fs-6 fw-bold d-block">GORE PASCO</span>
            <small class="text-white-50" style="font-size: 0.7rem;">SISTEMA PATRIMONIO</small>
        </div>
    </a>
    <hr>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema | GORE Pasco</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            /* Imagen de fondo de Pasco */
            background-image: url('img/fondo_login.jpg'); 
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        /* Capa oscura para que el texto se lea bien sobre la foto */
        .overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6); /* Oscuridad al 60% */
            z-index: 1;
        }
        .login-card {
            z-index: 2; /* Para que flote sobre la capa oscura */
            width: 100%;
            max-width: 400px;
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            overflow: hidden;
        }
        .card-header-gore {
            background-color: #8B0000; /* Color Institucional */
            color: white;
            text-align: center;
            padding: 20px;
        }
        .btn-gore {
            background-color: #8B0000;
            color: white;
            font-weight: bold;
        }
        .btn-gore:hover { background-color: #a00000; color: white; }
    </style>
</head>
<body>

    <div class="overlay"></div>

    <div class="card login-card animate__animated animate__fadeInDown">
        <div class="card-header-gore">
            <img src="img/logo_gore.png" alt="Logo" width="80" class="mb-2 bg-white rounded-circle p-1">
            <h5 class="mb-0">Control Patrimonial</h5>
            <small>Gobierno Regional de Pasco</small>
        </div>
        <div class="card-body p-4">
            
            <form action="procesos/validar_login.php" method="POST"> <div class="mb-3">
                    <label class="form-label text-muted">Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                        <input type="text" name="usuario" class="form-control" placeholder="Ingrese su usuario" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label text-muted">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-gore py-2">INGRESAR AL SISTEMA</button>
                </div>
            </form>

        </div>
        <div class="card-footer text-center bg-light py-3">
            <small class="text-muted">¿Olvidó su contraseña? Contacte a Sistemas.</small>
        </div>
    </div>

</body>
</html>