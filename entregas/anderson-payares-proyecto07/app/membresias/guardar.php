<?php
// membresias/guardar.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    require_once '../conexion.php';

    $id_afiliado = $_POST['id_afiliado'];
    $id_plan = $_POST['id_plan'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $valor_pagado = $_POST['valor_pagado'];
    $estado = $_POST['estado'];
    
    // La fecha de venta es el día exacto en que se registra en el sistema
    $fecha_venta = date('Y-m-d H:i:s');

    try {
        // 1. Consultar cuántos días dura el plan elegido
        $stmt_plan = $pdo->prepare("SELECT duracion_dias FROM planes WHERE id_plan = ?");
        $stmt_plan->execute([$id_plan]);
        $plan = $stmt_plan->fetch();
        $duracion_dias = $plan['duracion_dias'];

        // 2. Calcular la fecha de fin sumando los días a la fecha de inicio
        $fecha_fin = date('Y-m-d', strtotime($fecha_inicio . " + $duracion_dias days"));

        // 3. Insertar la membresía en la base de datos
        $sql = "INSERT INTO membresias (id_afiliado, id_plan, fecha_venta, fecha_inicio, fecha_fin, valor_pagado, estado) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_afiliado, $id_plan, $fecha_venta, $fecha_inicio, $fecha_fin, $valor_pagado, $estado]);

        header("Location: listar.php?mensaje=guardado");
        exit();

    } catch (PDOException $e) {
        echo "<h1>Error al registrar la membresía</h1>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "<br><a href='nueva.php'>Volver al formulario</a>";
    }

} else {
    header("Location: nueva.php");
    exit();
}
?>