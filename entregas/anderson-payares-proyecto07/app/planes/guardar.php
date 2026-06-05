<?php
// planes/guardar.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    require_once '../conexion.php';

    $nombre = $_POST['nombre'];
    $duracion_dias = $_POST['duracion_dias'];
    $precio = $_POST['precio'];
    $beneficios = $_POST['beneficios'];
    $estado = $_POST['estado'];

    try {
        $sql = "INSERT INTO planes (nombre, duracion_dias, precio, beneficios, estado) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $duracion_dias, $precio, $beneficios, $estado]);

        header("Location: listar.php?mensaje=guardado");
        exit();

    } catch (PDOException $e) {
        echo "<h1>Error al guardar el plan</h1>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "<br><a href='nuevo.php'>Volver al formulario</a>";
    }

} else {
    header("Location: nuevo.php");
    exit();
}
?>