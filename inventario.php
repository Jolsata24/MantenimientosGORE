<?php
// inventario.php
include 'conexion.php';
session_start();

if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: login.php");
    exit();
}

$page = 'inventario';

// 1. CONSULTA PRINCIPAL
$sql = "SELECT b.*, c.nombre as nombre_categoria, p.nombres, p.apellidos 
        FROM bienes b 
        LEFT JOIN categorias c ON b.id_categoria = c.id_categoria
        LEFT JOIN personal p ON b.id_personal = p.id_personal
        ORDER BY b.id_bien DESC";
$resultado = $conn->query($sql);

// 2. Consulta para selectores de personal (Se usa en Nuevo y Editar)
$sql_personal = "SELECT id_personal, nombres, apellidos FROM personal WHERE estado = 'Activo' ORDER BY apellidos ASC";
$res_personal = $conn->query($sql_personal);
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
                                <th>Custodio / Responsable</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($fila = $resultado->fetch_assoc()): ?>
                                <?php 
                                    $nombre_archivo_qr = preg_replace('/[^A-Za-z0-9\-]/', '', $fila['codigo_patrimonial']) . '.png';
                                    $ruta_qr = "img/qr/" . $nombre_archivo_qr;
                                ?>
                            <tr>
                                <td class="fw-bold" style="color: var(--gore-azul);">
                                    <i class="fas fa-barcode me-1 text-muted"></i> <?php echo $fila['codigo_patrimonial']; ?>
                                </td>
                                <td>
                                    <?php echo $fila['descripcion']; ?>
                                    <div class="small text-muted mt-1">
                                        <?php if($fila['marca']) echo "M: ".$fila['marca']; ?> 
                                        <?php if($fila['modelo']) echo "/ Mod: ".$fila['modelo']; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if($fila['nombres']): ?>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-light border d-flex justify-content-center align-items-center me-2 text-primary fw-bold" style="width:30px; height:30px; font-size:0.8rem;">
                                                <?php echo substr($fila['nombres'],0,1).substr($fila['apellidos'],0,1); ?>
                                            </div>
                                            <div class="lh-1">
                                                <div class="fw-bold small"><?php echo $fila['apellidos'].', '.$fila['nombres']; ?></div>
                                                <small class="text-muted" style="font-size:0.7rem;">Asignado</small>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-secondary opacity-50 fw-normal"><i class="fas fa-user-slash me-1"></i> Sin Asignar</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?php echo $fila['nombre_categoria']; ?></span></td>
                                <td>
                                    <?php 
                                    $clase_estado = 'estado-bueno'; $icono = 'fa-check-circle';
                                    if($fila['estado_fisico'] == 'Regular') { $clase_estado = 'estado-regular'; $icono = 'fa-exclamation-circle'; }
                                    if($fila['estado_fisico'] == 'Malo' || $fila['estado_fisico'] == 'Baja') { $clase_estado = 'estado-malo'; $icono = 'fa-times-circle'; }
                                    ?>
                                    <span class="badge-estado <?php echo $clase_estado; ?>"><i class="fas <?php echo $icono; ?> me-1"></i> <?php echo $fila['estado_fisico']; ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="ver_activo.php?id=<?php echo $fila['id_bien']; ?>" class="btn-action btn-ver" title="Ver Ficha"><i class="fas fa-eye"></i></a>
                                        <button class="btn-action btn-editar" title="Editar / Asignar" 
                                            data-bs-toggle="modal" data-bs-target="#modalEditar"
                                            onclick="cargarDatosEditar(
                                                '<?php echo $fila['id_bien']; ?>', '<?php echo $fila['codigo_patrimonial']; ?>',
                                                '<?php echo $fila['id_categoria']; ?>', '<?php echo $fila['descripcion']; ?>',
                                                '<?php echo $fila['marca']; ?>', '<?php echo $fila['modelo']; ?>',
                                                '<?php echo $fila['serie']; ?>', '<?php echo $fila['estado_fisico']; ?>',
                                                '<?php echo $fila['id_personal']; ?>' 
                                            )"><i class="fas fa-pen"></i></button>
                                        <button class="btn-action btn-qr" title="Ver QR" 
                                                data-bs-toggle="modal" data-bs-target="#modalVerQR"
                                                onclick="cargarQR('<?php echo $ruta_qr; ?>', '<?php echo $fila['codigo_patrimonial']; ?>')">
                                            <i class="fas fa-qrcode"></i></button>
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
                            <div class="col-12 bg-light p-3 rounded border mb-2">
                                <label class="form-label fw-bold text-primary small">ASIGNAR A CUSTODIO (Opcional)</label>
                                <select name="id_personal" class="form-select border-primary">
                                    <option value="">-- Sin Asignar (En Almacén) --</option>
                                    <?php 
                                    // Usamos el resultado de la consulta de arriba
                                    $res_personal->data_seek(0); // Reiniciamos puntero
                                    while($p = $res_personal->fetch_assoc()): ?>
                                        <option value="<?php echo $p['id_personal']; ?>">
                                            <?php echo $p['apellidos'] . ', ' . $p['nombres']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

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
                                <label class="form-label fw-bold small text-muted">DESCRIPCIÓN</label>
                                <input type="text" name="descripcion" class="form-control" placeholder="Ej. Laptop HP Core i7..." required>
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
                        <button type="submit" class="btn btn-nuevo"><i class="fas fa-save me-2"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark"><i class="fas fa-edit me-2"></i>Editar / Asignar Activo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesos/actualizar_bien.php" method="POST">
                    <input type="hidden" name="id_bien" id="edit_id">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12 bg-light p-3 rounded border">
                                <label class="form-label fw-bold text-primary">CUSTODIO / RESPONSABLE</label>
                                <select name="id_personal" id="edit_personal" class="form-select border-primary">
                                    <option value="">-- Sin Asignar (En Almacén) --</option>
                                    <?php 
                                    $res_personal->data_seek(0); // Reiniciamos puntero OTRA VEZ para este segundo modal
                                    while($p = $res_personal->fetch_assoc()): ?>
                                        <option value="<?php echo $p['id_personal']; ?>"><?php echo $p['apellidos'] . ', ' . $p['nombres']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6"><label class="form-label fw-bold small text-muted">CÓDIGO</label><input type="text" name="codigo" id="edit_codigo" class="form-control" readonly style="background:#e9ecef;"></div>
                            <div class="col-md-6"><label class="form-label fw-bold small text-muted">CATEGORÍA</label><select name="categoria" id="edit_categoria" class="form-select"><option value="1">Computadoras</option><option value="2">Mobiliario</option><option value="3">Vehículos</option><option value="4">Equipos Varios</option></select></div>
                            <div class="col-12"><label class="form-label fw-bold small text-muted">DESCRIPCIÓN</label><input type="text" name="descripcion" id="edit_descripcion" class="form-control" required></div>
                            <div class="col-md-4"><label class="form-label fw-bold small text-muted">MARCA</label><input type="text" name="marca" id="edit_marca" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label fw-bold small text-muted">MODELO</label><input type="text" name="modelo" id="edit_modelo" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label fw-bold small text-muted">N° SERIE</label><input type="text" name="serie" id="edit_serie" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label fw-bold small text-muted">ESTADO</label><select name="estado" id="edit_estado" class="form-select"><option value="Nuevo">Nuevo</option><option value="Bueno">Bueno</option><option value="Regular">Regular</option><option value="Malo">Malo</option><option value="Baja">De Baja</option></select></div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning text-dark fw-bold">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalVerQR" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-body p-4">
                    <img id="imgQR" src="" class="img-fluid mb-2">
                    <h6 id="tituloQR" class="fw-bold"></h6>
                </div>
                <div class="modal-footer justify-content-center bg-light border-top-0">
                    <button type="button" class="btn btn-outline-dark btn-sm" onclick="imprimirQR()"><i class="fas fa-print me-1"></i> Imprimir</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () { $('#tablaInventario').DataTable({ language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' }, lengthChange: false, pageLength: 8 }); });
        
        function cargarDatosEditar(id, codigo, categoria, descripcion, marca, modelo, serie, estado, id_personal) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_codigo').value = codigo;
            document.getElementById('edit_categoria').value = categoria;
            document.getElementById('edit_descripcion').value = descripcion;
            document.getElementById('edit_marca').value = marca;
            document.getElementById('edit_modelo').value = modelo;
            document.getElementById('edit_serie').value = serie;
            document.getElementById('edit_estado').value = estado;
            document.getElementById('edit_personal').value = id_personal;
        }

        function cargarQR(ruta, codigo) {
            document.getElementById('imgQR').src = ruta;
            document.getElementById('tituloQR').innerText = codigo;
        }
        
        function imprimirQR() {
            var img = document.getElementById('imgQR').src;
            var win = window.open('', 'Imprimir', 'height=400,width=400');
            win.document.write('<html><head><title>Etiqueta QR</title></head><body style="text-align:center;"><img src="' + img + '" style="width:100%;"><script>window.print();window.close();<\/script></body></html>');
            win.document.close();
        }
    </script>
</body>
</html>