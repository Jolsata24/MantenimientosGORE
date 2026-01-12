<?php
// inventario.php
include 'conexion.php';
session_start();

if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: login.php");
    exit();
}

$page = 'inventario';

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
    
    <link rel="stylesheet" href="css/inventario.css">
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            
            <div class="page-header">
                <h2 class="titulo-seccion">
                    <i class="fas fa-boxes text-secondary me-2 opacity-50"></i> Gestión de Inventario
                </h2>
                <button class="btn-nuevo" data-bs-toggle="modal" data-bs-target="#modalNuevo">
                    <i class="fas fa-plus-circle me-2"></i> Nuevo Activo
                </button>
            </div>

            <div class="card card-tabla bg-white p-3">
                <div class="table-responsive">
                    <table id="tablaInventario" class="table align-middle w-100">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th>Detalles Técnicos</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($fila = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold" style="color: var(--gore-azul);">
                                    <i class="fas fa-barcode me-1 text-muted"></i> <?php echo $fila['codigo_patrimonial']; ?>
                                </td>
                                <td><?php echo $fila['descripcion']; ?></td>
                                <td>
                                    <?php if($fila['marca']): ?> 
                                        <div class="small"><span class="fw-bold">Marca:</span> <?php echo $fila['marca']; ?></div>
                                    <?php endif; ?>
                                    <?php if($fila['modelo']): ?> 
                                        <div class="small"><span class="fw-bold">Modelo:</span> <?php echo $fila['modelo']; ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?php echo $fila['nombre_categoria']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    // Lógica para las clases de color
                                    $clase_estado = 'estado-bueno'; // Default (Verde)
                                    $icono = 'fa-check-circle';
                                    
                                    if($fila['estado_fisico'] == 'Regular') {
                                        $clase_estado = 'estado-regular'; // Amarillo
                                        $icono = 'fa-exclamation-circle';
                                    }
                                    if($fila['estado_fisico'] == 'Malo' || $fila['estado_fisico'] == 'Baja') {
                                        $clase_estado = 'estado-malo'; // Rojo
                                        $icono = 'fa-times-circle';
                                    }
                                    ?>
                                    <span class="badge-estado <?php echo $clase_estado; ?>">
                                        <i class="fas <?php echo $icono; ?> me-1"></i> <?php echo $fila['estado_fisico']; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="ver_activo.php?id=<?php echo $fila['id_bien']; ?>" class="btn-action btn-ver" title="Ver Ficha">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn-action btn-editar" title="Editar">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button class="btn-action btn-qr" title="Imprimir QR">
                                        <i class="fas fa-qrcode"></i>
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

    <div class="modal fade" id="modalNuevo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header modal-header-gore">
                    <h5 class="modal-title"><i class="fas fa-box-open me-2"></i>Registrar Nuevo Activo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesos/guardar_bien.php" method="POST">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">CÓDIGO PATRIMONIAL</label>
                                <input type="text" name="codigo" class="form-control" placeholder="Ej. GORE-PC-2026-001" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">CATEGORÍA</label>
                                <select name="categoria" class="form-select" required>
                                    <option value="1">Computadoras</option>
                                    <option value="2">Mobiliario</option>
                                    <option value="3">Vehículos</option>
                                    <option value="4">Equipos Varios</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">DESCRIPCIÓN DEL BIEN</label>
                                <input type="text" name="descripcion" class="form-control" placeholder="Ej. Laptop HP Core i7 16GB RAM..." required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">MARCA</label>
                                <input type="text" name="marca" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">MODELO</label>
                                <input type="text" name="modelo" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">N° SERIE</label>
                                <input type="text" name="serie" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">ESTADO INICIAL</label>
                                <select name="estado" class="form-select">
                                    <option value="Nuevo">Nuevo</option>
                                    <option value="Bueno" selected>Bueno</option>
                                    <option value="Regular">Regular</option>
                                    <option value="Malo">Malo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-nuevo">
                            <i class="fas fa-save me-2"></i> Guardar y Generar QR
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
            $('#tablaInventario').DataTable({ 
                language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
                // Opciones para quitar elementos si quieres una tabla más limpia
                lengthChange: false, 
                pageLength: 8
            });
        });
    </script>
</body>
</html>