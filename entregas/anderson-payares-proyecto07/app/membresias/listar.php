<?php
// membresias/listar.php
require_once '../conexion.php';

try {
    $sql = "SELECT m.id_membresia, a.nombre AS afiliado_nombre, a.apellido AS afiliado_apellido, p.nombre AS plan_nombre, 
            m.fecha_venta, m.fecha_inicio, m.fecha_fin, m.valor_pagado, m.estado 
            FROM membresias m
            INNER JOIN afiliados a ON m.id_afiliado = a.id_afiliado
            INNER JOIN planes p ON m.id_plan = p.id_plan
            ORDER BY m.id_membresia DESC";
    $stmt = $pdo->query($sql);
    $membresias = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error en la consulta: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Membresías</title>
</head>
<body>

    <p>
        <a href="../index.php">Volver al Dashboard</a> | 
        <a href="nueva.php">Vender Nueva Membresía</a>
    </p>
    <hr>

    <h1>Gestión de Membresías</h1>

    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'guardado'): ?>
        <p style="color: green;"><strong>¡Éxito! La membresía ha sido registrada.</strong></p>
    <?php endif; ?>

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Afiliado</th>
                <th>Plan</th>
                <th>Fecha Venta</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Valor Pagado</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($membresias) > 0): ?>
                <?php foreach ($membresias as $mem): ?>
                    <tr>
                        <td><?php echo $mem['id_membresia']; ?></td>
                        <td><?php echo htmlspecialchars($mem['afiliado_nombre'] . " " . $mem['afiliado_apellido']); ?></td>
                        <td><?php echo htmlspecialchars($mem['plan_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($mem['fecha_venta']); ?></td>
                        <td><?php echo htmlspecialchars($mem['fecha_inicio']); ?></td>
                        <td><?php echo htmlspecialchars($mem['fecha_fin']); ?></td>
                        <td>$<?php echo number_format($mem['valor_pagado'], 2); ?></td>
                        <td><?php echo htmlspecialchars($mem['estado']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No hay membresías registradas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>