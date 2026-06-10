<?php
// planes/listar.php
require_once '../conexion.php';

try {
    $sql = "SELECT * FROM planes ORDER BY nombre ASC";
    $stmt = $pdo->query($sql);
    $planes = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planes - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.1">
</head>
<body class="bg-light p-4">

    <div class="container" style="max-width: 1000px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bolder text-dark" style="letter-spacing: -1px;">
                <i class="bi bi-card-checklist text-success me-2"></i> Gestión de Planes
            </h2>
            <div>
                <a href="../index.php" class="btn btn-outline-dark me-2 rounded-pill px-4"><i class="bi bi-house-door"></i> Inicio</a>
                <a href="nuevo.php" class="btn btn-dark rounded-pill px-4"><i class="bi bi-plus-lg"></i> Nuevo Plan</a>
            </div>
        </div>

        <?php if (isset($_GET['mensaje'])): ?>
            <?php if ($_GET['mensaje'] === 'eliminado'): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                    <i class="bi bi-trash-fill me-2"></i> Plan eliminado permanentemente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['mensaje'] === 'editado'): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Plan actualizado correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="table-container shadow-sm">
            <table class="table align-middle mb-0 table-hover">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Nombre del Plan</th>
                        <th>Valor ($)</th>
                        <th>Duración</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($planes) > 0): ?>
                        <?php foreach ($planes as $plan): ?>
                            <tr>
                                <td class="ps-4 text-muted fw-bold">#<?php echo $plan['id_plan']; ?></td>
                                <td class="fw-bold text-dark"><?php echo htmlspecialchars($plan['nombre']); ?></td>
                                <td class="text-success fw-bold">$<?php echo number_format($plan['precio'], 0, ',', '.'); ?></td>                                <td><?php echo $plan['duracion_dias']; ?> días</td>
                                <td>
                                    <span class="badge <?php echo $plan['estado'] == 'Activo' ? 'bg-success' : 'bg-secondary'; ?> rounded-pill px-3">
                                        <?php echo $plan['estado']; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group shadow-sm" role="group">
                                        <a href="editar.php?id=<?php echo $plan['id_plan']; ?>" class="btn btn-sm btn-outline-primary px-3">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="eliminar.php?id=<?php echo $plan['id_plan']; ?>" class="btn btn-sm btn-outline-danger px-3" onclick="return confirm('¿Peligro: Seguro que deseas eliminar este plan? Esto podría afectar las membresías activas.');">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No hay planes registrados en el sistema.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>