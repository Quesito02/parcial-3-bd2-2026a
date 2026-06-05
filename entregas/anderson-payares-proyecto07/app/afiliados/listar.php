<?php
// afiliados/listar.php
require_once '../conexion.php';

try {
    $sql = "SELECT * FROM afiliados ORDER BY nombre ASC";
    $stmt = $pdo->query($sql);
    $afiliados = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Afiliados - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- AQUÍ LLAMAMOS AL ESTILO MÁGICO -->
</head>
<body class="p-4">

    <div class="container" style="max-width: 1000px;">
        <!-- Encabezado del Módulo -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bolder text-dark" style="letter-spacing: -1px;">
                <i class="bi bi-people-fill text-warning me-2"></i> Directorio de Atletas
            </h2>
            <div>
                <a href="../index.php" class="btn btn-outline-dark me-2 rounded-pill px-4"><i class="bi bi-house-door"></i> Inicio</a>
                <a href="nuevo.php" class="btn btn-dark rounded-pill px-4"><i class="bi bi-plus-lg"></i> Nuevo Atleta</a>
            </div>
        </div>

        <!-- Tabla con el nuevo estilo -->
        <div class="table-container">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Documento</th>
                        <th>Atleta</th>
                        <th>Contacto</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($afiliados as $afiliado): ?>
                    <tr>
                        <td class="ps-3 fw-bold text-secondary"><?php echo htmlspecialchars($afiliado['documento']); ?></td>
                        <td class="fw-bold"><?php echo htmlspecialchars($afiliado['nombre'] . ' ' . $afiliado['apellido']); ?></td>
                        <td><?php echo htmlspecialchars($afiliado['telefono']); ?></td>
                        <td>
                            <span class="badge <?php echo $afiliado['estado'] == 'Activo' ? 'bg-success' : 'bg-danger'; ?> rounded-pill px-3">
                                <?php echo $afiliado['estado']; ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="perfil.php?id=<?php echo $afiliado['id_afiliado']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Ver Perfil 360</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>