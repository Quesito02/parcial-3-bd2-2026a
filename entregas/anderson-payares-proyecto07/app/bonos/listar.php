<?php
// app/bonos/listar.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../conexion.php';

$mensaje = "";

// Catálogo Oficial de Premios (Se usa para validar en el Servidor)
$premios = [
    1 => ['nombre' => 'Agua Mineral King (600ml)', 'puntos' => 30, 'icono' => 'bi-droplet-fill', 'color' => 'text-info'],
    2 => ['nombre' => 'Bebida Energizante Monster', 'puntos' => 60, 'icono' => 'bi-lightning-charge-fill', 'color' => 'text-warning'],
    3 => ['nombre' => 'Camiseta Oficial Gym Kings', 'puntos' => 150, 'icono' => 'bi-shield-shaded', 'color' => 'text-danger'],
    4 => ['nombre' => 'Mes de Membresía Gratis', 'puntos' => 500, 'icono' => 'bi-gem', 'color' => 'text-success'],
];

// 🛠️ DETECTOR AUTOMÁTICO DE COLUMNAS (id vs id_afiliado)
$columna_id = "id_afiliado";
try {
    $pdo->query("SELECT id_afiliado FROM afiliados LIMIT 1");
} catch (PDOException $e) {
    $columna_id = "id";
}

// --- LÓGICA 1: PROCESAR EL CANJE REAL EN LA BASE DE DATOS (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_canjear'])) {
    $id_atleta = intval($_POST['id_afiliado']);
    $id_premio = intval($_POST['id_premio']);
    
    if (isset($premios[$id_premio])) {
        $premioSeleccionado = $premios[$id_premio];
        $nombre_premio = $premioSeleccionado['nombre'];
        $puntos_premio = $premioSeleccionado['puntos'];
        
        // A) Calcular los puntos reales actuales antes de gastar (Seguridad)
        $stmtCheck = $pdo->prepare("SELECT observaciones FROM asistencias WHERE id_afiliado = ?");
        $stmtCheck->execute([$id_atleta]);
        $puntos_actuales = 0;
        
        while($row = $stmtCheck->fetch()) {
            if (strpos($row['observaciones'], 'CANJE:') !== false) {
                // Si es un canje, extrae los puntos usando expresiones regulares y los resta
                if (preg_match('/-(\d+) PTS/', $row['observaciones'], $matches)) {
                    $puntos_actuales -= intval($matches[1]);
                }
            } elseif (strpos($row['observaciones'], 'Pase Diario') !== false) {
                // Los pases diarios no acumulan puntos
            } else {
                $puntos_actuales += 15; // Asistencia normal suma 15
            }
        }
        
        // B) Si el atleta tiene los puntos suficientes, guardamos el canje en la BD
        if ($puntos_actuales >= $puntos_premio) {
            $glosa_canje = "CANJE: " . $nombre_premio . " | -" . $puntos_premio . " PTS";
            
            $stmtIns = $pdo->prepare("INSERT INTO asistencias (id_afiliado, fecha, hora, observaciones) VALUES (?, CURDATE(), CURTIME(), ?)");
            $stmtIns->execute([$id_atleta, $glosa_canje]);
            
            $mensaje = "<div class='alert alert-success fw-bold text-center shadow-sm'><i class='bi bi-gift-fill me-2'></i> ¡Canje exitoso! Se descontaron $puntos_premio puntos a la cuenta del atleta.</div>";
        } else {
            $mensaje = "<div class='alert alert-danger fw-bold text-center shadow-sm'>❌ Error: El atleta no tiene suficientes puntos para reclamar este premio.</div>";
        }
    }
}

// --- LÓGICA 2: RECALCULAR PUNTOS Y ASISTENCIAS PARA LA TABLA ---
$tabla_render = [];
try {
    $stmtAtletas = $pdo->query("SELECT $columna_id as id, documento, nombre, apellido FROM afiliados ORDER BY id DESC");
    $todos_atletas = $stmtAtletas->fetchAll();
    
    foreach ($todos_atletas as $atl) {
        $id_atl = $atl['id'];
        
        $stmtA = $pdo->prepare("SELECT observaciones FROM asistencias WHERE id_afiliado = ?");
        $stmtA->execute([$id_atl]);
        
        $pts = 0;
        $asis_reales = 0;
        
        while($r = $stmtA->fetch()) {
            if (strpos($r['observaciones'], 'CANJE:') !== false) {
                if (preg_match('/-(\d+) PTS/', $r['observaciones'], $matches)) {
                    $pts -= intval($matches[1]); // Resta los puntos gastados
                }
            } elseif (strpos($r['observaciones'], 'Pase Diario') !== false) {
                // Ignorar pases casuales
            } else {
                $pts += 15; // Suma por entrenamiento
                $asis_reales++;
            }
        }
        
        // Evitar números negativos por seguridad visual
        if ($pts < 0) $pts = 0;
        
        $tabla_render[] = [
            'id' => $id_atl,
            'documento' => $atl['documento'],
            'nombre' => $atl['nombre'],
            'apellido' => $atl['apellido'],
            'asistencias' => $asis_reales,
            'puntos' => $pts
        ];
    }
} catch (PDOException $e) {
    $mensaje = "<div class='alert alert-danger text-center'>Error en procesamiento de cuentas: " . $e->getMessage() . "</div>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>King Rewards - Tienda de Fidelización</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=2.4">
    <style>
        body { background-color: #111; color: #fff; }
        .kings-card { background: #212529; border: 1px solid rgba(255,193,7,0.2); border-radius: 15px; }
        .reward-card { background: #1a1d20; border: 1px solid #333; border-radius: 10px; }
        .points-badge { background: linear-gradient(45deg, #ffc107, #ff9800); color: #000; font-weight: bold; }
    </style>
</head>
<body>

    <div class="container py-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bolder text-white mb-0"><i class="bi bi-gift-fill text-danger me-2"></i> KING REWARDS</h1>
                <p class="text-secondary mb-0">Club de Beneficios y Redención de Puntos Automatizado.</p>
            </div>
            <a href="../index.php" class="btn btn-outline-warning rounded-pill btn-sm px-3"><i class="bi bi-house-door"></i> Menú Principal</a>
        </div>

        <?php echo $mensaje; ?>

        <h4 class="text-warning fw-bold mb-3"><i class="bi bi-shop me-1"></i> Catálogo de Canjes</h4>
        <div class="row g-3 mb-5">
            <?php foreach($premios as $id_p => $premio): ?>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="reward-card p-3 text-center h-100 d-flex flex-column justify-content-between">
                        <div>
                            <i class="bi <?php echo $premio['icono'] . ' ' . $premio['color']; ?> fs-1 d-block mb-2"></i>
                            <h6 class="text-white fw-bold mb-1"><?php echo $premio['nombre']; ?></h6>
                        </div>
                        <div class="mt-2">
                            <span class="badge points-badge px-3 py-1.5 rounded-pill"><?php echo $premio['puntos']; ?> PTS</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <h4 class="text-warning fw-bold mb-3"><i class="bi bi-person-check me-1"></i> Estado de Cuentas por Atleta</h4>
        <div class="table-container kings-card p-4">
            <div class="table-responsive">
                <table class="table table-dark table-striped align-middle m-0">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Atleta Registrado</th>
                            <th class="text-center">Asistencias</th>
                            <th class="text-center">Saldo Disponible</th>
                            <th class="text-center">Acción de Caja</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($tabla_render)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No hay datos de atletas disponibles.</td></tr>
                        <?php else: ?>
                            <?php foreach($tabla_render as $atleta): ?>
                                <tr>
                                    <td class="text-secondary fw-bold"><?php echo htmlspecialchars($atleta['documento']); ?></td>
                                    <td class="text-white fw-bold"><?php echo htmlspecialchars($atleta['nombre'] . " " . $atleta['apellido']); ?></td>
                                    <td class="text-center"><span class="badge bg-dark border border-secondary"><?php echo $atleta['asistencias']; ?> Entrenos</span></td>
                                    <td class="text-center">
                                        <span class="text-warning fw-bold fs-5"><?php echo $atleta['puntos']; ?></span> <small class="text-muted">pts</small>
                                    </td>
                                    <td class="text-center">
                                        <form action="listar.php" method="POST" class="d-flex justify-content-center gap-1">
                                            <input type="hidden" name="id_afiliado" value="<?php echo $atleta['id']; ?>">
                                            <select name="id_premio" class="form-select form-select-sm bg-dark text-white border-secondary rounded-pill text-center" style="max-width: 190px;" required>
                                                <option value="" disabled selected>Selecciona Premio...</option>
                                                <?php foreach($premios as $id_p => $p): ?>
                                                    <option value="<?php echo $id_p; ?>" <?php echo ($atleta['puntos'] < $p['puntos']) ? 'disabled class="text-muted"' : ''; ?>>
                                                        <?php echo $p['nombre']; ?> (<?php echo $p['puntos']; ?> pts)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="btn_canjear" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold btn-dark">
                                                <i class="bi bi-gift"></i> Cobrar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>