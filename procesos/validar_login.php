<?php
// procesos/validar_login.php
session_start();
include '../conexion.php';

// 1. Recibir datos del formulario
$usuario_form = trim($_POST['usuario']);
$password_form = $_POST['password'];

// 2. Buscar usuario en la BD (Usamos sentencias preparadas por seguridad)
$sql = "SELECT * FROM usuarios_sistema WHERE nombre_usuario = ? AND estado = 'Activo'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario_form);
$stmt->execute();
$resultado = $stmt->get_result();

if ($row = $resultado->fetch_assoc()) {
    // 3. Verificar Contraseña (password_verify compara el texto con el Hash)
    // Nota: Asegúrate de que tu columna en BD se llame 'password_hash' como vimos antes
    if (password_verify($password_form, $row['password_hash'])) {
        
        // --- LOGIN CORRECTO ---

        // A. Guardar variables de sesión
        $_SESSION['logeado'] = true;
        $_SESSION['id_usuario'] = $row['id_usuario'];
        $_SESSION['nombre_usuario'] = $row['nombre_usuario'];
        $_SESSION['rol'] = $row['rol']; // Importante para que funcione la auditoría y el sidebar

        // B. --- REGISTRO DE AUDITORÍA ---
        if (file_exists('logger.php')) {
            include 'logger.php';
            // Registramos quién entró
            registrar_auditoria($conn, 'LOGIN', "Inicio de sesión exitoso");
        }
        // --------------------------------

        // C. Redirigir al Inventario
        header("Location: ../inventario.php");
        exit();

    } else {
        // Contraseña incorrecta
        header("Location: ../login.php?error=1");
        exit();
    }
} else {
    // Usuario no existe o está inactivo
    header("Location: ../login.php?error=1");
    exit();
}

$stmt->close();
$conn->close();
?>