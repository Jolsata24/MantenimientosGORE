<?php
// procesos/logout.php
session_start();
include '../conexion.php'; // Incluimos conexión para poder registrar la auditoría

// --- PASO OPCIONAL: REGISTRAR LA SALIDA EN AUDITORÍA ---
// Si el archivo logger.php existe, registramos que el usuario salió.
if (file_exists('logger.php') && isset($_SESSION['nombre_usuario'])) {
    include 'logger.php';
    // Usamos la función que creamos antes
    // Nota: Pasamos $conn, la acción 'LOGOUT' y el detalle.
    registrar_auditoria($conn, 'LOGOUT', 'Cierre de sesión de usuario: ' . $_SESSION['nombre_usuario']);
}
// -------------------------------------------------------

// 1. Vaciar el arreglo de sesión
$_SESSION = array();

// 2. Borrar la cookie de sesión del navegador (Seguridad Total)
// Esto asegura que si alguien le da "Atrás" en el navegador, no pueda entrar.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destruir la sesión en el servidor
session_destroy();

// 4. Cerrar conexión a BD (Buena práctica)
if(isset($conn)) $conn->close();

// 5. Redirigir al Login
header("Location: ../login.php");
exit;
?>