<?php
// afiliados/perfil.php
require_once '../conexion.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: listar.php");
    exit();
}

$id = $_GET['id'];

try {
    // 1. Datos del afiliado (ahora incluye somatotipo y objetivo)
    $stmt1 = $pdo->prepare("SELECT * FROM afiliados WHERE id_afiliado = ?");
    $stmt1->execute([$id]);
    $afiliado = $stmt1->fetch();

    if (!$afiliado) { die("Afiliado no encontrado."); }

    // 2. Membresía activa
    $stmt2 = $pdo->prepare("SELECT p.nombre, m.fecha_inicio, m.fecha_fin, DATEDIFF(m.fecha_fin, CURDATE()) as dias_restantes 
                            FROM membresias m 
                            INNER JOIN planes p ON m.id_plan = p.id_plan 
                            WHERE m.id_afiliado = ? AND m.estado = 'Activa' 
                            ORDER BY m.id_membresia DESC LIMIT 1");
    $stmt2->execute([$id]);
    $membresia = $stmt2->fetch();

    // Lógica del SMART COACH
    date_default_timezone_set('America/Bogota');
    $hora_entera = (int)date('H');
    $momento_dia = ($hora_entera < 12) ? 'Mañana' : 'Tarde/Noche';
    
    $somatotipo = $afiliado['somatotipo'] ?? 'Mesomorfo';
    $objetivo = $afiliado['objetivo'] ?? 'Mantenimiento';
    $dieta = "";

    if ($somatotipo === 'Ectomorfo') {
        $dieta = ($momento_dia === 'Mañana') 
            ? "Batido hipercalórico post-entreno (Avena, banano, proteína, leche entera) para asegurar el superávit." 
            : "Cena densa: 200g de pollo, 150g de arroz y aguacate. Ideal para no catabolizar en la noche.";
    } elseif ($somatotipo === 'Endomorfo') {
        $dieta = ($momento_dia === 'Mañana') 
            ? "Desayuno proteico: Tortilla de claras, espinacas y té verde. Mantener insulina baja." 
            : "Cena baja en carbohidratos: Filete de pescado magro con brócoli al vapor.";
    } else {
        $dieta = ($momento_dia === 'Mañana') 
            ? "Evolución limpia: Yogur griego, almendras y huevos duros." 
            : "Recuperación: Carne de res magra, puré de papa criolla y ensalada verde.";
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil - <?php echo htmlspecialchars($afiliado['nombre']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style> body { background-color: #f8f9fa; } .card { border-radius: 15px; } </style>
</head>
<body class="p-4">

    <div class="container">
        <a href="../index.php" class="btn btn-outline-dark mb-4 px-4 rounded-pill"><i class="bi bi-search"></i> Nueva Búsqueda</a>
        
        <div class="row">
            <div class="col-md-5 mb-4">
                
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body text-center mt-3">
                        <div class="display-1 text-primary mb-3"><i class="bi bi-person-bounding-box"></i></div>
                        <h3 class="fw-bold"><?php echo htmlspecialchars($afiliado['nombre'] . " " . $afiliado['apellido']); ?></h3>
                        <span class="badge <?php echo $afiliado['estado'] == 'Activo' ? 'bg-success' : 'bg-danger'; ?> mb-3 px-3 py-2">
                            <?php echo $afiliado['estado']; ?>
                        </span>
                        <p class="text-muted mb-0">Doc: <?php echo htmlspecialchars($afiliado['documento']); ?></p>
                        <a href="editar.php?id=<?php echo $id; ?>" class="btn btn-sm btn-outline-primary mt-3 rounded-pill w-50">Editar Datos</a>
                    </div>
                </div>

                <div class="card shadow-sm border-0 bg-dark text-white">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-warning mb-3"><i class="bi bi-cpu-fill"></i> Smart Coach AI</h5>
                        <div class="d-flex justify-content-between mb-3 border-bottom border-secondary pb-2">
                            <span class="text-white-50">Cuerpo:</span>
                            <span class="fw-bold"><?php echo $somatotipo; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 border-bottom border-secondary pb-2">
                            <span class="text-white-50">Meta:</span>
                            <span class="fw-bold"><?php echo $objetivo; ?></span>
                        </div>
                        <div class="p-3 rounded" style="background: rgba(255,255,255,0.05);">
                            <small class="text-warning fw-bold d-block mb-1"><i class="bi bi-lightning-charge-fill"></i> Dieta sugerida (<?php echo $momento_dia; ?>):</small>
                            <small class="fst-italic lh-sm d-block text-light"><?php echo $dieta; ?></small>
                        </div>
                        <div class="mt-3 text-center">
                            <small class="text-info"><i class="bi bi-droplet-fill"></i> Recuerda consumir suficiente agua, especialmente dado el clima caluroso de la región para evitar deshidratación durante el entreno.</small>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-7">
                
                <div class="card shadow-sm border-0 mb-4 border-top border-success border-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4"><i class="bi bi-star-fill text-success me-2"></i> Estado de Membresía</h5>
                        <?php if ($membresia): ?>
                            <h4 class="text-dark fw-bolder"><?php echo htmlspecialchars($membresia['nombre']); ?></h4>
                            <p class="text-muted mb-2">Válida hasta: <?php echo $membresia['fecha_fin']; ?></p>
                            <?php if ($membresia['dias_restantes'] > 0): ?>
                                <span class="badge bg-success-subtle text-success fs-6 px-3 py-2 border border-success"><i class="bi bi-check-circle"></i> Faltan <?php echo $membresia['dias_restantes']; ?> días</span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger fs-6 px-3 py-2 border border-danger"><i class="bi bi-exclamation-octagon"></i> Membresía Vencida</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-warning border-0 shadow-sm mb-0">
                                <i class="bi bi-info-circle"></i> El atleta no posee membresías activas.
                                <a href="../membresias/nueva.php" class="btn btn-dark btn-sm float-end rounded-pill px-3">Vender Ahora</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card shadow-sm border-0 bg-primary text-white text-center" style="cursor: pointer;" onclick="window.location.href='../asistencias/registrar.php?afiliado=<?php echo $id; ?>'">
                    <div class="card-body py-4">
                        <i class="bi bi-upc-scan display-4 d-block mb-2"></i>
                        <h4 class="fw-bold mb-0">Registrar Entrada Manual</h4>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>