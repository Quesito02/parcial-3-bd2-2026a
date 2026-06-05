<?php
// membresias/listar.php
require_once '../conexion.php';

try {
    // Usamos INNER JOIN para traer el nombre del atleta y el nombre del plan
    $sql = "SELECT m.id_membresia, a.nombre AS nombre_afiliado, a.apellido AS apellido_afiliado, 
                   p.nombre AS nombre_plan, m.fecha_inicio, m.fecha_fin, m.estado, m.valor_pagado 
            FROM membresias m
            INNER JOIN afiliados a ON m.id_afiliado = a.id_afiliado
            INNER JOIN planes p ON m.id_plan = p.id_plan
            ORDER BY m.fecha_fin DESC";
    $stmt = $pdo->query($sql);
    $membresias = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Membresías - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light p-4">

    <div class="container" style="max-width: 1100px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bolder text-dark" style="letter-spacing: -1px;">
                <i class="bi bi-tags-fill text-info me-2"></i> Gestión de Membresías
            </h2>
            <div>
                <a href="../index.php" class="btn btn-outline-dark me-2 rounded-pill px-4"><i class="bi bi-house-door"></i> Inicio</a>
                <a href="nueva.php" class="btn btn-dark rounded-pill px-4"><i class="bi bi-plus-lg"></i> Vender Membresía</a>
            </div>
        </div>

        <div class="table-container shadow-sm">
            <table class="table align-middle mb-0 table-hover">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Atleta</th>
                        <th>Plan</th>
                        <th>Vencimiento</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($membresias) > 0): ?>
                        <?php foreach ($membresias as $mem): ?>
                            <tr>
                                <td class="ps-4 text-muted fw-bold">#<?php echo $mem['id_membresia']; ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($mem['nombre_afiliado'] . ' ' . $mem['apellido_afiliado']); ?></td>
                                <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($mem['nombre_plan']); ?></span></td>
                                <td><?php echo $mem['fecha_fin']; ?></td>
                                <td>
                                    <span class="badge <?php echo $mem['estado'] == 'Activa' ? 'bg-success' : 'bg-danger'; ?> rounded-pill px-3">
                                        <?php echo $mem['estado']; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group shadow-sm" role="group">
                                        <a href="editar.php?id=<?php echo $mem['id_membresia']; ?>" class="btn btn-sm btn-outline-primary px-3">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="eliminar.php?id=<?php echo $mem['id_membresia']; ?>" class="btn btn-sm btn-outline-danger px-3" onclick="return confirm('¿Peligro: Eliminar este registro financiero?');">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">No hay membresías vendidas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>