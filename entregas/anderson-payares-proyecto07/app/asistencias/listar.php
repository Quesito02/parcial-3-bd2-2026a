<?php
// app/asistencias/listar.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../conexion.php'; 

$mensaje = "";

try {
    $query = "SELECT a.fecha, a.hora, a.observaciones, f.documento, f.nombre, f.apellido 
              FROM asistencias a 
              JOIN afiliados f ON a.id_afiliado = f.id_afiliado 
              ORDER BY a.fecha DESC, a.hora DESC LIMIT 100";
    $stmt = $pdo->query($query);
    $historial = $stmt->fetchAll();

    $totalHoy = 0; $totalExpress = 0; $totalPases = 0; $totalCanjes = 0;
    foreach ($historial as $reg) {
        if ($reg['fecha'] === date('Y-m-d')) $totalHoy++;
        if (strpos($reg['observaciones'], 'Check-in Express') !== false) $totalExpress++;
        elseif (strpos($reg['observaciones'], 'Pase Diario') !== false) $totalPases++;
        elseif (strpos($reg['observaciones'], 'CANJE:') !== false) $totalCanjes++;
    }
} catch (PDOException $e) {
    $mensaje = "<div class='alert alert-danger fw-bold text-center shadow-sm'>Error: " . $e->getMessage() . "</div>";
    $historial = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría de Asistencias - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #111; color: #fff; }
        .kings-card { background: #212529; border: 1px solid rgba(255,193,7,0.2); border-radius: 15px; }
        .stat-badge { background-color: #151719; border: 1px solid #333; border-radius: 10px; padding: 12px; text-align: center; }
        .badge-express { background-color: rgba(13, 202, 240, 0.15) !important; color: #0dcaf0 !important; border: 1px solid #0dcaf0; }
        .badge-pase { background-color: rgba(25, 135, 84, 0.15) !important; color: #198754 !important; border: 1px solid #198754; }
        .badge-canje { background-color: rgba(220, 53, 69, 0.15) !important; color: #dc3545 !important; border: 1px solid #dc3545; }
    </style>
</head>
<body>

    <div class="container py-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bolder text-white mb-0"><i class="bi bi-journal-text text-warning me-2"></i> TRÁFICO Y ENTRADAS</h1>
                <p class="text-secondary mb-0">Auditoría del libro de accesos rápidos.</p>
            </div>
            <div>
                <a href="registrar.php" class="btn btn-warning rounded-pill btn-sm px-3 fw-bold text-dark me-1"><i class="bi bi-check-circle-fill me-1"></i> Nueva Asistencia</a>
                <a href="../index.php" class="btn btn-outline-warning rounded-pill btn-sm px-3"><i class="bi bi-house-door"></i> Inicio</a>
            </div>
        </div>

        <?php echo $mensaje; ?>

        <div class="row g-3 mb-5">
            <div class="col-md-3 col-6"><div class="stat-badge"><small class="text-secondary d-block fw-bold text-uppercase" style="font-size:0.65rem;">Total Hoy</small><span class="fs-4 fw-bold text-white"><?php echo $totalHoy; ?></span></div></div>
            <div class="col-md-3 col-6"><div class="stat-badge"><small class="text-secondary d-block fw-bold text-uppercase" style="font-size:0.65rem;">Express</small><span class="fs-4 fw-bold text-info"><?php echo $totalExpress; ?></span></div></div>
            <div class="col-md-3 col-6"><div class="stat-badge"><small class="text-secondary d-block fw-bold text-uppercase" style="font-size:0.65rem;">Pases Diarios</small><span class="fs-4 fw-bold text-success"><?php echo $totalPases; ?></span></div></div>
            <div class="col-md-3 col-6"><div class="stat-badge"><small class="text-secondary d-block fw-bold text-uppercase" style="font-size:0.65rem;">Canjes</small><span class="fs-4 fw-bold text-danger"><?php echo $totalCanjes; ?></span></div></div>
        </div>

        <div class="table-container kings-card p-4">
            <div class="table-responsive">
                <table class="table table-dark table-striped align-middle m-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Documento</th>
                            <th>Atleta / Comprador</th>
                            <th>Concepto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($historial as $row): 
                            $claseBadge = (strpos($row['observaciones'], 'Check-in Express') !== false) ? 'badge-express' : ((strpos($row['observaciones'], 'Pase Diario') !== false) ? 'badge-pase' : ((strpos($row['observaciones'], 'CANJE:') !== false) ? 'badge-canje' : 'bg-dark'));
                        ?>
                            <tr>
                                <td><small class="text-white-50"><?php echo $row['fecha']; ?></small></td>
                                <td><span class="badge bg-dark border border-secondary text-warning font-monospace"><?php echo $row['hora']; ?></span></td>
                                <td class="text-secondary font-monospace fw-bold"><?php echo htmlspecialchars($row['documento']); ?></td>
                                <td class="text-white fw-bold"><?php echo htmlspecialchars($row['nombre'] . " " . $row['apellido']); ?></td>
                                <td><span class="badge <?php echo $claseBadge; ?> px-3 py-1.5 rounded-pill small"><?php echo htmlspecialchars($row['observaciones']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>