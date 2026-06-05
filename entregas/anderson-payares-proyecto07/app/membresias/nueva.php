<?php
// membresias/nueva.php
require_once '../conexion.php';

// Traer afiliados para el desplegable
$stmt_afiliados = $pdo->query("SELECT id_afiliado, documento, nombre, apellido FROM afiliados ORDER BY nombre ASC");
$afiliados = $stmt_afiliados->fetchAll();

// Traer planes activos para el desplegable
$stmt_planes = $pdo->query("SELECT id_plan, nombre, precio, duracion_dias FROM planes WHERE estado = 'Activo' ORDER BY nombre ASC");
$planes = $stmt_planes->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Membresía</title>
</head>
<body>

    <p>
        <a href="../index.php">Volver al Dashboard</a> | 
        <a href="listar.php">Cancelar y Volver</a>
    </p>
    <hr>

    <h1>Vender Membresía</h1>

    <form action="guardar.php" method="POST">
        
        <p>
            <label for="id_afiliado">Afiliado:</label><br>
            <select id="id_afiliado" name="id_afiliado" required>
                <option value="">-- Seleccione un Afiliado --</option>
                <?php foreach ($afiliados as $afil): ?>
                    <option value="<?php echo $afil['id_afiliado']; ?>">
                        <?php echo htmlspecialchars($afil['documento'] . " - " . $afil['nombre'] . " " . $afil['apellido']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="id_plan">Plan de Membresía:</label><br>
            <select id="id_plan" name="id_plan" required>
                <option value="">-- Seleccione un Plan --</option>
                <?php foreach ($planes as $plan): ?>
                    <option value="<?php echo $plan['id_plan']; ?>">
                        <?php echo htmlspecialchars($plan['nombre'] . " ($" . $plan['precio'] . " - " . $plan['duracion_dias'] . " días)"); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="fecha_inicio">Fecha de Inicio:</label><br>
            <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo date('Y-m-d'); ?>" required>
        </p>

        <p>
            <label for="valor_pagado">Valor a Pagar ($):</label><br>
            <input type="number" step="0.01" id="valor_pagado" name="valor_pagado" required>
        </p>

        <p>
            <label for="estado">Estado:</label><br>
            <select id="estado" name="estado">
                <option value="Activa">Activa</option>
                <option value="Inactiva">Inactiva</option>
            </select>
        </p>

        <p>
            <button type="submit">Registrar Venta</button>
        </p>
        
    </form>

</body>
</html>