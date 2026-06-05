<?php
// clases/guardar.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    require_once '../conexion.php';

    $nombre = $_POST['nombre'];
    $horario = $_POST['horario'];
    $cupo_maximo = $_POST['cupo_maximo'];
    $id_instructor = $_POST['id_instructor'];

    try {
        $sql = "INSERT INTO clases (nombre, horario, cupo_maximo, id_instructor) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $horario, $cupo_maximo, $id_instructor]);

        header("Location: listar.php?mensaje=guardado");
        exit();

    } catch (PDOException $e) {
        echo "<h1>Error al guardar la clase</h1>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "<br><a href='nueva.php'>Volver al formulario</a>";
    }

} else {
    header("Location: nueva.php");
    exit();
}
?>