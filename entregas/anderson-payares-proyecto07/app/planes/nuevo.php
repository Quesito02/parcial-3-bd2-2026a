<?php
// planes/nuevo.php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio']; // Ajustado a tu columna real 'precio'
    $duracion = $_POST['duracion_dias'];

    try {
        $sql = "INSERT INTO planes (nombre, precio, duracion_dias, estado) VALUES (?, ?, ?, 'Activo')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $precio, $duracion]);
        header("Location: listar.php");
        exit();
    } catch (PDOException $e) {
        die("Error al guardar: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Plan - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.1">
</head>
<body class="bg-light p-4">
    <div class="container" style="max-width: 500px;">
        <div class="card shadow border-0 mt-4">
            <div class="card-header bg-dark text-white fw-bold py-3">Crear Plan de Entrenamiento</div>
            <div class="card-body p-4">
                <form action="nuevo.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre del Oferta</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: VIP Black, Trimestral..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Precio / Valor ($)</label>
                        <input type="number" name="precio" class="form-control" placeholder="Monto en pesos colombianos" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Duración (Días de Cobertura)</label>
                        <input type="number" name="duracion_dias" class="form-control" placeholder="Ej: 30, 90, 365" required>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="listar.php" class="btn btn-secondary rounded-pill px-4">Volver</a>
                        <button type="submit" class="btn btn-dark rounded-pill px-4">Publicar Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>