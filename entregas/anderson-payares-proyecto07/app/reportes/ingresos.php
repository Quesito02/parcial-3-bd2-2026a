<?php
// app/reportes/ingresos.php (Módulo Unificado de Finanzas, Taquilla y Auditoría)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🛠️ BLINDAJE DE RUTA DE CONEXIÓN AUTOMÁTICA
if (file_exists('conexion.php')) {
    require_once 'conexion.php';
} else {
    require_once '../conexion.php';
}

$mensaje = "";
$precioPaseDiario = 10000; 
$capacidadMaximaGym = 40; 

// 🛠️ DETECTOR AUTOMÁTICO DE COLUMNAS (id vs id_afiliado / id_plan vs id)
$col_afiliado = "id_afiliado";
try { $pdo->query("SELECT id_afiliado FROM afiliados LIMIT 1"); } catch (PDOException $e) { $col_afiliado = "id"; }

$col_plan = "id_plan";
try { $pdo->query("SELECT id_plan FROM planes LIMIT 1"); } catch (PDOException $e) { $col_plan = "id"; }

// --- LÓGICA 1: REGISTRAR PASE DIARIO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_pase'])) {
    $doc = trim($_POST['documento']);
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    
    try {
        $pdo->beginTransaction();
        $stmtCheck = $pdo->prepare("SELECT $col_afiliado FROM afiliados WHERE documento = ?");
        $stmtCheck->execute([$doc]);
        $existe = $stmtCheck->fetch();

        if ($existe) {
            $id_afiliado = $existe[$col_afiliado];
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

// --- LÓGICA 2: REPORTES, ESTADÍSTICAS E INTEGRACIÓN DE AUDITORÍA TOTAL ---
try {
    // A) Datos diarios
    $stmtDineroHoy = $pdo->query("SELECT a.hora, f.documento, f.nombre, f.apellido FROM asistencias a JOIN afiliados f ON a.id_afiliado = f.$col_afiliado WHERE a.fecha = CURDATE() AND a.observaciones LIKE 'Pase Diario%' ORDER BY a.hora DESC");
    $dineroHoy = $stmtDineroHoy->fetchAll();

    $stmtSociosHoy = $pdo->query("SELECT a.hora, f.nombre, f.apellido, a.observaciones FROM asistencias a JOIN afiliados f ON a.id_afiliado = f.$col_afiliado WHERE a.fecha = CURDATE() AND a.observaciones NOT LIKE 'Pase Diario%' ORDER BY a.hora DESC");
    $sociosHoy = $stmtSociosHoy->fetchAll();

    $totalPasesHoy = count($dineroHoy);
    $totalSociosHoy = count($sociosHoy);
    $totalAsistenciasHoy = $totalPasesHoy + $totalSociosHoy;
    
    $porcentajeAforo = min(round(($totalAsistenciasHoy / $capacidadMaximaGym) * 100), 100);
    $totalDineroHoy = $totalPasesHoy * $precioPaseDiario;

    $stmtCajaMes = $pdo->query("SELECT COUNT(*) as total FROM asistencias WHERE MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE()) AND observaciones LIKE 'Pase Diario%'");
    $totalDineroMes = $stmtCajaMes->fetch()['total'] * $precioPaseDiario;

    // B) Métricas Globales reales (CORREGIDO: Se añade consulta de asistencias totales)
    $totalAtletas = $pdo->query("SELECT COUNT(*) FROM afiliados")->fetchColumn();
    $totalAsistencias = $pdo->query("SELECT COUNT(*) FROM asistencias")->fetchColumn();
    
    try { $totalInstructores = $pdo->query("SELECT COUNT(*) FROM instructores")->fetchColumn(); } catch (PDOException $e) { $totalInstructores = 0; }
    try { $totalPlanes = $pdo->query("SELECT COUNT(*) FROM planes")->fetchColumn(); } catch (PDOException $e) { $totalPlanes = 0; }
    
    try {
        $membresiasActivas = $pdo->query("SELECT COUNT(*) FROM membresias WHERE estado = 'Activa'")->fetchColumn();
        $membresiasVencidas = $pdo->query("SELECT COUNT(*) FROM membresias WHERE estado = 'Vencida'")->fetchColumn();
    } catch (PDOException $e) { $membresiasActivas = 0; $membresiasVencidas = 0; }

    try {
        $queryIncomes = "SELECT SUM(p.precio) FROM membresias m JOIN planes p ON m.id_plan = p.$col_plan WHERE m.estado = 'Activa'";
        $ingresoMembresias = floatval($pdo->query($queryIncomes)->fetchColumn());
    } catch (PDOException $e) { $ingresoMembresias = 0; }

    $granTotalEmpresa = $totalDineroHoy + $ingresoMembresias;

} catch (PDOException $e) {
    $mensaje = "<div class='alert alert-danger fw-bold text-center shadow-sm'>Error en Consolidación: " . $e->getMessage() . "</div>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balance General y Taquilla - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #111; color: #fff; font-family: 'Segoe UI', sans-serif; }
        .kings-card { background: #212529; border: 1px solid rgba(255,193,7,0.2); border-radius: 15px; }
        .stat-box { border-left: 4px solid #ffc107; background: #1a1d20; padding: 12px; border-radius: 8px; text-align: center; }
        .stat-box.success { border-left-color: #198754; }
        .stat-box.info { border-left-color: #0dcaf0; }
        .stat-box.danger { border-left-color: #dc3545; }
        .mini-stat { background: #151719; border: 1px solid #333; border-radius: 8px; padding: 10px; text-align: center; }
        
        @media print {
            body { background: #fff !important; color: #000 !important; }
            .kings-card, .stat-box, .mini-stat { background: #fff !important; color: #000 !important; border: 1px solid #000 !important; box-shadow: none !important; }
            .no-print, .btn, .nav-tabs { display: none !important; }
            h1, h4, h5, span, p, small, td, th { color: #000 !important; }
            .progress { border: 1px solid #000; }
        }
    </style>
</head>
<body>

    <div class="container py-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bolder text-white mb-0"><i class="bi bi-graph-up-arrow text-success me-2"></i> KINGS REPORTS & FINANZAS</h1>
                <p class="text-secondary mb-0">Auditoría contable, control de pases diarios y analítica de planta centralizada.</p>
            </div>
            <div class="no-print">
                <button onclick="window.print();" class="btn btn-success rounded-pill btn-sm px-3 fw-bold me-1">
                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Generar PDF
                </button>
                <a href="../index.php" class="btn btn-outline-warning rounded-pill btn-sm px-3"><i class="bi bi-arrow-left"></i> Panel</a>
            </div>
        </div>

        <?php echo $mensaje; ?>

        <h5 class="text-warning fw-bold mb-2"><i class="bi bi-cash-coin me-1"></i> Balance de Flujos de Efectivo</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="stat-box success">
                    <small class="text-secondary d-block fw-bold text-uppercase small">Pases de Hoy</small>
                    <span class="text-success fw-bolder fs-4">$<?php echo number_format($totalDineroHoy); ?></span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-box">
                    <small class="text-secondary d-block fw-bold text-uppercase small">Mes Pases Casuales</small>
                    <span class="text-warning fw-bolder fs-4">$<?php echo number_format($totalDineroMes); ?></span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-box info">
                    <small class="text-secondary d-block fw-bold text-uppercase small">Cartera Activa Planes</small>
                    <span class="text-info fw-bolder fs-4">$<?php echo number_format($ingresoMembresias); ?></span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-box success" style="border-left-color: #ff9800;">
                    <small class="text-secondary d-block fw-bold text-uppercase small">Caja Global Activa</small>
                    <span class="fw-bolder fs-4" style="color: #ff9800;">$<?php echo number_format($granTotalEmpresa); ?></span>
                </div>
            </div>
        </div>

        <h5 class="text-warning fw-bold mb-2"><i class="bi bi-calculator me-1"></i> Volúmenes de Activos y Contratos</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-2 col-4">
                <div class="mini-stat">
                    <small class="text-muted d-block small fw-bold">Atletas</small>
                    <span class="fs-5 fw-bold text-white"><?php echo $totalAtletas; ?></span>
                </div>
            </div>
            <div class="col-md-2 col-4">
                <div class="mini-stat">
                    <small class="text-muted d-block small fw-bold">Instructores</small>
                    <span class="fs-5 fw-bold text-white"><?php echo $totalInstructores; ?></span>
                </div>
            </div>
            <div class="col-md-2 col-4">
                <div class="mini-stat">
                    <small class="text-muted d-block small fw-bold">Planes Gym</small>
                    <span class="fs-5 fw-bold text-white"><?php echo $totalPlanes; ?></span>
                </div>
            </div>
            <div class="col-md-2 col-4">
                <div class="mini-stat">
                    <small class="text-muted d-block small fw-bold">Tráfico Total</small>
                    <span class="fs-5 fw-bold text-white"><?php echo $totalAsistencias; ?></span>
                </div>
            </div>
            <div class="col-md-2 col-4">
                <div class="mini-stat" style="border-color: #198754;">
                    <small class="text-success d-block small fw-bold">Suscritos</small>
                    <span class="fs-5 fw-bold text-success"><?php echo $membresiasActivas; ?></span>
                </div>
            </div>
            <div class="col-md-2 col-4">
                <div class="mini-stat" style="border-color: #dc3545;">
                    <small class="text-danger d-block small fw-bold">Vencidos</small>
                    <span class="fs-5 fw-bold text-danger"><?php echo $membresiasVencidas; ?></span>
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
                            <input type="text" name="documento" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Documento Identidad" required autocomplete="off">
                        </div>
                        <div class="col-6">
                            <input type="text" name="nombre" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Nombre" required autocomplete="off">
                        </div>
                        <div class="col-6">
                            <input type="text" name="apellido" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Apellido" required autocomplete="off">
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-warning w-100 fw-bold rounded-pill text-dark">
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
                            <h5 class="text-warning fw-bold m-0"><i class="bi bi-speedometer2 me-1"></i> Ocupación de Sala Hoy</h5>
                            <span class="badge bg-warning text-dark fw-bold"><?php echo $porcentajeAforo; ?>%</span>
                        </div>
                        <div class="progress bg-dark mb-3" style="height: 10px; border: 1px solid #333;">
                            <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo $porcentajeAforo; ?>%"></div>
                        </div>
                    </div>

                    <div class="mx-auto" style="width: 100%; max-width: 200px;">
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
                        <i class="bi bi-person-walking text-info me-1"></i> Tráfico de Puerta (Socios Hoy)
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
                labels: ['Pases Casuales', 'Socios Activos'],
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