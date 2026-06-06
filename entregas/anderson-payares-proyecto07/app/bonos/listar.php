<?php
// app/bonos/listar.php
require_once '../conexion.php';

// Consultar los bonos junto con el nombre del afiliado
$sql = "SELECT b.*, a.nombre, a.apellido, a.documento 
        FROM bonos b 
        INNER JOIN afiliados a ON b.id_afiliado = a.id_afiliado 
        ORDER BY b.fecha_emision DESC";
$stmt = $pdo->query($sql);
$bonos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>King Rewards - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .reward-card { background: linear-gradient(135deg, #212529 0%, #000000 100%); color: white; border: 1px solid #ffc107; border-radius: 15px; transition: transform 0.2s; }
        .reward-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(255, 193, 7, 0.2); }
        .badge-disponible { background-color: #198754; color: white; }
        .badge-canjeado { background-color: #6c757d; color: white; }
    </style>
</head>
<body class="p-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bolder text-dark"><i class="bi bi-gift-fill text-warning me-2"></i> King Rewards</h2>
            <div>
                <a href="../index.php" class="btn btn-outline-dark rounded-pill px-4 me-2"><i class="bi bi-house-door-fill"></i> Inicio</a>
                <a href="nuevo.php" class="btn btn-warning fw-bold rounded-pill px-4"><i class="bi bi-plus-circle-fill"></i> Otorgar Bono</a>
            </div>
        </div>

        <div class="row">
            <?php if (count($bonos) > 0): ?>
                <?php foreach ($bonos as $b): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card reward-card h-100 p-3 shadow-sm">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="fw-bold text-warning mb-0"><i class="bi bi-star-fill me-1"></i> <?php echo htmlspecialchars($b['tipo_bono']); ?></h5>
                                <span class="badge <?php echo $b['estado'] === 'Disponible' ? 'badge-disponible' : 'badge-canjeado'; ?> rounded-pill px-3 py-2">
                                    <?php echo $b['estado']; ?>
                                </span>
                            </div>
                            <h3 class="fw-bolder mb-1 text-white">
                                <?php echo $b['tipo_bono'] === 'Descuento $' ? '$' . number_format($b['cantidad'], 0) : $b['cantidad'] . ' Días'; ?>
                            </h3>
                            <p class="text-secondary small mb-3">Motivo: <strong><?php echo htmlspecialchars($b['motivo']); ?></strong></p>
                            
                            <div class="mt-auto pt-3 border-top border-secondary">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-circle fs-4 me-2 text-warning"></i>
                                    <div>
                                        <p class="mb-0 fw-bold fs-6" style="line-height: 1.1;"><?php echo htmlspecialchars($b['nombre'] . ' ' . $b['apellido']); ?></p>
                                        <small class="text-muted">Doc: <?php echo htmlspecialchars($b['documento']); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-trophy text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 text-secondary">Aún no hay bonos registrados</h4>
                    <p class="text-muted">Inicia una actividad o reto para premiar a tus atletas.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>