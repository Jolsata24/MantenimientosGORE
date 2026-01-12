<?php
// usuarios.php
include 'conexion.php';
session_start();

if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: login.php");
    exit();
}

// Verificar si es Administrador (Solo admins pueden ver esto)
if ($_SESSION['rol'] !== 'Administrador') {
    echo "<script>alert('Acceso no autorizado.'); window.location.href='index.php';</script>";
    exit();
}

$page = 'usuarios';
$id_actual = $_SESSION['id_usuario']; // Tu ID actual para no borrarte a ti mismo

// Consulta
$sql = "SELECT * FROM usuarios_sistema ORDER BY id_usuario ASC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios | GORE Pasco</title>
    <link rel="icon" type="image/png" href="img/logo_gore.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
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
                    <small class="text-muted">Gestión de accesos y seguridad</small>
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
                                    $avatar = strtoupper(substr($fila['nombre_usuario'], 0, 2));
                                    $clase_rol = ($fila['rol'] == 'Administrador') ? 'rol-admin' : 'rol-lector';
                                    $icono_rol = ($fila['rol'] == 'Administrador') ? 'fa-shield-alt' : 'fa-eye';
                                    
                                    // REGLAS DE SEGURIDAD PARA BOTONES
                                    $es_super_admin = ($fila['id_usuario'] == 1);
                                    $soy_yo = ($fila['id_usuario'] == $id_actual);
                                    $se_puede_borrar = (!$es_super_admin && !$soy_yo);
                                ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-usuario"><?php echo $avatar; ?></div>
                                        <div>
                                            <div class="fw-bold text-dark fs-5">
                                                <?php echo $fila['nombre_usuario']; ?>
                                                <?php if($soy_yo) echo '<span class="badge bg-success ms-2" style="font-size:0.6rem">TÚ</span>'; ?>
                                            </div>
                                            <small class="text-muted">ID Sistema: <?php echo $fila['id_usuario']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-rol <?php echo $clase_rol; ?>">
                                        <i class="fas <?php echo $icono_rol; ?> me-2"></i> <?php echo $fila['rol']; ?>
                                    </span>
                                </td>
                                <td><span class="badge bg-light text-success border border-success px-3 rounded-pill">Activo</span></td>
                                <td class="text-center">
                                    
                                    <button class="btn-action btn-key" title="Editar Datos" 
                                        onclick="cargarEditar('<?php echo $fila['id_usuario']; ?>', '<?php echo $fila['nombre_usuario']; ?>', '<?php echo $fila['rol']; ?>')"
                                        data-bs-toggle="modal" data-bs-target="#modalEditar">
                                        <i class="fas fa-pen"></i>
                                    </button>

                                    <button class="btn-action btn-key text-warning" title="Resetear Contraseña" 
                                        onclick="cargarPassword('<?php echo $fila['id_usuario']; ?>', '<?php echo $fila['nombre_usuario']; ?>')"
                                        data-bs-toggle="modal" data-bs-target="#modalPassword">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    
                                    <?php if($se_puede_borrar): ?>
                                        <button class="btn-action btn-delete" title="Eliminar Usuario" 
                                            onclick="confirmarEliminar('<?php echo $fila['id_usuario']; ?>')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-action" disabled style="cursor:not-allowed; opacity:0.3;"><i class="fas fa-trash-alt"></i></button>
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
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Nuevo Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesos/guardar_usuario.php" method="POST">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="fw-bold small text-muted">USUARIO</label>
                            <input type="text" name="usuario" class="form-control" required autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold small text-muted">CONTRASEÑA</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold small text-muted">ROL</label>
                            <select name="rol" class="form-select">
                                <option value="Lector">Lector</option>
                                <option value="Administrador">Administrador</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-nuevo">Guardar</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark"><i class="fas fa-user-edit me-2"></i>Editar Rol/Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesos/editar_usuario.php" method="POST">
                    <input type="hidden" name="id_usuario" id="edit_id">
                    <div class="modal-body p-4">
                        <div class="alert alert-warning small">
                            <i class="fas fa-exclamation-triangle me-1"></i> Si cambia el rol, los permisos se actualizarán al instante.
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold small text-muted">NOMBRE DE USUARIO</label>
                            <input type="text" name="usuario" id="edit_usuario" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold small text-muted">NIVEL DE ACCESO</label>
                            <select name="rol" id="edit_rol" class="form-select">
                                <option value="Lector">Lector</option>
                                <option value="Administrador">Administrador</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning fw-bold">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPassword" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="fas fa-key me-2"></i>Resetear Contraseña</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesos/cambiar_password.php" method="POST">
                    <input type="hidden" name="id_usuario" id="pass_id">
                    <div class="modal-body p-4">
                        <p>Cambiando clave para: <strong id="pass_user" class="text-primary"></strong></p>
                        <div class="mb-3">
                            <label class="fw-bold small text-muted">NUEVA CONTRASEÑA</label>
                            <input type="password" name="password" class="form-control" placeholder="Escriba la nueva clave" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-dark">Guardar Nueva Clave</button>
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
        $(document).ready(function () { $('#tablaUsuarios').DataTable({ language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' }, lengthChange: false }); });

        function cargarEditar(id, usuario, rol) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_usuario').value = usuario;
            document.getElementById('edit_rol').value = rol;
        }

        function cargarPassword(id, usuario) {
            document.getElementById('pass_id').value = id;
            document.getElementById('pass_user').innerText = usuario;
        }

        function confirmarEliminar(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará el acceso de este usuario permanentemente.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'procesos/eliminar_usuario.php?id=' + id;
                }
            })
        }
    </script>
</body>
</html>