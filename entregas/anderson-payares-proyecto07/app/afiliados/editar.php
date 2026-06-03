<?php
// afiliados/editar.php
require_once '../conexion.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: listar.php");
    exit();
}

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $documento = $_POST['documento'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ? $_POST['fecha_nacimiento'] : null;

    try {
        $sql_update = "UPDATE afiliados SET documento = ?, nombre = ?, apellido = ?, telefono = ?, correo = ?, fecha_nacimiento = ? WHERE id_afiliado = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$documento, $nombre, $apellido, $telefono, $correo, $fecha_nacimiento, $id]);

        header("Location: listar.php?mensaje=actualizado");
        exit();
    } catch (PDOException $e) {
        echo "<h1>Error al actualizar el afiliado</h1>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "<br><a href='listar.php'>Volver al listado</a>";
        exit();
    }
}

try {
    $sql_select = "SELECT * FROM afiliados WHERE id_afiliado = ?";
    $stmt_select = $pdo->prepare($sql_select);
    $stmt_select->execute([$id]);
    $afiliado = $stmt_select->fetch();

    if (!$afiliado) {
        echo "El afiliado no existe.";
        echo "<br><a href='listar.php'>Volver al listado</a>";
        exit();
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Afiliado</title>
</head>
<body>

    <p>
        <a href="../index.php">Volver al Dashboard</a> | 
        <a href="listar.php">Cancelar y Volver</a>
    </p>
    <hr>

    <h1>Editar Afiliado</h1>

    <form action="editar.php?id=<?php echo $id; ?>" method="POST">
        
        <p>
            <label for="documento">Documento de Identidad:</label><br>
            <input type="text" id="documento" name="documento" value="<?php echo htmlspecialchars($afiliado['documento']); ?>" required>
        </p>

        <p>
            <label for="nombre">Nombre:</label><br>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($afiliado['nombre']); ?>" required>
        </p>

        <p>
            <label for="apellido">Apellido:</label><br>
            <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($afiliado['apellido']); ?>" required>
        </p>

        <p>
            <label for="telefono">Teléfono:</label><br>
            <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($afiliado['telefono']); ?>">
        </p>

        <p>
            <label for="correo">Correo Electrónico:</label><br>
            <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($afiliado['correo']); ?>">
        </p>

        <p>
            <label for="fecha_nacimiento">Fecha de Nacimiento:</label><br>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($afiliado['fecha_nacimiento'] ?? ''); ?>">
        </p>

        <p>
            <button type="submit">Guardar Cambios</button>
        </p>
        
    </form>

</body>
</html>