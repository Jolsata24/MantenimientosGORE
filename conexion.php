<?php
// conexion.php
$servidor = "localhost";
$usuario = "root";       // Usuario por defecto de XAMPP
$password = "123456";          // Contraseña por defecto de XAMPP (vacía)
$base_datos = "gore_patrimonio";

// Crear la conexión
$conn = new mysqli($servidor, $usuario, $password, $base_datos);

// Verificar si hubo error
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Configurar caracteres para que acepte tildes y ñ
$conn->set_charset("utf8");

// (Opcional) Mensaje de prueba, descoméntalo solo si quieres verificar que conecta
// echo "Conexión exitosa";
?>