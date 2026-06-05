<?php
// planes/eliminar.php
require_once '../conexion.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        // Intenta eliminar el plan
        $sql = "DELETE FROM planes WHERE id_plan = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        
        // Si tiene éxito, devuelve a la lista con el mensaje rojo
        header("Location: listar.php?mensaje=eliminado");
        exit();
        
    } catch (PDOException $e) {
        // REGLA DE NEGOCIO: Si el plan ya fue vendido a alguien (existe en la tabla membresias),
        // MySQL bloqueará el DELETE por la llave foránea para no dañar el historial financiero.
        echo "<script>
                alert('ACCESO DENEGADO: No se puede eliminar este plan porque ya hay afiliados que lo han comprado. Te sugerimos cambiar su estado a Inactivo en la opción Editar.');
                window.location.href='listar.php';
              </script>";
    }
} else {
    header("Location: listar.php");
}
?>