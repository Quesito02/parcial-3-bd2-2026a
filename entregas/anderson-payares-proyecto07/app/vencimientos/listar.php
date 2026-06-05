<?php
// vencimientos/listar.php
require_once '../conexion.php';

try {
    // Consultamos membresías activas cuya fecha de fin sea menor o igual a 7 días a partir de hoy.
    // DATEDIFF(fecha1, fecha2) resta fecha2 a fecha1. 
    $sql = "SELECT m.id_membresia, a.documento, a.nombre, a.apellido, p.nombre AS plan_nombre, 
                   m.fecha_fin, DATEDIFF(m.fecha_fin, CURDATE()) AS dias_restantes
            FROM membresias m
            INNER JOIN afiliados a ON m.id_afiliado = a.id_afiliado
            INNER JOIN planes p ON m.id_plan = p.id_plan
            WHERE m.estado = 'Activa' AND DATEDIFF(m.fecha_fin, CURDATE()) <= 7
            ORDER BY dias_restantes ASC";
            
    $stmt = $pdo->query($sql);
    $vencimientos = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error en la consulta: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alertas de Vencimiento</title>
</head>
<body>

    <p>
        <a href="../index.php">Volver al Dashboard</a>
    </p>
    <hr>

    <h1>Alertas de Vencimiento</h1>
    <p>Mostrando membresías que vencen en los próximos 7 días o que ya están vencidas.</p>

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Afiliado</th>
                <th>Documento</th>
                <th>Plan Contratado</th>
                <th>Fecha de Fin</th>
                <th>Estado / Días Restantes</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($vencimientos) > 0): ?>
                <?php foreach ($vencimientos as $vencido): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($vencido['nombre'] . " " . $vencido['apellido']); ?></td>
                        <td><?php echo htmlspecialchars($vencido['documento']); ?></td>
                        <td><?php echo htmlspecialchars($vencido['plan_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($vencido['fecha_fin']); ?></td>
                        <td>
                            <?php 
                            $dias = $vencido['dias_restantes'];
                            if ($dias < 0) {
                                // Si los días son negativos, ya se pasó la fecha
                                echo "<span style='color: red; font-weight: bold;'>Vencida hace " . abs($dias) . " días</span>";
                            } elseif ($dias == 0) {
                                // Si es 0, vence exactamente hoy
                                echo "<span style='color: orange; font-weight: bold;'>¡Vence HOY!</span>";
                            } else {
                                // Si es mayor a 0, faltan esos días
                                echo "Faltan $dias días";
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Excelente. No hay membresías próximas a vencer.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>