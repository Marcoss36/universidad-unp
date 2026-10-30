<?php
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $carrera = $_POST['carrera'] ?? '';
    $folio = 'UNP-' . rand(1000, 9999);
    $curp = 'PENDIENTE'; // O el campo CURP si lo agregas al formulario modal

    $stmt = $conexion->prepare("INSERT INTO aspirantes (folio, nombre, correo, curp, carrera) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $folio, $nombre, $correo, $curp, $carrera);
    
    if ($stmt->execute()) {
        echo "<script>
                alert('¡Registro exitoso! Tu folio asignado es: $folio');
                window.location.href = 'index.php';
              </script>";
    } else {
        echo "Error al registrar: " . $stmt->error;
    }
}
?>
