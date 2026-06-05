<?php
// instructores/listar.php
require_once '../conexion.php';

try {
    $sql = "SELECT * FROM instructores ORDER BY nombre ASC";
    $stmt = $pdo->query($sql);
    $instructores = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Instructores - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light p-4">

    <div class="container" style="max-width: 900px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bolder text-dark" style="letter-spacing: -1px;">
                <i class="bi bi-person-badge text-warning me-2"></i> Nuestro Equipo
            </h2>
            <div>
                <a href="../index.php" class="btn btn-outline-dark me-2 rounded-pill px-4"><i class="bi bi-house-door"></i> Inicio</a>
                <a href="nuevo.php" class="btn btn-dark rounded-pill px-4"><i class="bi bi-plus-lg"></i> Registrar Instructor</a>
            </div>
        </div>

        <div class="table-container shadow-sm">
            <table class="table align-middle mb-0 table-hover">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Nombre Completo</th>
                        <th>Especialidad</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($instructores) > 0): ?>
                        <?php foreach ($instructores as $inst): ?>
                            <tr>
                                <td class="ps-4 text-muted fw-bold">#<?php echo $inst['id_instructor']; ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($inst['nombre'] . " " . $inst['apellido']); ?></td>
                                <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($inst['especialidad']); ?></span></td>
                                <td>
                                    <span class="badge <?php echo $inst['estado'] == 'Activo' ? 'bg-success' : 'bg-danger'; ?> rounded-pill px-3">
                                        <?php echo $inst['estado']; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group shadow-sm" role="group">
                                        <a href="editar.php?id=<?php echo $inst['id_instructor']; ?>" class="btn btn-sm btn-outline-primary px-3">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="eliminar.php?id=<?php echo $inst['id_instructor']; ?>" class="btn btn-sm btn-outline-danger px-3" onclick="return confirm('¿Seguro que deseas eliminar a este instructor de la plantilla?');">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No hay instructores en el sistema.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>