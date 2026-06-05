<?php
// planes/editar.php
require_once '../conexion.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: listar.php");
    exit();
}

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $duracion_dias = $_POST['duracion_dias'];
    $precio = $_POST['precio'];
    $beneficios = $_POST['beneficios'];
    $estado = $_POST['estado'];

    try {
        $sql_update = "UPDATE planes SET nombre = ?, duracion_dias = ?, precio = ?, beneficios = ?, estado = ? WHERE id_plan = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$nombre, $duracion_dias, $precio, $beneficios, $estado, $id]);

        header("Location: listar.php?mensaje=actualizado");
        exit();
    } catch (PDOException $e) {
        echo "<h1>Error al actualizar</h1>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "<br><a href='listar.php'>Volver al listado</a>";
        exit();
    }
}

try {
    $sql_select = "SELECT * FROM planes WHERE id_plan = ?";
    $stmt_select = $pdo->prepare($sql_select);
    $stmt_select->execute([$id]);
    $plan = $stmt_select->fetch();

    if (!$plan) {
        echo "El plan no existe.";
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
    <title>Editar Plan</title>
</head>
<body>

    <p>
        <a href="../index.php">Volver al Dashboard</a> | 
        <a href="listar.php">Cancelar y Volver</a>
    </p>
    <hr>

    <h1>Editar Plan</h1>

    <form action="editar.php?id=<?php echo $id; ?>" method="POST">
        
        <p>
            <label for="nombre">Nombre del Plan:</label><br>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($plan['nombre']); ?>" required>
        </p>

        <p>
            <label for="duracion_dias">Duración en Días:</label><br>
            <input type="number" id="duracion_dias" name="duracion_dias" value="<?php echo htmlspecialchars($plan['duracion_dias']); ?>" required>
        </p>

        <p>
            <label for="precio">Precio:</label><br>
            <input type="number" step="0.01" id="precio" name="precio" value="<?php echo htmlspecialchars($plan['precio']); ?>" required>
        </p>

        <p>
            <label for="beneficios">Beneficios:</label><br>
            <textarea id="beneficios" name="beneficios" rows="4" cols="30"><?php echo htmlspecialchars($plan['beneficios']); ?></textarea>
        </p>

        <p>
            <label for="estado">Estado:</label><br>
            <select id="estado" name="estado">
                <option value="Activo" <?php echo ($plan['estado'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                <option value="Inactivo" <?php echo ($plan['estado'] == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
            </select>
        </p>

        <p>
            <button type="submit">Guardar Cambios</button>
        </p>
        
    </form>

</body>
</html>