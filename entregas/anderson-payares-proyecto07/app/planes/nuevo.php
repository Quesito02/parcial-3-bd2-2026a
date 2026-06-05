<?php
// planes/nuevo.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Plan</title>
</head>
<body>

    <p>
        <a href="../index.php">Volver al Dashboard</a> | 
        <a href="listar.php">Cancelar y Volver</a>
    </p>
    <hr>

    <h1>Registrar Nuevo Plan</h1>

    <form action="guardar.php" method="POST">
        
        <p>
            <label for="nombre">Nombre del Plan:</label><br>
            <input type="text" id="nombre" name="nombre" required>
        </p>

        <p>
            <label for="duracion_dias">Duración en Días (Ej: 30, 90, 365):</label><br>
            <input type="number" id="duracion_dias" name="duracion_dias" required>
        </p>

        <p>
            <label for="precio">Precio:</label><br>
            <input type="number" step="0.01" id="precio" name="precio" required>
        </p>

        <p>
            <label for="beneficios">Beneficios:</label><br>
            <textarea id="beneficios" name="beneficios" rows="4" cols="30"></textarea>
        </p>

        <p>
            <label for="estado">Estado:</label><br>
            <select id="estado" name="estado">
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo</option>
            </select>
        </p>

        <p>
            <button type="submit">Guardar Plan</button>
        </p>
        
    </form>

</body>
</html>