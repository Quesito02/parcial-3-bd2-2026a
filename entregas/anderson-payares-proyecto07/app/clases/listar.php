<?php
// app/clases/listar.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../conexion.php'; 

$mensaje = "";

$clases_programadas = [
    1 => ['disciplina' => 'Spinning Pro', 'instructor' => 'Carlos Mendoza', 'hora' => '06:00 AM', 'cupo_max' => 20, 'icono' => 'bi-bicycle', 'color' => 'text-warning'],
    2 => ['disciplina' => 'CrossFit Kings', 'instructor' => 'Diana Robledo', 'hora' => '08:00 AM', 'cupo_max' => 15, 'icono' => 'bi-lightning-fill', 'color' => 'text-danger'],
    3 => ['disciplina' => 'Boxeo de Sombra', 'instructor' => 'Jairo Ruiz', 'hora' => '06:30 PM', 'cupo_max' => 12, 'icono' => 'bi-glove', 'color' => 'text-info'],
    4 => ['disciplina' => 'Funcional / HIIT', 'instructor' => 'Laura Beltrán', 'hora' => '07:30 PM', 'cupo_max' => 25, 'icono' => 'bi-heart-pulse-fill', 'color' => 'text-success'],
];

if (!isset($_SESSION)) { session_start(); }
if (!isset($_SESSION['reservas_simuladas'])) { $_SESSION['reservas_simuladas'] = [1 => 5, 2 => 14, 3 => 8, 4 => 19]; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_clase'])) {
    $id_clase = intval($_POST['id_clase']);
    if (isset($clases_programadas[$id_clase])) {
        $max = $clases_programadas[$id_clase]['cupo_max'];
        $actual = $_SESSION['reservas_simuladas'][$id_clase];
        if ($actual < $max) {
            $_SESSION['reservas_simuladas'][$id_clase]++;
            $mensaje = "<div class='alert alert-success fw-bold text-center shadow-sm'><i class='bi bi-bookmark-check-fill me-2'></i> ¡Cupo asegurado para " . $clases_programadas[$id_clase]['disciplina'] . "!</div>";
        } else {
            $mensaje = "<div class='alert alert-danger fw-bold text-center shadow-sm'>❌ Clase sin cupos disponibles.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clases Grupales - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #111; color: #fff; }
        .class-card { background: #1a1d20; border: 1px solid #333; border-radius: 12px; transition: 0.3s; }
        .class-card:hover { border-color: #ffc107; transform: translateY(-2px); }
        .capacity-bar { height: 6px; background-color: #111; border-radius: 3px; overflow: hidden; }
    </style>
</head>
<body>

    <div class="container py-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bolder text-white mb-0"><i class="bi bi-calendar3 text-warning me-2"></i> CLASES GRUPALES</h1>
                <p class="text-secondary mb-0">Horarios y control de aforo por disciplinas.</p>
            </div>
            <div>
                <a href="nueva.php" class="btn btn-warning rounded-pill btn-sm px-3 fw-bold text-dark me-1"><i class="bi bi-calendar-plus-fill me-1"></i> Nueva Clase</a>
                <a href="../index.php" class="btn btn-outline-warning rounded-pill btn-sm px-3"><i class="bi bi-house-door"></i> Inicio</a>
            </div>
        </div>

        <?php echo $mensaje; ?>

        <div class="row g-4">
            <?php foreach($clases_programadas as $id => $clase): 
                $ocupados = $_SESSION['reservas_simuladas'][$id]; $maximo = $clase['cupo_max']; $disponibles = $maximo - $ocupados; $porcentaje = round(($ocupados / $maximo) * 100);
                $colorBarra = ($porcentaje >= 90) ? 'bg-danger' : (($porcentaje >= 70) ? 'bg-warning' : 'bg-success');
            ?>
                <div class="col-lg-6 col-12">
                    <div class="class-card p-4 h-100 d-flex flex-column justify-content-between shadow-sm">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="bi <?php echo $clase['icono'] . ' ' . $clase['color']; ?> fs-2 me-3"></i>
                                    <div>
                                        <h4 class="text-white fw-bold mb-0"><?php echo $clase['disciplina']; ?></h4>
                                        <small class="text-muted">Coach: <?php echo $clase['instructor']; ?></small>
                                    </div>
                                </div>
                                <span class="badge bg-dark border border-secondary text-warning fs-6"><i class="bi bi-clock me-1"></i> <?php echo $clase['hora']; ?></span>
                            </div>
                            <hr class="border-secondary">
                        </div>
                        <div class="mt-2">
                            <div class="d-flex justify-content-between text-muted small mb-1"><span>Ocupación de Sala</span><span class="fw-bold text-white"><?php echo $ocupados; ?> / <?php echo $maximo; ?> Cupos</span></div>
                            <div class="capacity-bar mb-4"><div class="progress-bar <?php echo $colorBarra; ?>" style="width: <?php echo $porcentaje; ?>%; height: 100%;"></div></div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary" style="border-style: dashed !important;">
                                <small class="text-muted"><strong class="text-white"><?php echo $disponibles; ?> libres</strong></small>
                                <form action="listar.php" method="POST" class="m-0">
                                    <input type="hidden" name="id_clase" value="<?php echo $id; ?>">
                                    <button type="submit" class="btn btn-warning btn-sm rounded-pill px-4 fw-bold text-dark shadow-sm" <?php echo ($disponibles <= 0) ? 'disabled' : ''; ?>><i class="bi bi-bookmark-plus-fill me-1"></i> Reservar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>