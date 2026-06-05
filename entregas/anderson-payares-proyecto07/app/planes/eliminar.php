<?php
// planes/eliminar.php
if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    require_once '../conexion.php';
    $id = $_GET['id'];

    try {
        $sql = "DELETE FROM planes WHERE id_plan = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        header("Location: listar.php?mensaje=eliminado");
        exit();

    } catch (PDOException $e) {
        echo "<h1>No se puede eliminar el plan</h1>";
        echo "<p>Es probable que este plan ya esté asignado a una membresía de un afiliado. Por seguridad de la base de datos, no se puede borrar.</p>";
        echo "<p><i>Detalle técnico: " . $e->getMessage() . "</i></p>";
        echo "<br><a href='listar.php'>Volver al listado</a>";
    }

} else {
    header("Location: listar.php");
    exit();
}
?>