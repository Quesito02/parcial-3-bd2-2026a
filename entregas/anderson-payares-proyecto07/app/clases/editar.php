<?php
// clases/editar.php
require_once '../conexion.php';

if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit();
}
$id = $_GET['id'];

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $id_instructor = $_POST['id_instructor'];
    $horario = $_POST['horario']; // Usamos 'horario'
    $cupo = $_POST['cupo_maximo'];

    try {
        $sql = "UPDATE clases SET nombre=?, id_instructor=?, horario=?, cupo_maximo=? WHERE id_clase=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $id_instructor, $horario, $cupo, $id]);
        
        header("Location: listar.php?mensaje=editado");
        exit();
    } catch (PDOException $e) {
        die("Error al actualizar: " . $e->getMessage());
    }
}

$stmt = $pdo->prepare("SELECT * FROM clases WHERE id_clase = ?");
$stmt->execute([$id]);
$clase = $stmt->fetch();

$stmtInstructores = $pdo->query("SELECT * FROM instructores WHERE estado = 'Activo'");
$instructores = $stmtInstructores->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Clase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.1">
</head>
<body class="bg-light p-4">
    <div class="container" style="max-width: 600px;">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white fw-bold">Editar Clase Programada</div>
            <div class="card-body">
                <form action="editar.php?id=<?php echo $id; ?>" method="POST">
                    
                    <div class="mb-3">
                        <label>Nombre de la Clase</label>
                        <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($clase['nombre']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label>Instructor Asignado</label>
                        <select name="id_instructor" class="form-select" required>
                            <?php foreach ($instructores as $inst): ?>
                                <option value="<?php echo $inst['id_instructor']; ?>" <?php echo ($clase['id_instructor'] == $inst['id_instructor']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($inst['nombre'] . ' ' . $inst['apellido']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Horario (Ej: Lunes 6:00 PM)</label>
                            <input type="text" name="horario" class="form-control" value="<?php echo htmlspecialchars($clase['horario']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label>Cupo Máximo</label>
                            <input type="number" name="cupo_maximo" class="form-control" value="<?php echo $clase['cupo_maximo']; ?>" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="listar.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-warning fw-bold text-dark">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>