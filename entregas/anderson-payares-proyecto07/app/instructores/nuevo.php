<?php
// instructores/nuevo.php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $especialidad = $_POST['especialidad'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO instructores (nombre, apellido, especialidad, estado) VALUES (?, ?, ?, 'Activo')");
        $stmt->execute([$nombre, $apellido, $especialidad]);
        header("Location: listar.php?mensaje=guardado");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Instructor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.1">
</head>
<body class="bg-light p-5">
    <div class="container" style="max-width: 500px;">
        <h2 class="mb-4">Registrar Instructor</h2>
        <form action="nuevo.php" method="POST" class="card p-4 shadow-sm border-0">
            <div class="mb-3">
                <label>Nombre:</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Apellido:</label>
                <input type="text" name="apellido" class="form-control" required>
            </div>
            <div class="mb-4">
                <label>Especialidad (Ej: Spinning, Pesas, Yoga):</label>
                <input type="text" name="especialidad" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-dark w-100">Guardar Instructor</button>
            <a href="../index.php" class="btn btn-outline-secondary w-100 mt-2">Volver al Inicio</a>
        </form>
    </div>
</body>
</html>