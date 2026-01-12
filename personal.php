<?php
// personal.php
include 'conexion.php';
session_start();

if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: login.php");
    exit();
}

$page = 'personal';

// Consulta principal
$sql = "SELECT p.*, a.nombre_area 
        FROM personal p 
        LEFT JOIN areas a ON p.id_area = a.id_area 
        ORDER BY p.apellidos ASC";
$resultado = $conn->query($sql);

// Consulta para selectores (Áreas)
$sql_areas = "SELECT * FROM areas";
$res_areas = $conn->query($sql_areas); // Para Modal Nuevo
$res_areas_edit = $conn->query($sql_areas); // Para Modal Editar
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal | GORE Pasco</title>
    <link rel="icon" type="image/png" href="img/logo_gore.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <link rel="stylesheet" href="css/personal.css">
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            
            <div class="page-header">
                <div>
                    <h2 class="titulo-seccion">
                        <i class="fas fa-users text-secondary me-3 opacity-50"></i> Directorio de Personal
                    </h2>
                    <small class="text-muted">Gestión de funcionarios y asignación de bienes</small>
                </div>
                <button class="btn-nuevo" data-bs-toggle="modal" data-bs-target="#modalPersonal">
                    <i class="fas fa-user-plus me-2"></i> Nuevo Funcionario
                </button>
            </div>

            <div class="card card-tabla p-3">
                <div class="table-responsive">
                    <table id="tablaPersonal" class="table align-middle w-100">
                        <thead>
                            <tr>
                                <th>Funcionario</th>
                                <th>DNI / Contacto</th>
                                <th>Cargo</th>
                                <th>Oficina</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($fila = $resultado->fetch_assoc()): ?>
                                <?php 
                                    $iniciales = strtoupper(substr($fila['nombres'], 0, 1) . substr($fila['apellidos'], 0, 1));
                                ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-iniciales"><?php echo $iniciales; ?></div>
                                        <div>
                                            <div class="fw-bold text-dark">
                                                <?php echo $fila['apellidos']; ?>, <?php echo $fila['nombres']; ?>
                                            </div>
                                            <small class="text-muted">ID: <?php echo $fila['id_personal']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary"><i class="far fa-id-card me-1"></i> <?php echo $fila['dni']; ?></div>
                                    <small class="text-muted"><i class="fas fa-phone-alt me-1"></i> <?php echo $fila['telefono']; ?></small>
                                </td>
                                <td><?php echo $fila['cargo']; ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?php echo $fila['nombre_area']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($fila['estado'] == 'Activo'): ?>
                                        <span class="badge-estado estado-activo">Activo</span>
                                    <?php else: ?>
                                        <span class="badge-estado estado-inactivo">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="ver_personal.php?id=<?php echo $fila['id_personal']; ?>" class="btn-action btn-bienes" title="Ver Bienes Asignados">
                                        <i class="fas fa-laptop-house"></i>
                                    </a>
                                    
                                    <button class="btn-action btn-editar" title="Editar Datos"
                                        data-bs-toggle="modal" data-bs-target="#modalEditarPersonal"
                                        onclick="cargarEditarPersonal(
                                            '<?php echo $fila['id_personal']; ?>',
                                            '<?php echo $fila['dni']; ?>',
                                            '<?php echo $fila['nombres']; ?>',
                                            '<?php echo $fila['apellidos']; ?>',
                                            '<?php echo $fila['cargo']; ?>',
                                            '<?php echo $fila['id_area']; ?>',
                                            '<?php echo $fila['telefono']; ?>',
                                            '<?php echo $fila['estado']; ?>'
                                        )">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="modalPersonal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header modal-header-gore">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Registrar Nuevo Funcionario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesos/guardar_personal.php" method="POST">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">DNI</label>
                                <input type="text" name="dni" class="form-control" maxlength="8" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">NOMBRES</label>
                                <input type="text" name="nombres" class="form-control" required style="text-transform: uppercase;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">APELLIDOS</label>
                                <input type="text" name="apellidos" class="form-control" required style="text-transform: uppercase;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">CARGO</label>
                                <input type="text" name="cargo" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">OFICINA</label>
                                <select name="id_area" class="form-select" required>
                                    <?php while($area = $res_areas->fetch_assoc()): ?>
                                        <option value="<?php echo $area['id_area']; ?>"><?php echo $area['nombre_area']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">TELÉFONO</label>
                                <input type="text" name="telefono" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-nuevo">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditarPersonal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark"><i class="fas fa-user-edit me-2"></i>Editar Datos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesos/actualizar_personal.php" method="POST"> <input type="hidden" name="id_personal" id="edit_id">
                    
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">DNI</label>
                                <input type="text" name="dni" id="edit_dni" class="form-control" maxlength="8" required readonly style="background-color:#e9ecef;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">NOMBRES</label>
                                <input type="text" name="nombres" id="edit_nombres" class="form-control" required style="text-transform: uppercase;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">APELLIDOS</label>
                                <input type="text" name="apellidos" id="edit_apellidos" class="form-control" required style="text-transform: uppercase;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">CARGO</label>
                                <input type="text" name="cargo" id="edit_cargo" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">OFICINA</label>
                                <select name="id_area" id="edit_area" class="form-select" required>
                                    <?php while($area = $res_areas_edit->fetch_assoc()): ?>
                                        <option value="<?php echo $area['id_area']; ?>"><?php echo $area['nombre_area']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">TELÉFONO</label>
                                <input type="text" name="telefono" id="edit_telefono" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">ESTADO</label>
                                <select name="estado" id="edit_estado" class="form-select">
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo (Baja)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning text-dark fw-bold">Actualizar Datos</button>
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
            $('#tablaPersonal').DataTable({ language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' }, lengthChange: false, pageLength: 8 });
        });

        // Función para cargar datos en el Modal Editar
        function cargarEditarPersonal(id, dni, nombres, apellidos, cargo, area, telefono, estado) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_dni').value = dni;
            document.getElementById('edit_nombres').value = nombres;
            document.getElementById('edit_apellidos').value = apellidos;
            document.getElementById('edit_cargo').value = cargo;
            document.getElementById('edit_area').value = area;
            document.getElementById('edit_telefono').value = telefono;
            document.getElementById('edit_estado').value = estado;
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Detectar parámetros en la URL para mostrar alertas
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

    if (status === 'success') {
        Swal.fire({
            icon: 'success',
            title: '¡Operación Exitosa!',
            text: 'Los datos se guardaron correctamente.',
            confirmButtonColor: '#00609C'
        });
    } else if (status === 'updated') {
        Swal.fire({
            icon: 'info',
            title: 'Actualizado',
            text: 'La información ha sido modificada con éxito.',
            confirmButtonColor: '#FDB913',
            confirmButtonText: 'Genial'
        });
    } else if (status === 'error') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Hubo un problema al procesar la solicitud.',
        });
    }
    
    // Limpiar la URL para que no salga la alerta al recargar
    if (status) {
        window.history.replaceState(null, null, window.location.pathname);
    }
</script>
</body>
</html>