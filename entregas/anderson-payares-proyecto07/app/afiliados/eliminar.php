<?php
// afiliados/eliminar.php
if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    require_once '../conexion.php';
    $id = $_GET['id'];

    try {
        $sql = "DELETE FROM afiliados WHERE id_afiliado = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        header("Location: listar.php?mensaje=eliminado");
        exit();

    } catch (PDOException $e) {
        echo "<h1>Error al eliminar el afiliado</h1>";
        echo "<p>Detalle del error: " . $e->getMessage() . "</p>";
        echo "<br><a href='listar.php'>Volver al listado</a>";
    }

} else {
    header("Location: listar.php");
    exit();
}
?>