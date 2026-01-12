<?php
// inventario.php
include 'conexion.php';

$page = 'inventario'; // Variable para iluminar el menú lateral

$sql = "SELECT b.*, c.nombre as nombre_categoria 
        FROM bienes b 
        LEFT JOIN categorias c ON b.id_categoria = c.id_categoria
        ORDER BY b.id_bien DESC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario | GORE Pasco</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .main-card { border-radius: 15px; border: none; }
        .btn-gore { background-color: #8B0000; color: white; border: none; }
        .btn-gore:hover { background-color: #a00000; color: white; }
        .status-badge { font-size: 0.85rem; padding: 5px 10px; border-radius: 20px; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark">Gestión de Inventario</h2>
                    <p class="text-muted">Lista maestra de activos fijos</p>
                </div>
                <button class="btn btn-gore shadow-sm py-2 px-4" data-bs-toggle="modal" data-bs-target="#modalNuevo">
                    <i class="fas fa-plus-circle me-2"></i> Nuevo Activo
                </button>
            </div>

            <div class="card main-card shadow-lg p-3 mb-5 bg-body rounded">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaInventario" class="table table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th>Marca/Modelo</th>
                                    <th>Categoría</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($fila = $resultado->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold text-primary">
                                        <i class="fas fa-qrcode text-dark me-1"></i> <?php echo $fila['codigo_patrimonial']; ?>
                                    </td>
                                    <td><?php echo $fila['descripcion']; ?></td>
                                    <td>
                                        <?php if($fila['marca']): ?> <small class="d-block text-muted"><strong>M:</strong> <?php echo $fila['marca']; ?></small> <?php endif; ?>
                                        <?php if($fila['modelo']): ?> <small class="d-block text-muted"><strong>Mod:</strong> <?php echo $fila['modelo']; ?></small> <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo $fila['nombre_categoria']; ?></span></td>
                                    <td>
                                        <?php 
                                        $clase = 'bg-success';
                                        if($fila['estado_fisico'] == 'Regular') $clase = 'bg-warning text-dark';
                                        if($fila['estado_fisico'] == 'Malo') $clase = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $clase; ?> status-badge"><?php echo $fila['estado_fisico']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="ver_activo.php?id=<?php echo $fila['id_bien']; ?>" class="btn btn-sm btn-outline-primary" title="Ver"><i class="fas fa-eye"></i></a>
                                            <button class="btn btn-sm btn-outline-warning text-dark"><i class="fas fa-pen"></i></button>
                                        </div>
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

    <div class="modal fade" id="modalNuevo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Registrar Nuevo Activo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesos/guardar_bien.php" method="POST">
                    <div class="modal-body">
                        <div class="alert alert-info">Asegúrate de copiar los campos del formulario anterior aquí.</div>
                         </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-gore">Guardar</button>
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
            $('#tablaInventario').DataTable({ language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' } });
        });
    </script>
</body>
</html>