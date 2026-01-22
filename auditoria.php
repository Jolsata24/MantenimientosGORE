<?php
// auditoria.php
include 'conexion.php';
session_start();

// 1. SEGURIDAD: Solo rol 'sistemas' puede entrar
// Asegúrate de que al hacer LOGIN guardes $_SESSION['rol']
if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: login.php"); exit;
}
if (!isset($_SESSION['rol']) || strtolower($_SESSION['rol']) != 'sistemas') {
    // Si no es sistemas, lo botamos al inicio
    header("Location: inventario.php"); exit;
}

$page = 'auditoria';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Auditoría | GORE Pasco</title>
    <link rel="icon" type="image/png" href="img/logo_gore.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            
            <div class="page-header mb-4">
                <h2 class="titulo-seccion">
                    <i class="fas fa-shield-alt text-danger me-2"></i> Auditoría del Sistema
                </h2>
                <small class="text-muted">Registro de seguridad y trazabilidad de acciones.</small>
            </div>

            <div class="card card-tabla border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaAuditoria" class="table table-hover align-middle w-100">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Fecha / Hora</th>
                                    <th>Usuario</th>
                                    <th>Acción</th>
                                    <th>Detalle</th>
                                    <th>IP Origen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM auditoria ORDER BY id_log DESC";
                                $res = $conn->query($sql);
                                while($row = $res->fetch_assoc()):
                                    // Colores según acción
                                    $badge = "bg-secondary";
                                    if($row['accion'] == 'CREAR') $badge = "bg-success";
                                    if($row['accion'] == 'EDITAR') $badge = "bg-warning text-dark";
                                    if($row['accion'] == 'ELIMINAR') $badge = "bg-danger";
                                    if($row['accion'] == 'MANTENIMIENTO') $badge = "bg-info text-dark";
                                ?>
                                <tr>
                                    <td><?php echo $row['id_log']; ?></td>
                                    <td style="font-size:0.85rem; font-weight:bold;">
                                        <?php echo date('d/m/Y H:i', strtotime($row['fecha'])); ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-dark text-white d-flex justify-content-center align-items-center me-2" style="width:25px; height:25px; font-size:0.7rem;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <span class="d-block fw-bold small"><?php echo $row['usuario']; ?></span>
                                                <span class="d-block text-muted" style="font-size:0.7rem;"><?php echo $row['rol']; ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge <?php echo $badge; ?>"><?php echo $row['accion']; ?></span></td>
                                    <td class="small text-muted"><?php echo $row['detalle']; ?></td>
                                    <td class="font-monospace small"><?php echo $row['ip']; ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tablaAuditoria').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
                order: [[ 0, "desc" ]] // Ordenar por ID descendente (lo más nuevo primero)
            });
        });
    </script>
</body>
</html>