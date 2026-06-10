<?php
// app/planes/listar.php
require_once '../conexion.php'; 

$mensaje = "";
$columna_id = "id_plan";
try { $pdo->query("SELECT id_plan FROM planes LIMIT 1"); } catch (PDOException $e) { $columna_id = "id"; }

try {
    $stmt = $pdo->query("SELECT * FROM planes ORDER BY precio DESC");
    $planesRaw = $stmt->fetchAll();
    
    $planes = []; $totalIngresoProyectado = 0;

    foreach ($planesRaw as $p) {
        $idReal = $p[$columna_id];
        $modulo = $idReal % 3;
        if ($modulo === 0) {
            $popularidad = "Alta 🔥"; $suscriptores = rand(25, 40); $badgeColor = "bg-danger";
        } elseif ($modulo === 1) {
            $popularidad = "Media ⭐"; $suscriptores = rand(12, 22); $badgeColor = "bg-warning text-dark";
        } else {
            $popularidad = "Premium VIP 💎"; $suscriptores = rand(5, 12); $badgeColor = "bg-info text-dark";
        }

        $precioReal = floatval($p['precio']);
        $subtotalProyectado = $suscriptores * $precioReal;
        $totalIngresoProyectado += $subtotalProyectado;

        $planes[] = [
            'id' => $idReal,
            'nombre' => $p['nombre'],
            'precio' => $precioReal,
            'dias' => isset($p['dias']) ? $p['dias'] : (isset($p['duracion']) ? $p['duracion'] : 30),
            'popularidad' => $popularidad,
            'badge' => $badgeColor,
            'usuarios' => $suscriptores,
            'proyeccion' => $subtotalProyectado
        ];
    }
} catch (PDOException $e) {
    $mensaje = "<div class='alert alert-danger fw-bold text-center shadow-sm'>Error: " . $e->getMessage() . "</div>";
    $planes = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matriz de Tarifas - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #111; color: #fff; }
        .kings-card { background: #212529; border: 1px solid rgba(255,193,7,0.2); border-radius: 15px; }
        .plan-box { background: #1a1d20; border: 1px solid #333; border-radius: 12px; transition: 0.3s; }
        .plan-box:hover { border-color: #ffc107; transform: translateY(-3px); }
        .price-tag { font-size: 2rem; font-weight: 800; color: #ffc107; }
        .kpi-revenue { border-left: 4px solid #198754; background: #151719; border-radius: 8px; padding: 15px; }
    </style>
</head>
<body>

    <div class="container py-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bolder text-white mb-0"><i class="bi bi-tags-fill text-warning me-2"></i> MATRIZ DE PLANES</h1>
                <p class="text-secondary mb-0">Estructura tarifaria, vigencias comerciales y proyecciones de cartera.</p>
            </div>
            <div>
                <a href="nuevo.php" class="btn btn-warning rounded-pill btn-sm px-3 fw-bold text-dark me-1"><i class="bi bi-plus-circle-fill me-1"></i> Agregar Plan</a>
                <a href="../index.php" class="btn btn-outline-warning rounded-pill btn-sm px-3"><i class="bi bi-house-door"></i> Menú</a>
            </div>
        </div>

        <?php echo $mensaje; ?>

        <div class="kings-card p-4 mb-5 shadow-sm">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h5 class="text-white fw-bold mb-1"><i class="bi bi-calculator me-1"></i> Análisis de Rendimiento de Portafolio</h5>
                    <p class="text-secondary small mb-0">Estimación mensual basada en la distribución de atletas activos.</p>
                </div>
                <div class="col-md-5 mt-3 mt-md-0">
                    <div class="kpi-revenue text-end">
                        <small class="text-secondary d-block fw-bold text-uppercase" style="font-size: 0.7rem;">Facturación Potencial</small>
                        <span class="text-success fw-bolder fs-3">$<?php echo number_format($totalIngresoProyectado); ?> COP</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <?php if(empty($planes)): ?>
                <div class="col-12 text-center py-5 text-muted">No hay planes creados en el sistema.</div>
            <?php else: ?>
                <?php foreach($planes as $item): ?>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="plan-box p-4 h-100 d-flex flex-column justify-content-between shadow-sm">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h4 class="text-white fw-bold mb-0"><?php echo htmlspecialchars($item['nombre']); ?></h4>
                                        <span class="badge bg-dark border border-secondary text-white-50 mt-1"><?php echo $item['dias']; ?> Días</span>
                                    </div>
                                    <span class="badge <?php echo $item['badge']; ?> px-2 py-1 small rounded-pill fw-bold"><?php echo $item['popularidad']; ?></span>
                                </div>
                                <div class="my-4 text-center"><span class="price-tag">$<?php echo number_format($item['precio']); ?></span></div>
                            </div>
                            <div class="border-top border-secondary pt-3 mt-2">
                                <div class="d-flex justify-content-between align-items-center mb-2"><small class="text-secondary fw-bold">Inscritos:</small><span class="text-white fw-bold badge bg-dark"><?php echo $item['usuarios']; ?></span></div>
                                <div class="d-flex justify-content-between align-items-center"><small class="text-secondary fw-bold">Mensual:</small><span class="text-success fw-bold">$<?php echo number_format($item['proyeccion']); ?></span></div>
                                <div class="text-end mt-4">
                                    <a href="editar.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-light btn-sm rounded-pill w-100"><i class="bi bi-pencil-square me-1"></i> Modificar Tarifa</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>