<?php
// planes/listar.php
require_once '../conexion.php';

try {
    $sql = "SELECT id_plan, nombre, duracion_dias, precio, beneficios, estado FROM planes ORDER BY id_plan DESC";
    $stmt = $pdo->query($sql);
    $planes = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error en la consulta: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Planes</title>
</head>
<body>

    <p>
        <a href="../index.php">Volver al Dashboard</a> | 
        <a href="nuevo.php">Registrar Nuevo Plan</a>
    </p>
    <hr>

    <h1>Gestión de Planes de Membresía</h1>

    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'guardado'): ?>
        <p style="color: green;"><strong>¡Éxito! El plan ha sido registrado.</strong></p>
    <?php endif; ?>
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'actualizado'): ?>
        <p style="color: blue;"><strong>¡Éxito! El plan ha sido actualizado.</strong></p>
    <?php endif; ?>
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'eliminado'): ?>
        <p style="color: red;"><strong>¡Éxito! El plan ha sido eliminado.</strong></p>
    <?php endif; ?>

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Duración (Días)</th>
                <th>Precio</th>
                <th>Beneficios</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($planes) > 0): ?>
                <?php foreach ($planes as $plan): ?>
                    <tr>
                        <td><?php echo $plan['id_plan']; ?></td>
                        <td><?php echo htmlspecialchars($plan['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($plan['duracion_dias']); ?></td>
                        <td>$<?php echo number_format($plan['precio'], 2); ?></td>
                        <td><?php echo htmlspecialchars($plan['beneficios']); ?></td>
                        <td><?php echo htmlspecialchars($plan['estado']); ?></td>
                        <td>
                            <a href="editar.php?id=<?php echo $plan['id_plan']; ?>">Editar</a> | 
                            <a href="eliminar.php?id=<?php echo $plan['id_plan']; ?>" onclick="return confirm('¿Seguro que deseas eliminar este plan?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No hay planes registrados en el sistema.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>