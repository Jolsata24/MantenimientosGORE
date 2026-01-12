<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | GORE Pasco</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/login.css">
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <img src="img/logo_gore.png" alt="Logo GORE Pasco" class="logo-img">
            <h1 class="titulo-sistema">Control Patrimonial</h1>
            <p class="subtitulo">Gobierno Regional de Pasco</p>
        </div>

        <div class="login-body">
            
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger text-center py-2 mb-3" role="alert" style="font-size: 0.9rem;">
                    <i class="fas fa-exclamation-circle me-1"></i> Credenciales incorrectas
                </div>
            <?php endif; ?>

            <form action="procesos/validar_login.php" method="POST">
                
                <div class="mb-3">
                    <label class="form-label">USUARIO</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="usuario" class="form-control" placeholder="Ingrese su usuario" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">CONTRASEÑA</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    INGRESAR AL SISTEMA
                </button>

            </form>
        </div>

        <div class="login-footer">
            &copy; <?php echo date('Y'); ?> Unidad de Patrimonio - GORE Pasco
        </div>
    </div>

</body>
</html>