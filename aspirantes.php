<?php
include 'conexion.php';

// C - INSERTAR
if (isset($_POST['crear'])) {
    $folio = 'UNP-' . rand(1000, 9999);
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $curp = $_POST['curp'];
    $carrera = $_POST['carrera'];

    $stmt = $conexion->prepare("INSERT INTO aspirantes (folio, nombre, correo, curp, carrera) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $folio, $nombre, $correo, $curp, $carrera);
    $stmt->execute();
    header("Location: aspirantes.php");
    exit();
}

// U - ACTUALIZAR
if (isset($_POST['actualizar'])) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $curp = $_POST['curp'];
    $carrera = $_POST['carrera'];
    $estatus = $_POST['estatus'];

    $stmt = $conexion->prepare("UPDATE aspirantes SET nombre=?, correo=?, curp=?, carrera=?, estatus=? WHERE id=?");
    $stmt->bind_param("sssssi", $nombre, $correo, $curp, $carrera, $estatus, $id);
    $stmt->execute();
    header("Location: aspirantes.php");
    exit();
}

// D - ELIMINAR
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $conexion->prepare("DELETE FROM aspirantes WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: aspirantes.php");
    exit();
}

// R - CONSULTAR
$resultado = $conexion->query("SELECT * FROM aspirantes ORDER BY fecha_registro DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel CRUD - Aspirantes UNP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 p-8 font-sans">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow-md">
        <h1 class="text-2xl font-bold text-slate-800 mb-6">Gestión de Aspirantes - Universidad UNP</h1>

        <!-- Formulario para Registrar (INSERT) -->
        <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 bg-slate-50 p-4 rounded-lg border">
            <input type="text" name="nombre" placeholder="Nombre completo" required class="p-2 border rounded">
            <input type="email" name="correo" placeholder="Correo electrónico" required class="p-2 border rounded">
            <input type="text" name="curp" placeholder="CURP" required class="p-2 border rounded">
            <select name="carrera" class="p-2 border rounded" required>
                <option value="Ingeniería en Diseño Automotriz">Ingeniería en Diseño Automotriz</option>
                <option value="Ingeniería en Energías Renovables">Ingeniería en Energías Renovables</option>
            </select>
            <button type="submit" name="crear" class="bg-blue-900 text-white font-bold py-2 px-4 rounded hover:bg-blue-800 transition">
                + Registrar Aspirante
            </button>
        </form>

        <!-- Tabla de Consulta y Modificación (READ, UPDATE, DELETE) -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-800 text-white">
                        <th class="p-3">Folio</th>
                        <th class="p-3">Nombre</th>
                        <th class="p-3">Correo</th>
                        <th class="p-3">Carrera</th>
                        <th class="p-3">Estatus</th>
                        <th class="p-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $resultado->fetch_assoc()): ?>
                    <tr class="border-b hover:bg-slate-50">
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <td class="p-3 font-mono font-bold text-slate-600"><?= $row['folio'] ?></td>
                            <td class="p-3"><input type="text" name="nombre" value="<?= $row['nombre'] ?>" class="border rounded p-1 w-full"></td>
                            <td class="p-3"><input type="email" name="correo" value="<?= $row['correo'] ?>" class="border rounded p-1 w-full"></td>
                            <td class="p-3">
                                <select name="carrera" class="border rounded p-1 w-full">
                                    <option value="Ingeniería en Diseño Automotriz" <?= $row['carrera'] == 'Ingeniería en Diseño Automotriz' ? 'selected' : '' ?>>Diseño Automotriz</option>
                                    <option value="Ingeniería en Energías Renovables" <?= $row['carrera'] == 'Ingeniería en Energías Renovables' ? 'selected' : '' ?>>Energías Renovables</option>
                                </select>
                            </td>
                            <td class="p-3">
                                <select name="estatus" class="border rounded p-1 text-sm font-semibold">
                                    <option value="Pendiente" <?= $row['estatus'] == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                    <option value="Aceptado" <?= $row['estatus'] == 'Aceptado' ? 'selected' : '' ?>>Aceptado</option>
                                    <option value="Rechazado" <?= $row['estatus'] == 'Rechazado' ? 'selected' : '' ?>>Rechazado</option>
                                </select>
                            </td>
                            <td class="p-3 text-center space-x-2">
                                <button type="submit" name="actualizar" class="bg-emerald-600 text-white px-3 py-1 rounded text-sm hover:bg-emerald-700">Guardar</button>
                                <a href="aspirantes.php?eliminar=<?= $row['id'] ?>" onclick="return confirm('¿Desea eliminar este registro?')" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 inline-block">Eliminar</a>
                            </td>
                        </form>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>