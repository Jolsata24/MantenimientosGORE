<?php
// personal.php
include 'conexion.php';
session_start();

// Seguridad de sesión
if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: login.php");
    exit();
}

$page = 'personal';

// Consulta: Unimos Personal con Áreas
$sql = "SELECT p.*, a.nombre_area 
        FROM personal p 
        LEFT JOIN areas a ON p.id_area = a.id_area 
        ORDER BY p.apellidos ASC";
$resultado = $conn->query($sql);

// Para el Select del Modal
$sql_areas = "SELECT * FROM areas";
$res_areas = $conn->query($sql_areas);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Directorio Personal | GORE Pasco</title>
    
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
                    <small class="text-muted">Gestión de funcionarios y servidores públicos</small>
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
                                    // Generar iniciales (Ej: Juan Perez -> JP)
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
                                            <small class="text-muted" style="font-size:0.75rem;">ID: <?php echo $fila['id_personal']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary"><i class="far fa-id-card me-1"></i> <?php echo $fila['dni']; ?></div>
                                    <?php if($fila['telefono']): ?>
                                        <small class="text-muted"><i class="fas fa-phone-alt me-1"></i> <?php echo $fila['telefono']; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $fila['cargo']; ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?php echo $fila['nombre_area']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($fila['estado'] == 'Activo'): ?>
                                        <span class="badge-estado estado-activo"><i class="fas fa-check me-1"></i> Activo</span>
                                    <?php else: ?>
                                        <span class="badge-estado estado-inactivo"><i class="fas fa-ban me-1"></i> Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn-action btn-bienes" title="Ver Bienes Asignados">
                                        <i class="fas fa-laptop-house"></i>
                                    </button>
                                    <button class="btn-action btn-editar" title="Editar Datos">
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
                    <h5 class="modal-title"><i class="fas fa-user-tie me-2"></i>Registrar Nuevo Funcionario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesos/guardar_personal.php" method="POST">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            
                            <div class="col-12"><h6 class="text-primary border-bottom pb-2">Datos Personales</h6></div>
                            
                            <div class="col-md-4 position-relative">
                                <label class="form-label fw-bold small text-muted">DNI</label>
                                <input type="text" name="dni" class="form-control" maxlength="8" required placeholder="8 dígitos">
                                <i class="far fa-id-card input-icon"></i>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">NOMBRES</label>
                                <input type="text" name="nombres" class="form-control" required style="text-transform: uppercase;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">APELLIDOS</label>
                                <input type="text" name="apellidos" class="form-control" required style="text-transform: uppercase;">
                            </div>

                            <div class="col-12 mt-4"><h6 class="text-primary border-bottom pb-2">Datos Laborales</h6></div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">CARGO / PUESTO</label>
                                <input type="text" name="cargo" class="form-control" placeholder="Ej. ASISTENTE ADMINISTRATIVO">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">OFICINA / ÁREA</label>
                                <select name="id_area" class="form-select" required>
                                    <option value="">Seleccione ubicación...</option>
                                    <?php while($area = $res_areas->fetch_assoc()): ?>
                                        <option value="<?php echo $area['id_area']; ?>">
                                            <?php echo $area['nombre_area']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">TELÉFONO / CELULAR</label>
                                <input type="text" name="telefono" class="form-control" placeholder="999 999 999">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-nuevo">
                            <i class="fas fa-save me-2"></i> Guardar Personal
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
            $('#tablaPersonal').DataTable({ 
                language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
                lengthChange: false,
                pageLength: 8
            });
        });
    </script>
</body>
</html>