<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema | GORE Pasco</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        body {
            /* Asegúrate de que la imagen fondo_login.jpg exista en tu carpeta img */
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('img/fondo_login.jpg');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.95); /* Un poco transparente */
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            overflow: hidden;
            border: none;
        }
        .login-header {
            background-color: #8B0000;
            padding: 30px 20px;
            text-align: center;
            color: white;
            position: relative;
        }
        .login-header::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            border-width: 10px 10px 0;
            border-style: solid;
            border-color: #8B0000 transparent transparent transparent;
        }
        .btn-gore {
            background-color: #8B0000;
            border: none;
            padding: 12px;
            font-size: 1.1rem;
            transition: transform 0.2s;
        }
        .btn-gore:hover {
            background-color: #a00000;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <div class="card login-card animate__animated animate__fadeInUp">
        <div class="login-header">
            <img src="img/logo_gore.png" alt="Logo" width="90" class="bg-white rounded-circle p-1 mb-3 shadow-sm">
            <h4 class="fw-bold mb-0">BIENVENIDO</h4>
            <small class="opacity-75">Sistema de Control Patrimonial</small>
        </div>
        
        <div class="card-body p-4 pt-5">
            <form action="procesos/validar_login.php" method="POST">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Usuario" required>
                    <label for="usuario"><i class="fas fa-user me-2 text-muted"></i>Usuario</label>
                </div>
                
                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
                    <label for="password"><i class="fas fa-lock me-2 text-muted"></i>Contraseña</label>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-gore shadow">
                        INGRESAR <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
        <div class="card-footer bg-light text-center py-3 border-0">
            <small class="text-muted">Gobierno Regional de Pasco &copy; <?php echo date('Y'); ?></small>
        </div>
    </div>

</body>
</html>