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
</head>
<body class="bg-light p-4">

    <div class="container" style="max-width: 900px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bolder text-dark" style="letter-spacing: -1px;">
                <i class="bi bi-person-badge text-warning me-2"></i> Nuestro Equipo
            </h2>
            <div>
                <a href="../index.php" class="btn btn-outline-dark me-2 rounded-pill px-4">Volver al Inicio</a>
                <a href="nuevo.php" class="btn btn-dark rounded-pill px-4">Registrar Instructor</a>
            </div>
        </div>

        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'guardado'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> Instructor registrado correctamente en la plantilla.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Nombre Completo</th>
                            <th>Especialidad</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($instructores) > 0): ?>
                            <?php foreach ($instructores as $inst): ?>
                                <tr>
                                    <td class="ps-4 text-muted"><?php echo $inst['id_instructor']; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($inst['nombre'] . " " . $inst['apellido']); ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($inst['especialidad']); ?></span></td>
                                    <td>
                                        <span class="badge <?php echo $inst['estado'] == 'Activo' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $inst['estado']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No hay instructores en el sistema. Empieza registrando uno.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>