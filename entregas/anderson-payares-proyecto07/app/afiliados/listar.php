<?php
// afiliados/listar.php
require_once '../conexion.php';

try {
    // Consulta usando tus columnas reales de la base de datos 'gym'
    $sql = "SELECT id_afiliado, documento, nombre, apellido, telefono, correo, fecha_nacimiento FROM afiliados ORDER BY id_afiliado DESC";
    $stmt = $pdo->query($sql);
    $afiliados = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error en la consulta: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Afiliados</title>
</head>
<body>

    <p>
        <a href="../index.php">Volver al Dashboard</a> | 
        <a href="nuevo.php">Registrar Nuevo Afiliado</a>
    </p>
    <hr>

    <h1>Gestión de Afiliados</h1>

    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'guardado'): ?>
        <p style="color: green;"><strong>¡Éxito! El afiliado ha sido registrado correctamente.</strong></p>
    <?php endif; ?>
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'actualizado'): ?>
        <p style="color: blue;"><strong>¡Éxito! Los datos del afiliado han sido actualizados.</strong></p>
    <?php endif; ?>
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'eliminado'): ?>
        <p style="color: red;"><strong>¡Éxito! El afiliado ha sido eliminado correctamente.</strong></p>
    <?php endif; ?>

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Documento</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Fecha Nacimiento</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($afiliados) > 0): ?>
                <?php foreach ($afiliados as $afiliado): ?>
                    <tr>
                        <td><?php echo $afiliado['id_afiliado']; ?></td>
                        <td><?php echo htmlspecialchars($afiliado['documento']); ?></td>
                        <td><?php echo htmlspecialchars($afiliado['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($afiliado['apellido']); ?></td>
                        <td><?php echo htmlspecialchars($afiliado['telefono']); ?></td>
                        <td><?php echo htmlspecialchars($afiliado['correo']); ?></td>
                        <td><?php echo htmlspecialchars($afiliado['fecha_nacimiento'] ?? ''); ?></td>
                        <td>
                            <a href="editar.php?id=<?php echo $afiliado['id_afiliado']; ?>">Editar</a> | 
                            <a href="eliminar.php?id=<?php echo $afiliado['id_afiliado']; ?>" onclick="return confirm('¿Seguro que deseas eliminar este afiliado?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No hay afiliados registrados en el sistema.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>