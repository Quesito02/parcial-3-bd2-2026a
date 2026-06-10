<?php
// instructores/editar.php
require_once '../conexion.php';

if (!isset($_GET['id'])) { header("Location: listar.php"); exit(); }
$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $especialidad = $_POST['especialidad'];
    $estado = $_POST['estado'];

    try {
        $sql = "UPDATE instructores SET nombre=?, apellido=?, blackjack_especialidad=?, estado=? WHERE id_instructor=?"; 
        // Nota: Si en tu BD la columna es 'especialidad', quita el 'blackjack_' del string de arriba
        $sql = "UPDATE instructores SET nombre=?, apellido=?, especialidad=?, estado=? WHERE id_instructor=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $apellido, $especialidad, $estado, $id]);
        header("Location: listar.php?mensaje=editado");
        exit();
    } catch (PDOException $e) {
        die("Error al actualizar: " . $e->getMessage());
    }
}

$stmt = $pdo->prepare("SELECT * FROM instructores WHERE id_instructor = ?");
$stmt->execute([$id]);
$inst = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Instructor - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.1">
</head>
<body class="bg-light p-4">
    <div class="container" style="max-width: 550px;">
        <div class="card shadow border-0 mt-4">
            <div class="card-header bg-dark text-white fw-bold py-3">Modificar Miembro del Staff</div>
            <div class="card-body p-4">
                <form action="editar.php?id=<?php echo $id; ?>" method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($inst['nombre']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Apellido</label>
                            <input type="text" name="apellido" class="form-control" value="<?php echo htmlspecialchars($inst['apellido']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Especialidad</label>
                        <input type="text" name="especialidad" class="form-control" value="<?php echo htmlspecialchars($inst['especialidad']); ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Estado de Disponibilidad</label>
                        <select name="estado" class="form-select">
                            <option value="Activo" <?php echo $inst['estado'] == 'Activo' ? 'selected' : ''; ?>>Activo / Disponible</option>
                            <option value="Inactivo" <?php echo $inst['estado'] == 'Inactivo' ? 'selected' : ''; ?>>Inactivo / De Licencia</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="listar.php" class="btn btn-secondary rounded-pill px-4">Volver</a>
                        <button type="submit" class="btn btn-dark rounded-pill px-4">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>