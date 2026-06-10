<?php
// clases/nueva.php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $id_instructor = $_POST['id_instructor'];
    $horario = $_POST['horario']; // Vinculado a tu columna real VARCHAR 'horario'
    $cupo = $_POST['cupo_maximo'];

    try {
        $sql = "INSERT INTO clases (nombre, id_instructor, horario, cupo_maximo) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $id_instructor, $horario, $cupo]);
        header("Location: listar.php");
        exit();
    } catch (PDOException $e) {
        die("Error al programar sesión: " . $e->getMessage());
    }
}

$instructores = $pdo->query("SELECT * FROM instructores WHERE estado = 'Activo' ORDER BY nombre ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Programar Clase - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.1">
</head>
<body class="bg-light p-4">
    <div class="container" style="max-width: 550px;">
        <div class="card shadow border-0 mt-4">
            <div class="card-header bg-dark text-white fw-bold py-3">Apertura de Clase Grupal</div>
            <div class="card-body p-4">
                <form action="nueva.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre de la Disciplina</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Spinning Pro, Crossfit, Zumba..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Instructor a Cargo</label>
                        <select name="id_instructor" class="form-select" required>
                            <option value="">-- Asignar Líder de Sesión --</option>
                            <?php foreach ($instructores as $i): ?>
                                <option value="<?php echo $i['id_instructor']; ?>"><?php echo htmlspecialchars($i['nombre'] . ' ' . $i['apellido'] . ' (' . $i['especialidad'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold">Horario Establecido</label>
                            <input type="text" name="horario" class="form-control" placeholder="Ej: Martes y Jueves 7:00 PM" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Cupo Límite</label>
                            <input type="number" name="cupo_maximo" class="form-control" placeholder="Ej: 20" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="listar.php" class="btn btn-secondary rounded-pill px-4">Cancelar</a>
                        <button type="submit" class="btn btn-dark rounded-pill px-4">Abrir Agenda</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>