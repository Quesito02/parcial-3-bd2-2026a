<?php
// app/afiliados/ver.php
// 🛠️ ACTIVAR DETECTOR DE ERRORES (Para ver el problema real en pantalla)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../conexion.php';

$mensaje = "";
$id_afiliado = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    // 1. INTENTAR BUSCAR POR 'id_afiliado' O POR 'id' TRADICIONAL
    $atleta = false;
    try {
        $stmt = $pdo->prepare("SELECT * FROM afiliados WHERE id_afiliado = ?");
        $stmt->execute([$id_afiliado]);
        $atleta = $stmt->fetch();
    } catch (PDOException $e) {
        // Si falla, es porque la columna se llama 'id'
        $stmt = $pdo->prepare("SELECT * FROM afiliados WHERE id = ?");
        $stmt->execute([$id_afiliado]);
        $atleta = $stmt->fetch();
    }

    if (!$atleta) {
        die("<div class='container mt-5'><div class='alert alert-danger text-center fw-bold'>⚠️ Atleta con ID ($id_afiliado) no encontrado en la base de datos.</div></div>");
    }

    // Mapeo automático de nombres de columnas por si acaso
    $realId = isset($atleta['id_afiliado']) ? $atleta['id_afiliado'] : $atleta['id'];
    $objetivo = isset($atleta['objetivo']) ? $atleta['objetivo'] : (isset($atleta['objective']) ? $atleta['objective'] : 'Mantenimiento');
    $somatotipo = isset($atleta['somatotipo']) ? $atleta['somatotipo'] : 'Mesomorfo';

    // 2. CONTAR ASISTENCIAS REALES
    $totalAsistencias = 0;
    try {
        $stmtPoints = $pdo->prepare("SELECT COUNT(*) as total FROM asistencias WHERE id_afiliado = ?");
        $stmtPoints->execute([$realId]);
        $totalAsistencias = $stmtPoints->fetch()['total'];
    } catch (PDOException $e) {
        // Fallback por si en asistencias se llama 'id_atleta' o 'id'
        try {
            $stmtPoints = $pdo->prepare("SELECT COUNT(*) as total FROM asistencias WHERE id_afiliado = (SELECT documento FROM afiliados WHERE id_afiliado = ? OR id = ?)");
            $stmtPoints->execute([$id_afiliado, $id_afiliado]);
            $totalAsistencias = $stmtPoints->fetch()['total'];
        } catch (PDOException $e2) {
            $totalAsistencias = 0; 
        }
    }
    
    $kingPoints = $totalAsistencias * 15;

    // 3. ALGORITMO DE RUTINAS AUTOMÁTICAS
    $rutinaRecomendada = "Acondicionamiento General Funcional: 3 series de 12 repeticiones enfocadas en la resistencia muscular general, simetría corporal y fortalecimiento del core.";
    $consejoNutricional = "Alimentación balanceada libre de ultraprocesados. Mantener un consumo constante de agua de mínimo 3 litros diarios.";

    if (strpos(strtolower($somatotipo), 'ecto') !== false) {
        $rutinaRecomendada = "Hipertrofia de Fuerza Semipesada: 4 series de 6-8 repeticiones. Enfoque estricto en ejercicios compuestos (Sentadillas, Press de Banca, Peso Muerto). Descansos largos de 90 a 120 segundos.";
        $consejoNutricional = "Superávit calórico agresivo. Alta densidad de carbohidratos complejos (arroz, avena) y grasas saludables. Limitar el ejercicio cardiovascular.";
    } elseif (strpos(strtolower($somatotipo), 'endo') !== false) {
        $rutinaRecomendada = "Entrenamiento Metabólico Híbrido: Supeseries de fuerza seguidas de ráfagas de cardio (HIIT). 4 series de 12-15 repeticiones con descansos cortos de 45 segundos.";
        $consejoNutricional = "Déficit calórico controlado. Alta ingesta de proteínas magras y verduras verdes. Reducir carbohidratos simples.";
    } elseif (strpos(strtolower($somatotipo), 'meso') !== false) {
        $rutinaRecomendada = "Periodización Estándar King: Combinación de fuerza y aislamiento estético. 4 series de 10 repeticiones controlando la fase excéntrica (bajada) en 3 segundos.";
        $consejoNutricional = "Dieta normocalórica o ciclado de carbohidratos según días de entrenamiento pesado. Balance óptimo de macronutrientes.";
    }

} catch (Exception $e) {
    $mensaje = "<div class='alert alert-danger fw-bold text-center'>Error general: " . $e->getMessage() . "</div>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Atleta - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=2.0">
    <style>
        body { background-color: #111 !important; color: #fff !important; }
        .kings-card { background: #212529 !important; border: 1px solid rgba(255,193,7,0.2) !important; border-radius: 15px !important; }
        .badge-points { background: linear-gradient(45deg, #ffc107, #ff9800) !important; color: #000 !important; font-weight: bold; font-size: 1.1rem; }
        .box-rutina { background-color: #151719 !important; border-left: 4px solid #ffc107 !important; border-radius: 5px; padding: 15px; }
        .box-dieta { background-color: #151719 !important; border-left: 4px solid #198754 !important; border-radius: 5px; padding: 15px; }
    </style>
</head>
<body>

    <div class="container py-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bolder text-white mb-0"><i class="bi bi-person-badge text-warning me-2"></i> FICHA DEPORTIVA</h1>
                <p class="text-secondary mb-0">Análisis inteligente del atleta.</p>
            </div>
            <a href="listar.php" class="btn btn-outline-warning rounded-pill btn-sm px-3"><i class="bi bi-arrow-left"></i> Volver</a>
        </div>

        <?php echo $mensaje; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="kings-card p-4 text-center">
                    <i class="bi bi-person-circle text-secondary" style="font-size: 5rem;"></i>
                    <h3 class="fw-bold text-warning mt-2 mb-0"><?php echo htmlspecialchars($atleta['nombre'] . " " . $atleta['apellido']); ?></h3>
                    <p class="text-secondary small mb-3">Doc: <?php echo htmlspecialchars($atleta['documento']); ?></p>

                    <div class="badge badge-points w-100 py-2 rounded-pill shadow mb-2">
                        <i class="bi bi-trophy-fill me-1"></i> <?php echo $kingPoints; ?> KING POINTS
                    </div>
                    <small class="text-muted d-block">Calculados sobre <?php echo $totalAsistencias; ?> asistencias.</small>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="kings-card p-4 h-100">
                    <h4 class="text-white fw-bold mb-4 border-bottom border-secondary pb-2"><i class="bi bi-cpu text-warning me-2"></i> Plan Automatizado</h4>
                    
                    <div class="row g-3">
                        <div class="col-6">
                            <small class="text-secondary d-block fw-bold">Somatotipo</small>
                            <span class="text-warning fw-bold fs-5"><?php echo htmlspecialchars($somatotipo); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-secondary d-block fw-bold">Objetivo</small>
                            <span class="text-info fw-bold fs-5"><?php echo htmlspecialchars($objetivo); ?></span>
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="text-warning fw-bold"><i class="bi bi-journal-check me-1"></i> Entrenamiento Recomendado:</h6>
                            <div class="box-rutina text-white-50">
                                <?php echo $rutinaRecomendada; ?>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="text-success fw-bold"><i class="bi bi-egg-fried me-1"></i> Nutrición Base:</h6>
                            <div class="box-dieta text-white-50">
                                <?php echo $consejoNutricional; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>