<?php
// afiliados/nuevo.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Afiliado</title>
</head>
<body>

    <p>
        <a href="../index.php">Volver al Dashboard</a> | 
        <a href="listar.php">Ver Listado de Afiliados</a>
    </p>
    <hr>

    <h1>Registrar Nuevo Afiliado</h1>

    <form action="guardar.php" method="POST">
        
        <p>
            <label for="documento">Documento de Identidad:</label><br>
            <input type="text" id="documento" name="documento" required>
        </p>

        <p>
            <label for="nombre">Nombre:</label><br>
            <input type="text" id="nombre" name="nombre" required>
        </p>

        <p>
            <label for="apellido">Apellido:</label><br>
            <input type="text" id="apellido" name="apellido" required>
        </p>

        <p>
            <label for="telefono">Teléfono:</label><br>
            <input type="text" id="telefono" name="telefono">
        </p>

        <p>
            <label for="correo">Correo Electrónico:</label><br>
            <input type="email" id="correo" name="correo">
        </p>

        <p>
            <label for="fecha_nacimiento">Fecha de Nacimiento:</label><br>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento">
        </p>

        <p>
            <button type="submit">Guardar Afiliado</button>
        </p>
        
    </form>

</body>
</html>