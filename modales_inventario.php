<div class="modal fade" id="modalAgregar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nuevo Registro</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="procesos/guardar_bien.php" method="POST">
                <div class="modal-body">
                    
                    <input type="hidden" name="id_categoria" value="<?php echo $cat_id ? $cat_id : 1; ?>">

                    <div class="row g-3">
                        <div class="col-md-12">
                            <h6 class="text-primary fw-bold border-bottom pb-1">Datos Generales</h6>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Código Patrimonial</label>
                            <input type="text" name="codigo" class="form-control" placeholder="Ej: 740899..." required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Descripción</label>
                            <input type="text" name="descripcion" class="form-control" placeholder="Ej: LAPTOP HP PROBOOK..." required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Marca</label>
                            <input type="text" name="marca" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Modelo</label>
                            <input type="text" name="modelo" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">N° Serie</label>
                            <input type="text" name="serie" class="form-control">
                        </div>

                        <div class="col-md-12 mt-4">
                            <h6 class="text-primary fw-bold border-bottom pb-1">Detalles Técnicos & Red</h6>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Procesador</label>
                            <input type="text" name="procesador" class="form-control" placeholder="Ej: Core i5">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">RAM</label>
                            <input type="text" name="ram" class="form-control" placeholder="Ej: 8 GB">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Disco</label>
                            <input type="text" name="disco" class="form-control" placeholder="Ej: 500 GB">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Sis. Operativo</label>
                            <input type="text" name="so" class="form-control" placeholder="Ej: Windows 10">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Dirección IP</label>
                            <input type="text" name="ip" class="form-control" placeholder="192.168.1.XX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Dirección MAC</label>
                            <input type="text" name="mac" class="form-control">
                        </div>

                        <div class="col-md-12 mt-4">
                            <h6 class="text-primary fw-bold border-bottom pb-1">Ubicación</h6>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Ubicación Física (Oficina)</label>
                            <input type="text" name="ubicacion" class="form-control" placeholder="Ej: OFICINA DE LOGÍSTICA">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Equipo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="procesos/actualizar_bien.php" method="POST">
                <div class="modal-body">
                    
                    <input type="hidden" name="id_bien" id="edit_id">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Código Patrimonial</label>
                            <input type="text" name="codigo" id="edit_codigo" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Estado Físico</label>
                            <select name="estado" id="edit_estado" class="form-select">
                                <option value="Bueno">Bueno</option>
                                <option value="Regular">Regular</option>
                                <option value="Malo">Malo</option>
                                <option value="Baja">Baja</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Descripción</label>
                            <input type="text" name="descripcion" id="edit_descripcion" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Marca</label>
                            <input type="text" name="marca" id="edit_marca" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Modelo</label>
                            <input type="text" name="modelo" id="edit_modelo" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Serie</label>
                            <input type="text" name="serie" id="edit_serie" class="form-control">
                        </div>

                        <div class="col-md-12 mt-3"><hr></div>
                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Ubicación Física</label>
                            <input type="text" name="ubicacion" id="edit_ubicacion" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Dirección IP</label>
                            <input type="text" name="ip" id="edit_ip" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small text-muted">Procesador</label>
                            <input type="text" name="procesador" id="edit_procesador" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">RAM</label>
                            <input type="text" name="ram" id="edit_ram" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Disco</label>
                            <input type="text" name="disco" id="edit_disco" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function cargarDatosEditar(datos) {
        // Llenamos los campos del formulario con el JSON que viene del botón
        document.getElementById('edit_id').value = datos.id_bien;
        document.getElementById('edit_codigo').value = datos.codigo_patrimonial;
        document.getElementById('edit_descripcion').value = datos.descripcion;
        document.getElementById('edit_marca').value = datos.marca;
        document.getElementById('edit_modelo').value = datos.modelo;
        document.getElementById('edit_serie').value = datos.serie;
        document.getElementById('edit_estado').value = datos.estado_fisico;
        
        // Nuevos campos (Usamos || '' para evitar errores si el campo viene vacío)
        document.getElementById('edit_ubicacion').value = datos.ubicacion || '';
        document.getElementById('edit_ip').value = datos.ip || '';
        document.getElementById('edit_procesador').value = datos.procesador || '';
        document.getElementById('edit_ram').value = datos.ram || '';
        document.getElementById('edit_disco').value = datos.disco || '';
    }
</script>