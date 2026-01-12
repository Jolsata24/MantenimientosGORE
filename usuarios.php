<?php
// usuarios.php
include 'conexion.php';
session_start();

// Seguridad: Verificar sesión
if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: login.php");
    exit();
}

$page = 'usuarios';

// Consultar usuarios
$sql = "SELECT * FROM usuarios_sistema ORDER BY id_usuario ASC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios del Sistema | GORE Pasco</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <link rel="stylesheet" href="css/usuarios.css">
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            
            <div class="page-header">
                <div>
                    <h2 class="titulo-seccion">
                        <i class="fas fa-users-cog text-secondary me-3 opacity-50"></i> Usuarios del Sistema
                    </h2>
                    <small class="text-muted">Administradores y personal con acceso al software</small>
                </div>
                <button class="btn-nuevo" data-bs-toggle="modal" data-bs-target="#modalUsuario">
                    <i class="fas fa-user-plus me-2"></i> Nuevo Usuario
                </button>
            </div>

            <div class="card card-tabla p-3">
                <div class="table-responsive">
                    <table id="tablaUsuarios" class="table align-middle w-100">
                        <thead>
                            <tr>
                                <th>Usuario / Identidad</th>
                                <th>Rol de Acceso</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($fila = $resultado->fetch_assoc()): ?>
                                <?php 
                                    // Generar avatar (Primeras 2 letras)
                                    $avatar = strtoupper(substr($fila['nombre_usuario'], 0, 2));
                                    
                                    // Definir estilo según rol
                                    $clase_rol = ($fila['rol'] == 'Administrador') ? 'rol-admin' : 'rol-lector';
                                    $icono_rol = ($fila['rol'] == 'Administrador') ? 'fa-shield-alt' : 'fa-eye';
                                ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-usuario"><?php echo $avatar; ?></div>
                                        <div>
                                            <div class="fw-bold text-dark fs-5"><?php echo $fila['nombre_usuario']; ?></div>
                                            <small class="text-muted">ID Sistema: <?php echo $fila['id_usuario']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-rol <?php echo $clase_rol; ?>">
                                        <i class="fas <?php echo $icono_rol; ?> me-2"></i> <?php echo $fila['rol']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-success border border-success px-3 rounded-pill">
                                        <i class="fas fa-check-circle me-1"></i> Activo
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn-action btn-key" title="Cambiar Contraseña">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    
                                    <?php if($fila['id_usuario'] != 1): ?>
                                        <button class="btn-action btn-delete" title="Eliminar Usuario">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header modal-header-gore">
                    <h5 class="modal-title"><i class="fas fa-user-shield me-2"></i>Crear Credenciales</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesos/guardar_usuario.php" method="POST">
                    <div class="modal-body p-4">
                        
                        <div class="alert alert-light border-start border-4 border-info mb-4">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            <small>El nuevo usuario podrá iniciar sesión inmediatamente.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">NOMBRE DE USUARIO</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                                <input type="text" name="usuario" class="form-control" placeholder="Ej. jperez" required autocomplete="off">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">CONTRASEÑA</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-key"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="••••••••" required autocomplete="new-password">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">NIVEL DE ACCESO</label>
                            <select name="rol" class="form-select">
                                <option value="Lector">Lector (Solo ver reportes e inventario)</option>
                                <option value="Administrador">Administrador (Control Total)</option>
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-nuevo">
                            <i class="fas fa-save me-2"></i> Guardar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#tablaUsuarios').DataTable({ 
                language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
                lengthChange: false
            });
        });
    </script>
</body>
</html>