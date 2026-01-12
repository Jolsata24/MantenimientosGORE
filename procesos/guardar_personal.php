<?php
// procesos/guardar_personal.php
include '../conexion.php';

// 1. Recibir los datos del formulario (vienen por el método POST)
$dni = $_POST['dni'];
$nombres = $_POST['nombres'];
$apellidos = $_POST['apellidos'];
$cargo = $_POST['cargo'];
$id_area = $_POST['id_area']; // Este es el ID de la oficina (número)
$telefono = $_POST['telefono'];

// 2. Validación básica (Opcional pero recomendada)
// Verificamos si el DNI ya existe para no duplicar personas
$sql_check = "SELECT id_personal FROM personal WHERE dni = '$dni'";
$result_check = $conn->query($sql_check);

if ($result_check->num_rows > 0) {
    // Si el DNI ya existe, detenemos todo y mostramos aviso
    echo "<script>
            alert('Error: El DNI $dni ya está registrado en el sistema.');
            window.location.href = '../personal.php';
          </script>";
    exit(); // Detiene la ejecución
}

// 3. Preparar la consulta INSERT (Usamos Prepared Statements por seguridad)
// El estado por defecto será 'Activo'
$sql = "INSERT INTO personal (dni, nombres, apellidos, cargo, telefono, id_area, estado) 
        VALUES (?, ?, ?, ?, ?, ?, 'Activo')";

$stmt = $conn->prepare($sql);

// "sssssi" significa: String, String, String, String, String, Integer (el ID del area es numero)
$stmt->bind_param("sssssi", $dni, $nombres, $apellidos, $cargo, $telefono, $id_area);

// 4. Ejecutar y Redirigir
if ($stmt->execute()) {
    // Si todo salió bien, volvemos a la página de personal
    header("Location: ../personal.php?status=success");
} else {
    echo "Error al guardar: " . $conn->error;
}

$stmt->close();
$conn->close();
?>  