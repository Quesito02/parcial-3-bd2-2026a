<?php
// afiliados/guardar.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    require_once '../conexion.php';

    $documento = $_POST['documento'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ? $_POST['fecha_nacimiento'] : null;

    try {
        $sql = "INSERT INTO afiliados (documento, nombre, apellido, telefono, correo, fecha_nacimiento) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$documento, $nombre, $apellido, $telefono, $correo, $fecha_nacimiento]);

        header("Location: listar.php?mensaje=guardado");
        exit();

    } catch (PDOException $e) {
        echo "<h1>Error al guardar el afiliado</h1>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "<br><a href='nuevo.php'>Volver al formulario</a>";
    }

} else {
    header("Location: nuevo.php");
    exit();
}
?>