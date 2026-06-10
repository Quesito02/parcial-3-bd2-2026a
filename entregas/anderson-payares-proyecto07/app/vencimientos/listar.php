<?php
// vencimientos/listar.php
require_once '../conexion.php';

try {
    // Buscamos membresías vencidas o que vencen en los próximos 3 días
    $sql = "SELECT m.id_membresia, m.fecha_fin, m.estado, 
                   af.nombre, af.apellido, af.telefono, 
                   p.nombre AS plan_nombre,
                   DATEDIFF(m.fecha_fin, CURDATE()) as dias_restantes
            FROM membresias m
            INNER JOIN afiliados af ON m.id_afiliado = af.id_afiliado
            INNER JOIN planes p ON m.id_plan = p.id_plan
            WHERE DATEDIFF(m.fecha_fin, CURDATE()) <= 3 OR m.estado = 'Inactiva'
            ORDER BY m.fecha_fin ASC";
    $stmt = $pdo->query($sql);
    $vencimientos = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Vencimientos - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.1">
</head>
<body class="bg-light p-4">

    <div class="container" style="max-width: 1000px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bolder text-danger" style="letter-spacing: -1px;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> Centro de Cobros y Vencimientos
            </h2>
            <a href="../index.php" class="btn btn-outline-dark rounded-pill px-4"><i class="bi bi-house-door"></i> Inicio</a>
        </div>

        <div class="alert alert-warning border-start border-warning border-4 shadow-sm mb-4">
            <i class="bi bi-info-circle-fill"></i> Este panel muestra automáticamente las membresías que ya están vencidas o a punto de vencer en los próximos 3 días.
        </div>

        <div class="table-container shadow-sm border-top border-danger border-4">
            <table class="table align-middle mb-0 table-hover">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Atleta</th>
                        <th>Plan</th>
                        <th>Vencimiento</th>
                        <th>Estado</th>
                        <th class="text-center">Acción de Cobro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($vencimientos) > 0): ?>
                        <?php foreach ($vencimientos as $venc): ?>
                            <?php 
                                // Formatear el número para WhatsApp y crear el mensaje
                                $telefono = preg_replace('/[^0-9]/', '', $venc['telefono']); 
                                $mensaje = urlencode("¡Hola " . $venc['nombre'] . "! Te saludamos de Gym Kings. Te recordamos que tu membresía '" . $venc['plan_nombre'] . "' " . ($venc['dias_restantes'] < 0 ? "se ha vencido" : "está a punto de vencer") . ". ¡Te esperamos en recepción para renovar y seguir entrenando duro!");
                                $linkWhatsapp = "https://wa.me/57" . $telefono . "?text=" . $mensaje;
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark"><?php echo htmlspecialchars($venc['nombre'] . ' ' . $venc['apellido']); ?></td>
                                <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($venc['plan_nombre']); ?></span></td>
                                <td>
                                    <?php echo $venc['fecha_fin']; ?><br>
                                    <?php if ($venc['dias_restantes'] < 0): ?>
                                        <small class="text-danger fw-bold">Vencido hace <?php echo abs($venc['dias_restantes']); ?> días</small>
                                    <?php elseif ($venc['dias_restantes'] == 0): ?>
                                        <small class="text-warning fw-bold">Vence HOY</small>
                                    <?php else: ?>
                                        <small class="text-muted">Vence en <?php echo $venc['dias_restantes']; ?> días</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $venc['dias_restantes'] < 0 ? 'bg-danger' : 'bg-warning text-dark'; ?> rounded-pill px-3">
                                        <?php echo $venc['dias_restantes'] < 0 ? 'Vencida' : 'Por Vencer'; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($telefono)): ?>
                                        <a href="<?php echo $linkWhatsapp; ?>" target="_blank" class="btn btn-sm btn-success px-3 rounded-pill shadow-sm">
                                            <i class="bi bi-whatsapp"></i> Notificar
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Sin número</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No hay membresías vencidas o por vencer actualmente.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>