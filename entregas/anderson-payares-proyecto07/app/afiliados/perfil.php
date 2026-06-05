<?php
// afiliados/perfil.php
require_once '../conexion.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: listar.php");
    exit();
}

$id = $_GET['id'];

try {
    // 1. Datos básicos del afiliado
    $stmt1 = $pdo->prepare("SELECT * FROM afiliados WHERE id_afiliado = ?");
    $stmt1->execute([$id]);
    $afiliado = $stmt1->fetch();

    if (!$afiliado) {
        die("Afiliado no encontrado.");
    }

    // 2. Membresía activa (si tiene)
    $stmt2 = $pdo->prepare("SELECT p.nombre, m.fecha_inicio, m.fecha_fin, DATEDIFF(m.fecha_fin, CURDATE()) as dias_restantes 
                            FROM membresias m 
                            INNER JOIN planes p ON m.id_plan = p.id_plan 
                            WHERE m.id_afiliado = ? AND m.estado = 'Activa' 
                            ORDER BY m.id_membresia DESC LIMIT 1");
    $stmt2->execute([$id]);
    $membresia = $stmt2->fetch();

    // 3. Últimas 5 asistencias
    $stmt3 = $pdo->prepare("SELECT fecha, hora, observaciones FROM asistencias 
                            WHERE id_afiliado = ? ORDER BY fecha DESC, hora DESC LIMIT 5");
    $stmt3->execute([$id]);
    $asistencias = $stmt3->fetchAll();

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
</head>
<body class="bg-light p-4">

    <div class="container">
        <a href="../index.php" class="btn btn-outline-secondary mb-4"><i class="bi bi-arrow-left"></i> Volver</a>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center mt-3">
                        <div class="display-1 text-primary mb-3"><i class="bi bi-person-circle"></i></div>
                        <h3 class="fw-bold"><?php echo htmlspecialchars($afiliado['nombre'] . " " . $afiliado['apellido']); ?></h3>
                        <span class="badge <?php echo $afiliado['estado'] == 'Activo' ? 'bg-success' : 'bg-danger'; ?> mb-3">
                            <?php echo $afiliado['estado']; ?>
                        </span>
                        <hr>
                        <p class="text-start mb-1"><strong>Documento:</strong> <?php echo htmlspecialchars($afiliado['documento']); ?></p>
                        <p class="text-start mb-1"><strong>Teléfono:</strong> <?php echo htmlspecialchars($afiliado['telefono']); ?></p>
                        <p class="text-start mb-1"><strong>Correo:</strong> <?php echo htmlspecialchars($afiliado['correo']); ?></p>
                    </div>
                    <div class="card-footer bg-white border-0 text-center pb-3">
                        <a href="editar.php?id=<?php echo $id; ?>" class="btn btn-primary btn-sm w-100">Editar Datos</a>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                
                <div class="card shadow-sm border-0 mb-4 border-start border-warning border-4">
                    <div class="card-header bg-white fw-bold"><i class="bi bi-star-fill text-warning"></i> Estado de Membresía</div>
                    <div class="card-body">
                        <?php if ($membresia): ?>
                            <h5 class="text-primary fw-bold"><?php echo htmlspecialchars($membresia['nombre']); ?></h5>
                            <p class="mb-1"><strong>Válida hasta:</strong> <?php echo $membresia['fecha_fin']; ?></p>
                            <?php if ($membresia['dias_restantes'] > 0): ?>
                                <p class="text-success fw-bold mb-0"><i class="bi bi-check-circle"></i> Le quedan <?php echo $membresia['dias_restantes']; ?> días.</p>
                            <?php else: ?>
                                <p class="text-danger fw-bold mb-0"><i class="bi bi-exclamation-octagon"></i> ¡Vencida!</p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-muted mb-0">El usuario no tiene membresías activas.</p>
                            <a href="../membresias/nueva.php" class="btn btn-sm btn-outline-success mt-2">Vender Membresía</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card shadow-sm border-0 border-start border-info border-4">
                    <div class="card-header bg-white fw-bold"><i class="bi bi-calendar-check text-info"></i> Últimas 5 Asistencias</div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php if (count($asistencias) > 0): ?>
                                <?php foreach ($asistencias as $asis): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-clock text-muted me-2"></i> <?php echo $asis['fecha']; ?></span>
                                        <span class="badge bg-light text-dark border"><?php echo $asis['hora']; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="list-group-item text-muted">Aún no ha registrado asistencias.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>