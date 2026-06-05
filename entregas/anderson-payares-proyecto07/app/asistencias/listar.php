<?php
// asistencias/listar.php
require_once '../conexion.php';

try {
    $sql = "SELECT asis.id_asistencia, asis.fecha, asis.hora, asis.observaciones, 
                   af.nombre, af.apellido, af.documento 
            FROM asistencias asis
            INNER JOIN afiliados af ON asis.id_afiliado = af.id_afiliado
            ORDER BY asis.fecha DESC, asis.hora DESC";
    $stmt = $pdo->query($sql);
    $asistencias = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asistencias - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light p-4">

    <div class="container" style="max-width: 1000px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bolder text-dark" style="letter-spacing: -1px;">
                <i class="bi bi-door-open-fill text-secondary me-2"></i> Historial de Accesos
            </h2>
            <a href="../index.php" class="btn btn-outline-dark rounded-pill px-4"><i class="bi bi-house-door"></i> Inicio</a>
        </div>

        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'eliminado'): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="bi bi-trash-fill me-2"></i> Registro de asistencia eliminado.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-container shadow-sm">
            <table class="table align-middle mb-0 table-hover">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Documento</th>
                        <th>Atleta</th>
                        <th>Fecha y Hora</th>
                        <th>Método / Observación</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($asistencias) > 0): ?>
                        <?php foreach ($asistencias as $asis): ?>
                            <tr>
                                <td class="ps-4 text-muted fw-bold"><?php echo htmlspecialchars($asis['documento']); ?></td>
                                <td class="fw-bold text-dark"><?php echo htmlspecialchars($asis['nombre'] . ' ' . $asis['apellido']); ?></td>
                                <td>
                                    <i class="bi bi-calendar-event text-muted me-1"></i> <?php echo $asis['fecha']; ?> <br>
                                    <i class="bi bi-clock text-muted me-1"></i> <span class="badge bg-light text-dark border"><?php echo $asis['hora']; ?></span>
                                </td>
                                <td><span class="fst-italic text-secondary" style="font-size: 0.9rem;"><?php echo htmlspecialchars($asis['observaciones']); ?></span></td>
                                <td class="text-center">
                                    <a href="eliminar.php?id=<?php echo $asis['id_asistencia']; ?>" class="btn btn-sm btn-outline-danger px-3 rounded-pill" onclick="return confirm('¿Seguro que deseas borrar este registro de entrada?');">
                                        <i class="bi bi-trash-fill"></i> Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">Aún no hay registros de entrada en el sistema.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>