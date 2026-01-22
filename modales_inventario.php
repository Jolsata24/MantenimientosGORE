<?php
// 1. Cargar Personal para los Selects
$opt_personal = '<option value="">-- Sin Asignar (Libre) --</option>';
$q_p = $conn->query("SELECT * FROM personal WHERE estado = 'Activo' ORDER BY apellidos ASC");
if($q_p){
    while($p = $q_p->fetch_assoc()){
        $opt_personal .= '<option value="'.$p['id_personal'].'">'.$p['apellidos'].' '.$p['nombres'].' ('.$p['oficina'].')</option>';
    }
}

// 2. Cargar Categorías DINÁMICAS (Para que aparezcan Proyectores, Tablets, etc.)
$opt_categorias = '';
$q_c = $conn->query("SELECT * FROM categorias WHERE estado = 'Activo' ORDER BY nombre ASC");
if($q_c){
    while($c = $q_c->fetch_assoc()){
        $opt_categorias .= '<option value="'.$c['id_categoria'].'">'.$c['nombre'].'</option>';
    }
}
?>

<div class="modal fade" id="modalNuevaCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-folder-plus me-2"></i>Nueva Categoría</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="procesos/guardar_categoria.php" method="POST">
                <div class="modal-body">
                    <div class="alert alert-info small mb-3">
                        <i class="fas fa-info-circle me-1"></i> Esto creará una nueva tarjeta en el inventario.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre de la Categoría</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Proyectores, Vehículos, Muebles..." required>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Color de Tarjeta</label>
                            <input type="color" name="color" class="form-control form-control-color w-100" value="#6f42c1" title="Elige un color">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Ícono</label>
                            <select name="icono" class="form-select font-awesome-select">
                                <option value="fa-box">📦 Caja (Genérico)</option>
                                <option value="fa-video">🎥 Proyector/Video</option>
                                <option value="fa-tablet-alt">📱 Tablet/Móvil</option>
                                <option value="fa-server">🖥️ Servidor/Redes</option>
                                <option value="fa-chair">🪑 Mobiliario</option>
                                <option value="fa-car">🚗 Vehículo</option>
                                <option value="fa-tools">🔧 Herramienta</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAgregar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nuevo Activo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="procesos/guardar_bien.php" method="POST">
                <div class="modal-body">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Categoría</label>
                            <select name="categoria" class="form-select" required>
                                <?php echo $opt_categorias; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Cod. Patrimonial</label>
                            <input type="text" name="codigo" class="form-control" required placeholder="Ej: 74089500...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Estado Físico</label>
                            <select name="estado" class="form-select">
                                <option value="Bueno">Bueno</option>
                                <option value="Regular" selected>Regular</option>
                                <option value="Malo">Malo</option>
                                <option value="Baja">Para Baja</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Hostname / Descripción</label>
                            <input type="text" name="descripcion" class="form-control" required placeholder="Nombre del equipo">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Marca</label>
                            <input type="text" name="marca" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Modelo</label>
                            <input type="text" name="modelo" class="form-control">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">N° Serie</label>
                            <input type="text" name="serie" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ubicación Física</label>
                            <input type="text" name="ubicacion" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Custodio</label>
                            <select name="id_personal" class="form-select">
                                <?php echo $opt_personal; ?>
                            </select>
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted mb-3">Especificaciones (Opcional)</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small">Procesador</label>
                            <input type="text" name="procesador" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Memoria RAM</label>
                            <input type="text" name="ram" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Disco</label>
                            <input type="text" name="disco" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small">Dirección IP</label>
                            <input type="text" name="ip" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Dirección MAC</label>
                            <input type="text" name="mac" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Sistema Operativo</label>
                            <input type="text" name="so" class="form-control form-control-sm">
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-2"></i>Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark"><i class="fas fa-edit me-2"></i>Editar Activo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="procesos/actualizar_bien.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_bien" id="edit_id_bien">

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Categoría</label>
                            <select name="categoria" id="edit_categoria" class="form-select" required>
                                <?php echo $opt_categorias; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Cod. Patrimonial</label>
                            <input type="text" name="codigo" id="edit_codigo" class="form-control" readonly style="background-color: #e9ecef;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Estado Físico</label>
                            <select name="estado" id="edit_estado" class="form-select">
                                <option value="Bueno">Bueno</option>
                                <option value="Regular">Regular</option>
                                <option value="Malo">Malo</option>
                                <option value="Baja">Para Baja</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Hostname / Descripción</label>
                            <input type="text" name="descripcion" id="edit_descripcion" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Marca</label>
                            <input type="text" name="marca" id="edit_marca" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Modelo</label>
                            <input type="text" name="modelo" id="edit_modelo" class="form-control">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">N° Serie</label>
                            <input type="text" name="serie" id="edit_serie" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ubicación Física</label>
                            <input type="text" name="ubicacion" id="edit_ubicacion" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Custodio</label>
                            <select name="id_personal" id="edit_personal" class="form-select">
                                <?php echo $opt_personal; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
    <label class="form-label">Color / Tipo de Impresión</label>
    <select class="form-select" name="color" id="edit_color">
        <option value="">Seleccione...</option>
        <option value="Monocromático (B/N)">Monocromático (B/N)</option>
        <option value="Color">Color</option>
        <option value="Matricial">Matricial</option>
        <option value="Plotter">Plotter</option>
    </select>
</div>
                    <hr>
                    <h6 class="text-muted mb-3">Especificaciones</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small">Procesador</label>
                            <input type="text" name="procesador" id="edit_procesador" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">RAM</label>
                            <input type="text" name="ram" id="edit_ram" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Disco</label>
                            <input type="text" name="disco" id="edit_disco" class="form-control form-control-sm">
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small">Dirección IP</label>
                            <input type="text" name="ip" id="edit_ip" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Dirección MAC</label>
                            <input type="text" name="mac" id="edit_mac" class="form-control form-control-sm">
                        </div>
                         <div class="col-md-4">
                            <label class="form-label small">Sistema Operativo</label>
                            <input type="text" name="so" id="edit_so" class="form-control form-control-sm">
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark"><i class="fas fa-save me-2"></i>Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function cargarDatosEditar(json) {
        document.getElementById('edit_id_bien').value = json.id_bien;
        document.getElementById('edit_codigo').value = json.codigo_patrimonial;
        document.getElementById('edit_descripcion').value = json.descripcion;
        document.getElementById('edit_categoria').value = json.id_categoria;
        document.getElementById('edit_marca').value = json.marca;
        document.getElementById('edit_modelo').value = json.modelo;
        document.getElementById('edit_serie').value = json.serie;
        document.getElementById('edit_ubicacion').value = json.ubicacion;
        document.getElementById('edit_personal').value = json.id_personal ? json.id_personal : "";

        // Estado
        let estado = json.estado_fisico.charAt(0).toUpperCase() + json.estado_fisico.slice(1).toLowerCase();
        let selectEstado = document.getElementById('edit_estado');
        let options = Array.from(selectEstado.options).map(opt => opt.value);
        if(options.includes(estado)) selectEstado.value = estado;
        else if(estado.includes("Baja")) selectEstado.value = "Baja";
        else if(estado.includes("Bueno")) selectEstado.value = "Bueno";
        else if(estado.includes("Malo")) selectEstado.value = "Malo";
        else selectEstado.value = "Regular";

        // Specs
        document.getElementById('edit_procesador').value = json.procesador || "";
        document.getElementById('edit_ram').value = json.ram || "";
        document.getElementById('edit_disco').value = json.disco || "";
        document.getElementById('edit_ip').value = json.ip || "";
        document.getElementById('edit_mac').value = json.mac || "";
        document.getElementById('edit_so').value = json.so || "";
    }
</script>