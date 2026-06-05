<?php
// clases/listar.php
require_once '../conexion.php';

try {
    // Unimos la tabla clases con instructores para traer el nombre del profesor
    $sql = "SELECT c.id_clase, c.nombre AS clase_nombre, c.horario, c.cupo_maximo, 
                   i.nombre AS instructor_nombre, i.apellido AS instructor_apellido 
            FROM clases c
            LEFT JOIN instructores i ON c.id_instructor = i.id_instructor
            ORDER BY c.id_clase DESC";
    $stmt = $pdo->query($sql);
    $clases = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error en la consulta: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Clases</title>
</head>
<body>

    <p>
        <a href="../index.php">Volver al Dashboard</a> | 
        <a href="nueva.php">Registrar Nueva Clase</a>
    </p>
    <hr>

    <h1>Gestión de Clases Dirigidas</h1>

    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'guardado'): ?>
        <p style="color: green;"><strong>¡Éxito! La clase ha sido registrada correctamente.</strong></p>
    <?php endif; ?>

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre de la Clase</th>
                <th>Horario</th>
                <th>Cupo Máximo</th>
                <th>Instructor</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($clases) > 0): ?>
                <?php foreach ($clases as $clase): ?>
                    <tr>
                        <td><?php echo $clase['id_clase']; ?></td>
                        <td><?php echo htmlspecialchars($clase['clase_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($clase['horario']); ?></td>
                        <td><?php echo htmlspecialchars($clase['cupo_maximo']); ?></td>
                        <td>
                            <?php 
                            if ($clase['instructor_nombre']) {
                                echo htmlspecialchars($clase['instructor_nombre'] . " " . $clase['instructor_apellido']); 
                            } else {
                                echo "<em>Sin asignar</em>";
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No hay clases registradas en el sistema.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>