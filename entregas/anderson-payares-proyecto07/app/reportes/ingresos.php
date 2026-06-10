<?php
// app/ingresos.php (Ubicado en la carpeta principal junto al index.php)
require_once '../conexion.php';

$mensaje = "";
$precioPaseDiario = 10000; 
$capacidadMaximaGym = 40; // Ajusta aquí el límite de tu aforo

// --- LÓGICA 1: REGISTRAR PASE DIARIO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_pase'])) {
    $doc = trim($_POST['documento']);
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    
    try {
        $pdo->beginTransaction();
        $stmtCheck = $pdo->prepare("SELECT id_afiliado FROM afiliados WHERE documento = ?");
        $stmtCheck->execute([$doc]);
        $existe = $stmtCheck->fetch();

        if ($existe) {
            $id_afiliado = $existe['id_afiliado'];
        } else {
            $stmtIns = $pdo->prepare("INSERT INTO afiliados (documento, nombre, apellido, somatotipo, objetivo) VALUES (?, ?, ?, 'Mesomorfo', 'Mantenimiento')");
            $stmtIns->execute([$doc, $nombre, $apellido]);
            $id_afiliado = $pdo->lastInsertId();
        }

        $stmtAsis = $pdo->prepare("INSERT INTO asistencias (id_afiliado, fecha, hora, observaciones) VALUES (?, CURDATE(), CURTIME(), 'Pase Diario - Pagó $precioPaseDiario')");
        $stmtAsis->execute([$id_afiliado]);

        $pdo->commit();
        $mensaje = "<div class='alert alert-success fw-bold text-center shadow-sm'><i class='bi bi-cash-coin me-2'></i> ¡Pase Diario registrado con éxito!</div>";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $mensaje = "<div class='alert alert-danger fw-bold text-center shadow-sm'>Error: " . $e->getMessage() . "</div>";
    }
}

// --- LÓGICA 2: REPORTES Y ESTADÍSTICAS ---
try {
    // Pases diarios HOY
    $stmtDineroHoy = $pdo->query("SELECT a.hora, f.documento, f.nombre, f.apellido FROM asistencias a JOIN afiliados f ON a.id_afiliado = f.id_afiliado WHERE a.fecha = CURDATE() AND a.observaciones LIKE 'Pase Diario%' ORDER BY a.hora DESC");
    $dineroHoy = $stmtDineroHoy->fetchAll();

    // Socios HOY
    $stmtSociosHoy = $pdo->query("SELECT a.hora, f.nombre, f.apellido, a.observaciones FROM asistencias a JOIN afiliados f ON a.id_afiliado = f.id_afiliado WHERE a.fecha = CURDATE() AND a.observaciones NOT LIKE 'Pase Diario%' ORDER BY a.hora DESC");
    $sociosHoy = $stmtSociosHoy->fetchAll();

    // Conteo para Finanzas y Gráficos
    $totalPasesHoy = count($dineroHoy);
    $totalSociosHoy = count($sociosHoy);
    $totalAsistenciasHoy = $totalPasesHoy + $totalSociosHoy;
    
    // Cálculo de porcentaje de aforo actual
    $porcentajeAforo = min(round(($totalAsistenciasHoy / $capacidadMaximaGym) * 100), 100);

    $totalDineroHoy = $totalPasesHoy * $precioPaseDiario;

    $stmtCajaMes = $pdo->query("SELECT COUNT(*) as total FROM asistencias WHERE MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE()) AND observaciones LIKE 'Pase Diario%'");
    $totalDineroMes = $stmtCajaMes->fetch()['total'] * $precioPaseDiario;

} catch (PDOException $e) {
    $mensaje = "<div class='alert alert-danger fw-bold text-center shadow-sm'>Error: " . $e->getMessage() . "</div>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Inteligente - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="assets/css/style.css?v=1.7">
    <style>
        body { background-color: #111; color: #fff; }
        .kings-card { background: #212529; border: 1px solid rgba(255,193,7,0.2); border-radius: 15px; }
        .stat-box { border-left: 4px solid #ffc107; background: #1a1d20; padding: 12px; border-radius: 8px; }
        .stat-box.success { border-left-color: #198754; }
        .stat-box.info { border-left-color: #0dcaf0; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>

    <div class="container py-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <h1 class="fw-bolder text-white mb-0">KINGS REPORTS</h1>
                <p class="text-secondary mb-0">Módulo de analítica y control financiero centralizado.</p>
            </div>
            <a href="../index.php" class="btn btn-outline-secondary rounded-pill btn-sm px-3"><i class="bi bi-arrow-left"></i> Panel</a>
        </div>

        <?php echo $mensaje; ?>

        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="stat-box success">
                    <small class="text-secondary d-block fw-bold text-uppercase small">Caja Efectivo Hoy</small>
                    <span class="text-success fw-bolder fs-4">$<?php echo number_format($totalDineroHoy); ?></span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-box">
                    <small class="text-secondary d-block fw-bold text-uppercase small">Recaudo del Mes</small>
                    <span class="text-warning fw-bolder fs-4">$<?php echo number_format($totalDineroMes); ?></span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-box info">
                    <small class="text-secondary d-block fw-bold text-uppercase small">Accesos de Socios</small>
                    <span class="text-info fw-bolder fs-4"><?php echo $totalSociosHoy; ?> Entradas</span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-box">
                    <small class="text-secondary d-block fw-bold text-uppercase small">Pases Vendidos Hoy</small>
                    <span class="text-white fw-bolder fs-4"><?php echo $totalPasesHoy; ?> Pases</span>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6 no-print">
                <div class="kings-card p-4 h-100">
                    <h5 class="text-warning fw-bold mb-3"><i class="bi bi-ticket-perforated-fill me-2"></i> Taquilla Express: Venta de un Día</h5>
                    <form action="ingresos.php" method="POST" class="row g-2">
                        <input type="hidden" name="accion_pase" value="1">
                        <div class="col-12 mb-2">
                            <input type="text" name="documento" class="form-control form-control-sm" placeholder="Documento Identidad" required autocomplete="off">
                        </div>
                        <div class="col-6">
                            <input type="text" name="nombre" class="form-control form-control-sm" placeholder="Nombre" required autocomplete="off">
                        </div>
                        <div class="col-6">
                            <input type="text" name="apellido" class="form-control form-control-sm" placeholder="Apellido" required autocomplete="off">
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-warning w-100 fw-bold rounded-pill btn-dark">
                                <i class="bi bi-currency-dollar"></i> Cobrar Entrada ($<?php echo number_format($precioPaseDiario); ?>)
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="kings-card p-4 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h5 class="text-warning fw-bold m-0"><i class="bi bi-speedometer2 me-1"></i> Ocupación en Tiempo Real</h5>
                            <span class="badge bg-warning text-dark fw-bold"><?php echo $porcentajeAforo; ?>%</span>
                        </div>
                        <div class="progress bg-dark mb-3" style="height: 10px; border: 1px solid #333;">
                            <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo $porcentajeAforo; ?>%"></div>
                        </div>
                    </div>

                    <div class="mx-auto" style="width: 100%; max-width: 220px;">
                        <canvas id="chartGymKings"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="kings-card p-4">
            <ul class="nav nav-tabs border-secondary mb-3 no-print" id="accountingTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active text-white bg-dark border-secondary" id="recaudo-tab" data-bs-toggle="tab" data-bs-target="#recaudo-pane" type="button">
                        <i class="bi bi-cash-stack text-success me-1"></i> Caja Chica (Ingreso de Dinero)
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link text-white bg-dark border-secondary ms-2" id="asistencias-tab" data-bs-toggle="tab" data-bs-target="#asistencias-pane" type="button">
                        <i class="bi bi-person-walking text-info me-1"></i> Tráfico de Puerta (Socios)
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="recaudo-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-dark table-striped align-middle m-0">
                            <thead>
                                <tr>
                                    <th>Hora Pago</th>
                                    <th>Cliente Casual</th>
                                    <th>Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($dineroHoy)): ?>
                                    <tr><td colspan="3" class="text-center text-muted py-3">No hay pases vendidos hoy.</td></tr>
                                <?php else: ?>
                                    <?php foreach($dineroHoy as $caja): ?>
                                        <tr>
                                            <td><span class="badge bg-success-subtle text-success"><?php echo $caja['hora']; ?></span></td>
                                            <td class="fw-bold"><?php echo htmlspecialchars($caja['nombre'] . " " . $caja['apellido']); ?></td>
                                            <td class="text-success fw-bold">+$<?php echo number_format($precioPaseDiario); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="asistencias-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-dark table-striped align-middle m-0">
                            <thead>
                                <tr>
                                    <th>Hora Entrada</th>
                                    <th>Atleta Afiliado</th>
                                    <th>Estado / Alerta</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($sociosHoy)): ?>
                                    <tr><td colspan="3" class="text-center text-muted py-3">No han entrado afiliados hoy.</td></tr>
                                <?php else: ?>
                                    <?php foreach($sociosHoy as $socio): ?>
                                        <tr>
                                            <td><span class="badge bg-dark text-warning"><?php echo $socio['hora']; ?></span></td>
                                            <td class="fw-bold"><?php echo htmlspecialchars($socio['nombre'] . " " . $socio['apellido']); ?></td>
                                            <td><span class="text-secondary"><i><?php echo htmlspecialchars($socio['observaciones']); ?></i></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('chartGymKings').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pases de un Día', 'Socios Activos'],
                datasets: [{
                    data: [<?php echo $totalPasesHoy; ?>, <?php echo $totalSociosHoy; ?>],
                    backgroundColor: ['#ffc107', '#0dcaf0'],
                    borderWidth: 2,
                    borderColor: '#212529'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#fff', font: { size: 11 } }
                    }
                }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>