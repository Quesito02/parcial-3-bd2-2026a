<?php
// asistencias/registrar.php
require_once '../conexion.php';

// Si el formulario fue enviado (POST), procesamos los datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_afiliado = $_POST['id_afiliado'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $observaciones = $_POST['observaciones'];

    try {
        $sql = "INSERT INTO asistencias (id_afiliado, fecha, hora, observaciones) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_afiliado, $fecha, $hora, $observaciones]);

        // Redirigir al listado con mensaje de éxito
        header("Location: listar.php?mensaje=registrado");
        exit();

    } catch (PDOException $e) {
        $error = "Error al registrar la asistencia: " . $e->getMessage();
    }
}

// Consultamos los afiliados para llenar la lista desplegable
try {
    $stmt_afiliados = $pdo->query("SELECT id_afiliado, documento, nombre, apellido FROM afiliados ORDER BY nombre ASC");
    $afiliados = $stmt_afiliados->fetchAll();
} catch (PDOException $e) {
    echo "Error al cargar afiliados: " . $e->getMessage();
    exit();
}

// Obtenemos la fecha y hora actual para autocompletar los campos
date_default_timezone_set('America/Bogota'); // Ajusta la zona horaria a Colombia
$fecha_actual = date('Y-m-d');
$hora_actual = date('H:i');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Asistencia</title>
</head>
<body>

    <p>
        <a href="../index.php">Volver al Dashboard</a> | 
        <a href="listar.php">Ver Historial de Asistencias</a>
    </p>
    <hr>

    <h1>Registrar Entrada de Afiliado</h1>

    <?php if (isset($error)): ?>
        <p style="color: red;"><strong><?php echo $error; ?></strong></p>
    <?php endif; ?>

    <form action="registrar.php" method="POST">
        
        <p>
            <label for="id_afiliado">Afiliado que ingresa:</label><br>
            <select id="id_afiliado" name="id_afiliado" required>
                <option value="">-- Seleccione un Afiliado --</option>
                <?php foreach ($afiliados as $afil): ?>
                    <option value="<?php echo $afil['id_afiliado']; ?>">
                        <?php echo htmlspecialchars($afil['documento'] . " - " . $afil['nombre'] . " " . $afil['apellido']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="fecha">Fecha:</label><br>
            <input type="date" id="fecha" name="fecha" value="<?php echo $fecha_actual; ?>" required>
        </p>

        <p>
            <label for="hora">Hora de Entrada:</label><br>
            <input type="time" id="hora" name="hora" value="<?php echo $hora_actual; ?>" required>
        </p>

        <p>
            <label for="observaciones">Observaciones (Opcional):</label><br>
            <textarea id="observaciones" name="observaciones" rows="3" cols="30"></textarea>
        </p>

        <p>
            <button type="submit">Registrar Entrada</button>
        </p>
        
    </form>

</body>
</html>