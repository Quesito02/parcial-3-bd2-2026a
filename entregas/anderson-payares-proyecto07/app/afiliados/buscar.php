<?php
// afiliados/buscar.php
require_once '../conexion.php';

if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
    header("Location: ../index.php");
    exit();
}

$termino = trim($_GET['q']);
$busqueda = "%" . $termino . "%";

try {
    // Buscamos por documento exacto o por coincidencia en nombre/apellido
    $sql = "SELECT id_afiliado, documento, nombre, apellido FROM afiliados 
            WHERE documento = ? OR nombre LIKE ? OR apellido LIKE ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$termino, $busqueda, $busqueda]);
    $resultados = $stmt->fetchAll();

    // Si solo hay un resultado, lo llevamos directo al Perfil 360 (¡Magia!)
    if (count($resultados) === 1) {
        $id = $resultados[0]['id_afiliado'];
        header("Location: perfil.php?id=$id");
        exit();
    }

} catch (PDOException $e) {
    echo "Error en la búsqueda: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de Búsqueda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container">
        <a href="../index.php" class="btn btn-secondary mb-4">Volver al Inicio</a>
        <h2>Resultados para: "<?php echo htmlspecialchars($termino); ?>"</h2>

        <?php if (count($resultados) > 1): ?>
            <div class="list-group mt-4">
                <?php foreach ($resultados as $res): ?>
                    <a href="perfil.php?id=<?php echo $res['id_afiliado']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?php echo htmlspecialchars($res['nombre'] . " " . $res['apellido']); ?></strong>
                            <br><small class="text-muted">Documento: <?php echo htmlspecialchars($res['documento']); ?></small>
                        </div>
                        <span class="badge bg-primary rounded-pill">Ver Perfil</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning mt-4">No se encontró a nadie con ese documento o nombre.</div>
        <?php endif; ?>
    </div>
</body>
</html>