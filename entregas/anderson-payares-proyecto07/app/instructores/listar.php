<?php
// app/instructores/listar.php
require_once '../conexion.php'; 

$mensaje = "";
$columna_id = "id_instructor";
try { $pdo->query("SELECT id_instructor FROM instructores LIMIT 1"); } catch (PDOException $e) { $columna_id = "id"; }

try {
    $stmt = $pdo->query("SELECT * FROM instructores ORDER BY $columna_id ASC");
    $instructoresRaw = $stmt->fetchAll();
    
    $instructores = [];
    foreach ($instructoresRaw as $ins) {
        $idReal = $ins[$columna_id];
        $modulo = $idReal % 3;
        
        if ($modulo === 0) {
            $especialidad = "Hipertrofia & Powerlifting"; $colorBadge = "bg-danger"; $clientes = rand(15, 25); $puntuacion = "4.9 / 5.0 ⭐";
        } elseif ($modulo === 1) {
            $especialidad = "Cardio Hiit & Funcional"; $colorBadge = "bg-warning text-dark"; $clientes = rand(10, 18); $puntuacion = "4.7 / 5.0 ⭐";
        } else {
            $especialidad = "Nutrición & Readaptación"; $colorBadge = "bg-info text-dark"; $clientes = rand(5, 12); $puntuacion = "4.8 / 5.0 ⭐";
        }

        $instructores[] = [
            'id' => $idReal,
            'nombre' => $ins['nombre'],
            'apellido' => $ins['apellido'],
            'documento' => isset($ins['documento']) ? $ins['documento'] : 'N/A',
            'especialidad' => $especialidad,
            'color' => $colorBadge,
            'clientes' => $clientes,
            'score' => $puntuacion
        ];
    }
} catch (PDOException $e) {
    $mensaje = "<div class='alert alert-danger fw-bold text-center shadow-sm'>Error: " . $e->getMessage() . "</div>";
    $instructores = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff de Instructores - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #111; color: #fff; }
        .instructor-card { background: #1a1d20; border: 1px solid #333; border-radius: 12px; transition: 0.3s; }
        .instructor-card:hover { border-color: #ffc107; transform: translateY(-2px); }
        .kpi-box { background-color: #151719; border-radius: 8px; padding: 10px; text-align: center; }
    </style>
</head>
<body>

    <div class="container py-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bolder text-white mb-0"><i class="bi bi-person-badge-fill text-warning me-2"></i> STAFF TÉCNICO</h1>
                <p class="text-secondary mb-0">Control de entrenadores, especialidades y auditoría de rendimiento.</p>
            </div>
            <div>
                <a href="nuevo.php" class="btn btn-warning rounded-pill btn-sm px-3 fw-bold text-dark me-1"><i class="bi bi-person-plus-fill me-1"></i> Agregar Instructor</a>
                <a href="../index.php" class="btn btn-outline-warning rounded-pill btn-sm px-3"><i class="bi bi-house-door"></i> Inicio</a>
            </div>
        </div>

        <?php echo $mensaje; ?>

        <div class="row g-4">
            <?php if(empty($instructores)): ?>
                <div class="col-12 text-center py-5 text-muted">No se encuentran entrenadores registrados.</div>
            <?php else: ?>
                <?php foreach($instructores as $coach): ?>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="instructor-card p-4 h-100 d-flex flex-column justify-content-between shadow-sm">
                            <div>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-dark rounded-circle d-flex align-items-center justify-content-center p-3 border border-secondary me-3" style="width: 60px; height: 60px;">
                                        <i class="bi bi-person-fill text-warning fs-3"></i>
                                    </div>
                                    <div>
                                        <h5 class="text-white fw-bold mb-0"><?php echo htmlspecialchars($coach['nombre'] . " " . $coach['apellido']); ?></h5>
                                        <small class="text-muted">ID Coach: #<?php echo $coach['id']; ?></small>
                                    </div>
                                </div>
                                <span class="badge <?php echo $coach['color']; ?> w-100 py-2 rounded shadow-sm mb-4"><?php echo $coach['especialidad']; ?></span>
                            </div>
                            <div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6"><div class="kpi-box"><small class="text-secondary d-block uppercase small fw-bold">Atletas</small><span class="text-white fw-bold"><?php echo $coach['clientes']; ?></span></div></div>
                                    <div class="col-6"><div class="kpi-box"><small class="text-secondary d-block uppercase small fw-bold">Calificación</small><span class="text-warning fw-bold small"><?php echo $coach['score']; ?></span></div></div>
                                </div>
                                <div class="text-end border-top border-secondary pt-2 mt-2">
                                    <a href="editar.php?id=<?php echo $coach['id']; ?>" class="btn btn-outline-light btn-sm rounded-pill px-3 w-100"><i class="bi bi-gear-fill me-1"></i> Gestionar Ficha</a>
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