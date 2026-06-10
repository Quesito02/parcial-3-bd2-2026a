<?php
// planes/editar.php
require_once '../conexion.php';

if (!isset($_GET['id'])) { header("Location: listar.php"); exit(); }
$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $duracion = $_POST['duracion_dias'];
    $estado = $_POST['estado'];

    try {
        $sql = "UPDATE planes SET nombre=?, precio=?, duracion_dias=?, estado=? WHERE id_plan=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $precio, $duracion, $estado, $id]);
        header("Location: listar.php?mensaje=editado");
        exit();
    } catch (PDOException $e) {
        die("Error al modificar: " . $e->getMessage());
    }
}

$stmt = $pdo->prepare("SELECT * FROM planes WHERE id_plan = ?");
$stmt->execute([$id]);
$plan = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Plan - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.1">
</head>
<body class="bg-light p-4">
    <div class="container" style="max-width: 500px;">
        <div class="card shadow border-0 mt-4">
            <div class="card-header bg-dark text-white fw-bold py-3">Configurar Plan de Suscripción</div>
            <div class="card-body p-4">
                <form action="editar.php?id=<?php echo $id; ?>" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre comercial</label>
                        <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($plan['nombre']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Inversión ($)</label>
                        <input type="number" name="precio" class="form-control" value="<?php echo $plan['precio']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Días del Ciclo</label>
                        <input type="number" name="duracion_dias" class="form-control" value="<?php echo $plan['duracion_dias']; ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Estado de Oferta</label>
                        <select name="estado" class="form-select">
                            <option value="Activo" <?php echo $plan['estado'] == 'Activo' ? 'selected' : ''; ?>>Activo (Disponible en caja)</option>
                            <option value="Inactivo" <?php echo $plan['estado'] == 'Inactivo' ? 'selected' : ''; ?>>Inactivo (Archivado)</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="listar.php" class="btn btn-secondary rounded-pill px-4">Cancelar</a>
                        <button type="submit" class="btn btn-dark rounded-pill px-4">Actualizar Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>