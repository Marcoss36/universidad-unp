<?php
$host = "localhost";
$user = "root";
$password = ""; // Contraseña por defecto de XAMPP es vacía
$database = "bd_unp";

$conexion = new mysqli($host, $user, $password, $database);

if ($conexion->connect_error) {
    die("Error de conexión a la base de datos: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");
?>