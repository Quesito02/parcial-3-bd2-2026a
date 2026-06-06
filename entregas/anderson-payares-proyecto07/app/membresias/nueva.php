<?php
// membresias/nueva.php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_afiliado = $_POST['id_afiliado'];
    $id_plan = $_POST['id_plan'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $valor_pagado = $_POST['valor_pagado'];

    try {
        // Sacamos los días del plan para autocalcular el vencimiento
        $stmtPlan = $pdo->prepare("SELECT duracion_dias FROM planes WHERE id_plan = ?");
        $stmtPlan->execute([$id_plan]);
        $plan = $stmtPlan->fetch();
        $dias = $plan['duracion_dias'];

        $fecha_fin = date('Y-m-d', strtotime($fecha_inicio . " + $dias days"));

        $sql = "INSERT INTO membresias (id_afiliado, id_plan, fecha_inicio, fecha_fin, valor_pagado, estado) VALUES (?, ?, ?, ?, ?, 'Activa')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_afiliado, $id_plan, $fecha_inicio, $fecha_fin, $valor_pagado]);

        header("Location: listar.php");
        exit();
    } catch (PDOException $e) {
        die("Error procesando la venta: " . $e->getMessage());
    }
}

$afiliados = $pdo->query("SELECT * FROM afiliados WHERE estado = 'Activo' ORDER BY nombre ASC")->fetchAll();
$planes = $pdo->query("SELECT * FROM planes WHERE estado = 'Activo' ORDER BY nombre ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Venta - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light p-4">
    <div class="container" style="max-width: 650px;">
        <div class="card shadow border-0 mt-3">
            <div class="card-header bg-dark text-white fw-bold py-3">
                <i class="bi bi-cart-check-fill text-info me-2"></i> Apertura de Nueva Membresía
            </div>
            <div class="card-body p-4">
                <form action="nueva.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Seleccionar Atleta</label>
                        <select name="id_afiliado" class="form-select" required>
                            <option value="">-- Elige un atleta activo --</option>
                            <?php foreach ($afiliados as $a): ?>
                                <option value="<?php echo $a['id_afiliado']; ?>"><?php echo htmlspecialchars($a['nombre'] . ' ' . $a['apellido'] . ' - Doc: ' . $a['documento']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Plan a Adquirir</label>
                        <select name="id_plan" class="form-select" required>
                            <option value="">-- Elige el paquete --</option>
                            <?php foreach ($planes as $p): ?>
                                <option value="<?php echo $p['id_plan']; ?>"><?php echo htmlspecialchars($p['nombre'] . ' ($' . number_format($p['precio'],0).')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha de Activación</label>
                            <input type="date" name="fecha_inicio" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Valor Recibido ($)</label>
                            <input type="number" name="valor_pagado" class="form-control" placeholder="Monto total cobrado" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="listar.php" class="btn btn-secondary rounded-pill px-4">Cancelar Venta</a>
                        <button type="submit" class="btn btn-dark rounded-pill px-4">Registrar Pago e Iniciar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>