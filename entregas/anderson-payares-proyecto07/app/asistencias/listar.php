<?php
// asistencias/listar.php
require_once '../conexion.php';

try {
    // Unimos asistencias con afiliados para ver el nombre y documento
    $sql = "SELECT a.id_asistencia, af.documento, af.nombre, af.apellido, a.fecha, a.hora, a.observaciones 
            FROM asistencias a
            INNER JOIN afiliados af ON a.id_afiliado = af.id_afiliado
            ORDER BY a.fecha DESC, a.hora DESC";
    $stmt = $pdo->query($sql);
    $asistencias = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error en la consulta: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Asistencias</title>
</head>
<body>

    <p>
        <a href="../index.php">Volver al Dashboard</a> | 
        <a href="registrar.php">Registrar Nueva Asistencia</a>
    </p>
    <hr>

    <h1>Control de Asistencias Diarias</h1>

    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'registrado'): ?>
        <p style="color: green;"><strong>¡Éxito! La entrada del afiliado ha sido registrada.</strong></p>
    <?php endif; ?>

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID Asistencia</th>
                <th>Documento</th>
                <th>Afiliado</th>
                <th>Fecha</th>
                <th>Hora de Entrada</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($asistencias) > 0): ?>
                <?php foreach ($asistencias as $asistencia): ?>
                    <tr>
                        <td><?php echo $asistencia['id_asistencia']; ?></td>
                        <td><?php echo htmlspecialchars($asistencia['documento']); ?></td>
                        <td><?php echo htmlspecialchars($asistencia['nombre'] . " " . $asistencia['apellido']); ?></td>
                        <td><?php echo htmlspecialchars($asistencia['fecha']); ?></td>
                        <td><?php echo htmlspecialchars($asistencia['hora']); ?></td>
                        <td><?php echo htmlspecialchars($asistencia['observaciones']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No hay registros de asistencia aún.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>