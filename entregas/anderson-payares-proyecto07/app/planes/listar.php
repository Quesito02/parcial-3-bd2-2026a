<?php
// app/planes/listar.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../conexion.php'; // Saliendo a la raíz de la carpeta app

$mensaje = "";

// 🛠️ DETECTOR AUTOMÁTICO DE COLUMNAS PARA PLANES (id_plan vs id)
$columna_id = "id_plan";
try {
    $pdo->query("SELECT id_plan FROM planes LIMIT 1");
} catch (PDOException $e) {
    $columna_id = "id";
}

try {
    // 1. 🔍 CONSULTA REAL: Cuenta cuántas membresías reales están amarradas a cada plan de gym
    $query = "SELECT p.*, COUNT(m.id_afiliado) as total_suscriptores 
              FROM planes p 
              LEFT JOIN membresias m ON p.$columna_id = m.id_plan 
              GROUP BY p.$columna_id 
              ORDER BY p.precio DESC";
                  
    $stmt = $pdo->query($query);
    $planesRaw = $stmt->fetchAll();
    
} catch (PDOException $e) {
    // Fallback por si en tu tabla membresias la columna del plan se llama diferente a id_plan
    try {
        $query = "SELECT p.*, 0 as total_suscriptores FROM planes p ORDER BY p.precio DESC";
        $stmt = $pdo->query($query);
        $planesRaw = $stmt->fetchAll();
        $mensaje = "<div class='alert alert-warning text-center small mb-3'>Nota: Mostrando planes con saldo cero. Revisa si en tu tabla 'membresias' la columna de unión se llama 'id_plan'.</div>";
    } catch (PDOException $e2) {
        $mensaje = "<div class='alert alert-danger fw-bold text-center shadow-sm'>Error: " . $e2->getMessage() . "</div>";
        $planesRaw = [];
    }
}

$planes = [];
$totalIngresoProyectado = 0;

// 2. Procesar y enriquecer los planes con analítica de negocio basada en datos REALES
foreach ($planesRaw as $p) {
    $idReal = $p[$columna_id];
    $suscriptores = intval($p['total_suscriptores']);
    
    // Clasificación de popularidad inteligente basada en la cantidad REAL de inscritos
    if ($suscriptores >= 15) {
        $popularidad = "Alta 🔥";
        $badgeColor = "bg-danger";
    } elseif ($suscriptores >= 5) {
        $popularidad = "Media ⭐";
        $badgeColor = "bg-warning text-dark";
    } else {
        $popularidad = "VIP / Nuevo 💎";
        $badgeColor = "bg-info text-dark";
    }

    // Calcular facturación real del portafolio
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
                <p class="text-secondary mb-0">Estructura tarifaria, vigencias comerciales y proyecciones de cartera real.</p>
            </div>
            <div>
                <a href="nuevo.php" class="btn btn-warning rounded-pill btn-sm px-3 fw-bold text-dark me-1"><i class="bi bi-plus-circle-fill me-1"></i> Nuevo Plan</a>
                <a href="../index.php" class="btn btn-outline-warning rounded-pill btn-sm px-3"><i class="bi bi-house-door"></i> Menú</a>
            </div>
        </div>

        <?php echo $mensaje; ?>

        <div class="kings-card p-4 mb-5 shadow-sm">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h5 class="text-white fw-bold mb-1"><i class="bi bi-calculator me-1"></i> Análisis de Rendimiento de Portafolio Real</h5>
                    <p class="text-secondary small mb-0">Cálculo exacto del recaudo potencial de caja según la cantidad de afiliados con contratos asignados.</p>
                </div>
                <div class="col-md-5 mt-3 mt-md-0">
                    <div class="kpi-revenue text-end">
                        <small class="text-secondary d-block fw-bold text-uppercase" style="font-size: 0.7rem;">Caja Potencial Vinculada</small>
                        <span class="text-success fw-bolder fs-3">$<?php echo number_format($totalIngresoProyectado); ?> COP</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <?php if(empty($planes)): ?>
                <div class="col-12 text-center py-5 text-muted">No hay planes ni tarifas estructuradas en el sistema.</div>
            <?php else: ?>
                <?php foreach($planes as $item): ?>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="plan-box p-4 h-100 d-flex flex-column justify-content-between shadow-sm">
                            
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h4 class="text-white fw-bold mb-0"><?php echo htmlspecialchars($item['nombre']); ?></h4>
                                        <span class="badge bg-dark border border-secondary text-white-50 mt-1"><?php echo $item['dias']; ?> Días de Vigencia</span>
                                    </div>
                                    <span class="badge <?php echo $item['badge']; ?> px-2 py-1 small rounded-pill fw-bold"><?php echo $item['popularidad']; ?></span>
                                </div>

                                <div class="my-4 text-center">
                                    <span class="price-tag">$<?php echo number_format($item['precio']); ?></span>
                                    <small class="text-muted d-block mt-1">Cobro por Ciclo Contractual</small>
                                </div>
                            </div>

                            <div class="border-top border-secondary pt-3 mt-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-secondary fw-bold">Atletas con este Plan:</small>
                                    <span class="text-white fw-bold badge bg-dark"><?php echo $item['usuarios']; ?> Activos</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-secondary fw-bold">Retorno Esperado:</small>
                                    <span class="text-success fw-bold">$<?php echo number_format($item['proyeccion']); ?></span>
                                </div>

                                <div class="text-end mt-4">
                                    <a href="editar.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-light btn-sm rounded-pill w-100">
                                        <i class="bi bi-pencil-square me-1"></i> Modificar Tarifa
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>