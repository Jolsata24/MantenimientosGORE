<?php
// auditoria.php
include 'conexion.php';
session_start();

// 1. SEGURIDAD: Solo rol 'sistemas' puede entrar
if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: login.php"); exit;
}
if (!isset($_SESSION['rol']) || strtolower($_SESSION['rol']) != 'sistemas') {
    header("Location: inventario.php"); exit;
}

$page = 'auditoria';

// Obtener versión de MySQL para mostrar en info
$sql_ver = "SELECT VERSION() as ver";
$res_ver = $conn->query($sql_ver);
$mysql_version = $res_ver->fetch_assoc()['ver'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Auditoría y Sistema | GORE Pasco</title>
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
                    <i class="fas fa-server text-danger me-2"></i> Panel de Sistemas
                </h2>
                <small class="text-muted">Gestión técnica, copias de seguridad y logs de auditoría.</small>
            </div>

            <div class="row mb-4">
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white fw-bold">
                            <i class="fas fa-microchip me-2 text-primary"></i> Información del Servidor
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded border text-center">
                                        <small class="d-block text-muted text-uppercase mb-1" style="font-size:0.7rem;">Versión PHP</small>
                                        <span class="fw-bold text-dark"><?php echo phpversion(); ?></span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded border text-center">
                                        <small class="d-block text-muted text-uppercase mb-1" style="font-size:0.7rem;">Versión MySQL</small>
                                        <span class="fw-bold text-dark"><?php echo $mysql_version; ?></span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded border text-center">
                                        <small class="d-block text-muted text-uppercase mb-1" style="font-size:0.7rem;">Base de Datos</small>
                                        <span class="fw-bold text-primary"><?php echo $base_datos; ?></span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded border text-center">
                                        <small class="d-block text-muted text-uppercase mb-1" style="font-size:0.7rem;">IP Servidor</small>
                                        <span class="fw-bold text-dark"><?php echo $_SERVER['SERVER_ADDR'] ?? 'Localhost'; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card border-0 shadow-sm h-100 bg-primary text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                            <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center mb-3" style="width:60px; height:60px; font-size:1.5rem;">
                                <i class="fas fa-database"></i>
                            </div>
                            <h5 class="card-title fw-bold">Copia de Seguridad</h5>
                            <p class="small text-white-50 mb-4">Genera un archivo SQL completo de la base de datos actual para resguardar la información.</p>
                            <a href="procesos/backup.php" class="btn btn-light text-primary fw-bold w-100 rounded-pill">
                                <i class="fas fa-download me-2"></i> Descargar Backup (.sql)
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <h5 class="mb-3 text-muted"><i class="fas fa-history me-2"></i>Registro de Actividades (Logs)</h5>

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
                                    if($row['accion'] == 'LOGIN') $badge = "bg-primary";
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
                order: [[ 0, "desc" ]] 
            });
        });
    </script>
</body>
</html>