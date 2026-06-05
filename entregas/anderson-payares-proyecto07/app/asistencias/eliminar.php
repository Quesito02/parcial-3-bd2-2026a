<?php
// asistencias/eliminar.php
require_once '../conexion.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $sql = "DELETE FROM asistencias WHERE id_asistencia = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        header("Location: listar.php?mensaje=eliminado");
        exit();
    } catch (PDOException $e) {
        die("Error al eliminar: " . $e->getMessage());
    }
} else {
    header("Location: listar.php");
}
?>