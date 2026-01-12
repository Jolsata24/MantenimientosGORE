<?php
// personal.php
include 'conexion.php';
$page = 'personal'; // Necesitaremos agregar esto al Sidebar luego

// Consulta: Unimos Personal con Áreas para ver dónde trabajan
$sql = "SELECT p.*, a.nombre_area 
        FROM personal p 
        LEFT JOIN areas a ON p.id_area = a.id_area 
        ORDER BY p.apellidos ASC";
$resultado = $conn->query($sql);

// Consulta para el Select de Áreas en el Modal
$sql_areas = "SELECT * FROM areas";
$res_areas = $conn->query($sql_areas);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Directorio de Personal | GORE Pasco</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .main-card { border-radius: 15px; border: none; }
        .btn-gore { background-color: #8B0000; color: white; border: none; }
        .btn-gore:hover { background-color: #a00000; color: white; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark"><i class="fas fa-id-card-alt me-2 text-danger"></i>Directorio de Personal</h2>
                    <p class="text-muted">Funcionarios y servidores públicos con bienes asignados</p>
                </div>
                <button class="btn btn-gore shadow-sm py-2 px-4" data-bs-toggle="modal" data-bs-target="#modalPersonal">
                    <i class="fas fa-user-plus me-2"></i> Nuevo Funcionario
                </button>
            </div>

            <div class="card main-card shadow-lg p-3 mb-5 bg-body rounded">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaPersonal" class="table table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>DNI</th>
                                    <th>Apellidos y Nombres</th>
                                    <th>Cargo / Puesto</th>
                                    <th>Área / Oficina</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($fila = $resultado->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold text-secondary"><?php echo $fila['dni']; ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo $fila['apellidos']; ?>, <?php echo $fila['nombres']; ?></div>
                                        <small class="text-muted"><i class="fas fa-phone-alt me-1"></i> <?php echo $fila['telefono']; ?></small>
                                    </td>
                                    <td><?php echo $fila['cargo']; ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo $fila['nombre_area']; ?></span></td>
                                    <td>
                                        <?php if($fila['estado'] == 'Activo'): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary" title="Ver Bienes a su cargo">
                                            <i class="fas fa-laptop-house"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning text-dark"><i class="fas fa-pen"></i></button>
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

    <div class="modal fade" id="modalPersonal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Registrar Nuevo Funcionario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesos/guardar_personal.php" method="POST">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">DNI</label>
                                <input type="text" name="dni" class="form-control" maxlength="8" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Nombres</label>
                                <input type="text" name="nombres" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Apellidos</label>
                                <input type="text" name="apellidos" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Cargo</label>
                                <input type="text" name="cargo" class="form-control" placeholder="Ej. Asistente Administrativo">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Área / Oficina</label>
                                <select name="id_area" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    <?php while($area = $res_areas->fetch_assoc()): ?>
                                        <option value="<?php echo $area['id_area']; ?>">
                                            <?php echo $area['nombre_area']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Teléfono / Celular</label>
                                <input type="text" name="telefono" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-gore">Guardar Personal</button>
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
            $('#tablaPersonal').DataTable({ language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' } });
        });
    </script>
</body>
</html>