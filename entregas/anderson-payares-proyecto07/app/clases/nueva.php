<?php
// clases/nueva.php
require_once '../conexion.php';

try {
    // Traer a los instructores activos para asignarlos a la clase
    $stmt = $pdo->query("SELECT id_instructor, nombre, apellido, especialidad FROM instructores WHERE estado = 'Activo' ORDER BY nombre ASC");
    $instructores = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error al cargar instructores: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Clase</title>
</head>
<body>

    <p>
        <a href="../index.php">Volver al Dashboard</a> | 
        <a href="listar.php">Cancelar y Volver</a>
    </p>
    <hr>

    <h1>Registrar Nueva Clase</h1>

    <form action="guardar.php" method="POST">
        
        <p>
            <label for="nombre">Nombre de la Clase (Ej: Spinning, Yoga):</label><br>
            <input type="text" id="nombre" name="nombre" required>
        </p>

        <p>
            <label for="horario">Horario (Ej: Lunes y Miércoles 6:00 PM):</label><br>
            <input type="text" id="horario" name="horario" required>
        </p>

        <p>
            <label for="cupo_maximo">Cupo Máximo de Asistentes:</label><br>
            <input type="number" id="cupo_maximo" name="cupo_maximo" required>
        </p>

        <p>
            <label for="id_instructor">Instructor a cargo:</label><br>
            <select id="id_instructor" name="id_instructor" required>
                <option value="">-- Seleccione un Instructor --</option>
                <?php foreach ($instructores as $inst): ?>
                    <option value="<?php echo $inst['id_instructor']; ?>">
                        <?php echo htmlspecialchars($inst['nombre'] . " " . $inst['apellido'] . " (" . $inst['especialidad'] . ")"); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <button type="submit">Guardar Clase</button>
        </p>
        
    </form>

</body>
</html>