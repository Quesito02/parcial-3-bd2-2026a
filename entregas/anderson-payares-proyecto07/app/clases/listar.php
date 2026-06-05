<?php
// clases/listar.php
require_once '../conexion.php';

try {
    $sql = "SELECT c.*, i.nombre AS instructor_nombre, i.apellido AS instructor_apellido 
            FROM clases c 
            LEFT JOIN instructores i ON c.id_instructor = i.id_instructor 
            ORDER BY c.id_clase DESC";
    $stmt = $pdo->query($sql);
    $clases = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Clases - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light p-4">

    <div class="container" style="max-width: 1000px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bolder text-dark" style="letter-spacing: -1px;">
                <i class="bi bi-bicycle text-warning me-2"></i> Clases Grupales
            </h2>
            <div>
                <a href="../index.php" class="btn btn-outline-dark me-2 rounded-pill px-4"><i class="bi bi-house-door"></i> Inicio</a>
                <a href="nueva.php" class="btn btn-dark rounded-pill px-4"><i class="bi bi-plus-lg"></i> Programar Clase</a>
            </div>
        </div>

        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'editado'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> Clase actualizada correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-container shadow-sm">
            <table class="table align-middle mb-0 table-hover">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Clase</th>
                        <th>Instructor</th>
                        <th>Horario Asignado</th>
                        <th>Cupos</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($clases) > 0): ?>
                        <?php foreach ($clases as $clase): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary"><?php echo htmlspecialchars($clase['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($clase['instructor_nombre'] . " " . $clase['instructor_apellido']); ?></td>
                                <td>
                                    <i class="bi bi-calendar-range text-muted me-1"></i> <?php echo htmlspecialchars($clase['horario']); ?>
                                </td>
                                <td class="fw-bold"><?php echo $clase['cupo_maximo']; ?></td>
                                <td class="text-center">
                                    <div class="btn-group shadow-sm" role="group">
                                        <a href="editar.php?id=<?php echo $clase['id_clase']; ?>" class="btn btn-sm btn-outline-primary px-3">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="eliminar.php?id=<?php echo $clase['id_clase']; ?>" class="btn btn-sm btn-outline-danger px-3" onclick="return confirm('¿Seguro que deseas cancelar esta clase?');">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No hay clases programadas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>