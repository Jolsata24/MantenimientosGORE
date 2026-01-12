<?php
// usuarios.php
include 'conexion.php';
$page = 'usuarios'; // Para iluminar el sidebar

// Consultar usuarios
$sql = "SELECT * FROM usuarios_sistema ORDER BY id_usuario ASC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios | GORE Pasco</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .main-card { border-radius: 15px; border: none; }
        .btn-gore { background-color: #8B0000; color: white; border: none; }
        .btn-gore:hover { background-color: #a00000; color: white; }
        .avatar-circle { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: white; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark"><i class="fas fa-users-cog me-2 text-danger"></i>Gestión de Usuarios</h2>
                    <p class="text-muted">Administradores y personal con acceso al sistema</p>
                </div>
                <button class="btn btn-gore shadow-sm py-2 px-4" data-bs-toggle="modal" data-bs-target="#modalUsuario">
                    <i class="fas fa-user-plus me-2"></i> Nuevo Usuario
                </button>
            </div>

            <div class="card main-card shadow-lg p-3 mb-5 bg-body rounded">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaUsuarios" class="table table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Usuario</th>
                                    <th>Rol / Permisos</th>
                                    <th>Estado</th> <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($fila = $resultado->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $fila['id_usuario']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-secondary me-3">
                                                <?php echo strtoupper(substr($fila['nombre_usuario'], 0, 2)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?php echo $fila['nombre_usuario']; ?></div>
                                                <small class="text-muted">Acceso Sistema</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if($fila['rol'] == 'Administrador'): ?>
                                            <span class="badge bg-danger rounded-pill px-3">
                                                <i class="fas fa-shield-alt me-1"></i> Administrador
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-info text-dark rounded-pill px-3">
                                                <i class="fas fa-eye me-1"></i> Lector / Visitante
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-success dot-badge">Activo</span></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-dark" title="Cambiar Contraseña"><i class="fas fa-key"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Registrar Nuevo Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesos/guardar_usuario.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre de Usuario</label>
                            <input type="text" name="usuario" class="form-control" placeholder="Ej. jperez" required autocomplete="off">
                            <div class="form-text">Este será el ID para iniciar sesión.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Contraseña</label>
                            <input type="password" name="password" class="form-control" placeholder="*******" required autocomplete="new-password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Rol de Acceso</label>
                            <select name="rol" class="form-select">
                                <option value="Lector">Lector (Solo ver)</option>
                                <option value="Administrador">Administrador (Control Total)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-gore">Guardar Usuario</button>
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
            $('#tablaUsuarios').DataTable({ language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' } });
        });
    </script>
</body>
</html>