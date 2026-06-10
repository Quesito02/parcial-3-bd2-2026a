<?php
// membresias/editar.php
require_once '../conexion.php';

if (!isset($_GET['id'])) { header("Location: listar.php"); exit(); }
$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $estado = $_POST['estado'];
    $fecha_fin = $_POST['fecha_fin'];

    try {
        $sql = "UPDATE membresias SET estado=?, fecha_fin=? WHERE id_membresia=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$estado, $fecha_fin, $id]);
        header("Location: listar.php?mensaje=editado");
        exit();
    } catch (PDOException $e) {
        die("Error al modificar factura: " . $e->getMessage());
    }
}

$stmt = $pdo->prepare("SELECT m.*, a.nombre, a.apellido FROM membresias m INNER JOIN afiliados a ON m.id_afiliado = a.id_afiliado WHERE m.id_membresia = ?");
$stmt->execute([$id]);
$mem = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Modificar Membresía - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.1">
</head>
<body class="bg-light p-4">
    <div class="container" style="max-width: 500px;">
        <div class="card shadow border-0 mt-4">
            <div class="card-header bg-dark text-white fw-bold py-3">Modificar Vigencia Financiera</div>
            <div class="card-body p-4">
                <form action="editar.php?id=<?php echo $id; ?>" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Suscrito</label>
                        <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($mem['nombre'].' '.$mem['apellido']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Fecha de Vencimiento Manual</label>
                        <input type="date" name="fecha_fin" class="form-control" value="<?php echo $mem['fecha_fin']; ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Estado de Cobertura</label>
                        <select name="estado" class="form-select">
                            <option value="Activa" <?php echo $mem['estado'] == 'Activa' ? 'selected' : ''; ?>>Activa (Permitir Entrada)</option>
                            <option value="Inactiva" <?php echo $mem['estado'] == 'Inactiva' ? 'selected' : ''; ?>>Inactiva (Bloquear en puerta)</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="listar.php" class="btn btn-secondary rounded-pill px-4">Volver</a>
                        <button type="submit" class="btn btn-dark rounded-pill px-4">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>