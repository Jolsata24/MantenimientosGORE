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
// --- 1. CONSULTA PRINCIPAL DE BIENES (Agregamos p.id_area para saber la oficina del custodio actual) ---
$sql = "SELECT b.*, c.nombre as nombre_categoria, p.nombres, p.apellidos, p.id_area as id_area_personal
        FROM bienes b 
        LEFT JOIN categorias c ON b.id_categoria = c.id_categoria
        LEFT JOIN personal p ON b.id_personal = p.id_personal
        ORDER BY b.id_bien DESC";
$resultado = $conn->query($sql);

// --- 2. CONSULTAS PARA LOS SELECTORES (Áreas y Personal) ---
// A. Traer todas las áreas para el primer combo
$sql_areas = "SELECT * FROM areas ORDER BY nombre_area ASC";
$res_areas = $conn->query($sql_areas);

// B. Traer todo el personal activo CON SU ID_AREA para filtrar con JS
$sql_personal_js = "SELECT id_personal, nombres, apellidos, id_area FROM personal WHERE estado = 'Activo' ORDER BY apellidos ASC";
$res_p_js = $conn->query($sql_personal_js);

$lista_personal = [];
while ($p = $res_p_js->fetch_assoc()) {
    $lista_personal[] = $p; // Guardamos en un array para pasarlo a Javascript
}

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
    <link rel="icon" type="image/png" href="img/logo_gore.png">
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
                                <th>Ubicación</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = $resultado->fetch_assoc()): ?>
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
                                            <?php if ($fila['marca']) echo "M: " . $fila['marca']; ?>
                                            <?php if ($fila['modelo']) echo "/ Mod: " . $fila['modelo']; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($fila['nombres']): ?>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-light border d-flex justify-content-center align-items-center me-2 text-primary fw-bold" style="width:30px; height:30px; font-size:0.8rem;">
                                                    <?php echo substr($fila['nombres'], 0, 1) . substr($fila['apellidos'], 0, 1); ?>
                                                </div>
                                                <div class="lh-1">
                                                    <div class="fw-bold small"><?php echo $fila['apellidos'] . ', ' . $fila['nombres']; ?></div>
                                                    <small class="text-muted" style="font-size:0.7rem;">Asignado</small>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-secondary opacity-50 fw-normal"><i class="fas fa-user-slash me-1"></i> Sin Asignar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo $fila['nombre_categoria']; ?></span></td>
                                    <td>
                                        <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i> <?php echo $fila['ubicacion']; ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $clase_estado = 'estado-bueno';
                                        $icono = 'fa-check-circle';
                                        if ($fila['estado_fisico'] == 'Regular') {
                                            $clase_estado = 'estado-regular';
                                            $icono = 'fa-exclamation-circle';
                                        }
                                        if ($fila['estado_fisico'] == 'Malo' || $fila['estado_fisico'] == 'Baja') {
                                            $clase_estado = 'estado-malo';
                                            $icono = 'fa-times-circle';
                                        }
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
    '<?php echo $fila['id_personal']; ?>',
    '<?php echo $fila['id_area_personal']; ?>' /* <--- NUEVO PARAMETRO */
)"
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
                            <div class="col-12 bg-light p-2 rounded border-top border-start border-end">
                                <label class="form-label fw-bold text-primary small">1. FILTRAR POR OFICINA</label>
                                <select id="filtro_area_nuevo" class="form-select form-select-sm border-primary" onchange="filtrarPersonal('filtro_area_nuevo', 'select_personal_nuevo')">
                                    <option value="">-- Seleccione una Oficina --</option>
                                    <?php
                                    $res_areas->data_seek(0); // Reiniciar puntero
                                    while ($a = $res_areas->fetch_assoc()): ?>
                                        <option value="<?php echo $a['id_area']; ?>"><?php echo $a['nombre_area']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-12 bg-light p-2 rounded border-bottom border-start border-end mb-2">
                                <label class="form-label fw-bold text-primary small">2. ASIGNAR A CUSTODIO</label>
                                <select name="id_personal" id="select_personal_nuevo" class="form-select border-primary">
                                    <option value="">-- Sin Asignar (En Almacén) --</option>
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
                            <div class="col-12 bg-light p-2 rounded border-top border-start border-end">
                                <label class="form-label fw-bold text-primary small">1. CAMBIAR OFICINA</label>
                                <select id="filtro_area_editar" class="form-select form-select-sm border-primary" onchange="filtrarPersonal('filtro_area_editar', 'select_personal_editar')">
                                    <option value="">-- Seleccione una Oficina --</option>
                                    <?php
                                    $res_areas->data_seek(0);
                                    while ($a = $res_areas->fetch_assoc()): ?>
                                        <option value="<?php echo $a['id_area']; ?>"><?php echo $a['nombre_area']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-12 bg-light p-2 rounded border-bottom border-start border-end mb-2">
                                <label class="form-label fw-bold text-primary small">2. CUSTODIO RESPONSABLE</label>
                                <select name="id_personal" id="select_personal_editar" class="form-select border-primary">
                                    <option value="">-- Sin Asignar (En Almacén) --</option>
                                </select>
                            </div>
                            <div class="col-md-6"><label class="form-label fw-bold small text-muted">CÓDIGO</label><input type="text" name="codigo" id="edit_codigo" class="form-control" readonly style="background:#e9ecef;"></div>
                            <div class="col-md-6"><label class="form-label fw-bold small text-muted">CATEGORÍA</label><select name="categoria" id="edit_categoria" class="form-select">
                                    <option value="1">Computadoras</option>
                                    <option value="2">Mobiliario</option>
                                    <option value="3">Vehículos</option>
                                    <option value="4">Equipos Varios</option>
                                </select></div>
                            <div class="col-12"><label class="form-label fw-bold small text-muted">DESCRIPCIÓN</label><input type="text" name="descripcion" id="edit_descripcion" class="form-control" required></div>
                            <div class="col-md-4"><label class="form-label fw-bold small text-muted">MARCA</label><input type="text" name="marca" id="edit_marca" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label fw-bold small text-muted">MODELO</label><input type="text" name="modelo" id="edit_modelo" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label fw-bold small text-muted">N° SERIE</label><input type="text" name="serie" id="edit_serie" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label fw-bold small text-muted">ESTADO</label><select name="estado" id="edit_estado" class="form-select">
                                    <option value="Nuevo">Nuevo</option>
                                    <option value="Bueno">Bueno</option>
                                    <option value="Regular">Regular</option>
                                    <option value="Malo">Malo</option>
                                    <option value="Baja">De Baja</option>
                                </select></div>
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
        // 1. Convertimos la lista de PHP a un objeto JSON de Javascript
        const todosLosPersonales = <?php echo json_encode($lista_personal); ?>;

        $(document).ready(function() {
            $('#tablaInventario').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                lengthChange: false,
                pageLength: 8
            });

            // Cargar personal inicial en el modal Nuevo (Sin filtrar, o vacío)
            filtrarPersonal('filtro_area_nuevo', 'select_personal_nuevo');
        });

        // --- FUNCIÓN PARA FILTRAR PERSONAL ---
        function filtrarPersonal(idSelectArea, idSelectPersonal, idPersonalSeleccionado = null) {
            const idArea = document.getElementById(idSelectArea).value;
            const selectPersonal = document.getElementById(idSelectPersonal);

            // Limpiar opciones actuales
            selectPersonal.innerHTML = '<option value="">-- Sin Asignar (En Almacén) --</option>';

            // Filtrar y llenar
            todosLosPersonales.forEach(persona => {
                // Si no hay área seleccionada, mostramos TODOS (o ninguno si prefieres)
                // Aquí mostramos solo si coincide el área. Si idArea está vacío, no muestra nada (limpio).
                if (idArea && persona.id_area == idArea) {
                    const option = document.createElement('option');
                    option.value = persona.id_personal;
                    option.text = persona.apellidos + ', ' + persona.nombres;
                    selectPersonal.add(option);
                }
            });

            // Si estamos editando, volver a seleccionar al custodio original
            if (idPersonalSeleccionado) {
                selectPersonal.value = idPersonalSeleccionado;
            }
        }

        // --- CARGAR DATOS EN MODAL EDITAR ---
        function cargarDatosEditar(id, codigo, categoria, descripcion, marca, modelo, serie, estado, id_personal, id_area_personal) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_codigo').value = codigo;
            document.getElementById('edit_categoria').value = categoria;
            document.getElementById('edit_descripcion').value = descripcion;
            document.getElementById('edit_marca').value = marca;
            document.getElementById('edit_modelo').value = modelo;
            document.getElementById('edit_serie').value = serie;
            document.getElementById('edit_estado').value = estado;

            // LOGICA DE COMBOS ANIDADOS
            // 1. Seteamos el área en el primer combo
            const selectArea = document.getElementById('filtro_area_editar');
            selectArea.value = id_area_personal || ""; // Si es null, pone vacío

            // 2. Disparamos el filtro manualmente pasándole el personal que debe quedar seleccionado
            filtrarPersonal('filtro_area_editar', 'select_personal_editar', id_personal);
        }

        // --- LOGICA QR ---
        function cargarQR(ruta, codigo) {
            document.getElementById('imgQR').src = ruta;
            document.getElementById('tituloQR').innerText = codigo;
        }

        // --- CORRECCIÓN DE IMPRESIÓN QR ---
        function imprimirQR() {
            var img = document.getElementById('imgQR').src;
            var titulo = document.getElementById('tituloQR').innerText;

            var win = window.open('', 'Imprimir', 'height=500,width=500');

            win.document.write('<html><head><title>Imprimir QR</title>');
            win.document.write('<style>body{text-align:center; font-family:sans-serif; padding-top:20px;} img{max-width:80%;} .code{font-size:20px; font-weight:bold; margin-top:10px;}</style>');
            win.document.write('</head><body>');
            // Usamos ONLOAD en la imagen para asegurar que cargue antes de imprimir
            win.document.write('<img src="' + img + '" onload="window.print();">');
            win.document.write('<div class="code">' + titulo + '</div>');
            win.document.write('</body></html>');
            win.document.close();
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