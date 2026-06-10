<?php
// afiliados/editar.php
require_once '../conexion.php';

if (!isset($_GET['id'])) {
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
    $estado = $_POST['estado'];
    // Nuevos campos para la IA
    $somatotipo = $_POST['somatotipo'];
    $objetivo = $_POST['objetivo'];

    try {
        $sql = "UPDATE afiliados SET documento=?, nombre=?, apellido=?, telefono=?, correo=?, estado=?, somatotipo=?, objetivo=? WHERE id_afiliado=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$documento, $nombre, $apellido, $telefono, $correo, $estado, $somatotipo, $objetivo, $id]);
        
        // Al guardar, lo mandamos directo a ver su perfil con los cambios
        header("Location: perfil.php?id=$id");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// Cargar datos actuales
$stmt = $pdo->prepare("SELECT * FROM afiliados WHERE id_afiliado = ?");
$stmt->execute([$id]);
$afiliado = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Afiliado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.1">
    
</head>
<body class="bg-light p-4">
    <div class="container" style="max-width: 600px;">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white fw-bold">Editar Datos y Configuración Médica</div>
            <div class="card-body">
                <form action="editar.php?id=<?php echo $id; ?>" method="POST">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Documento</label>
                            <input type="text" name="documento" class="form-control" value="<?php echo $afiliado['documento']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label>Estado</label>
                            <select name="estado" class="form-select">
                                <option value="Activo" <?php echo ($afiliado['estado'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                                <option value="Inactivo" <?php echo ($afiliado['estado'] == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Nombre</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo $afiliado['nombre']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label>Apellido</label>
                            <input type="text" name="apellido" class="form-control" value="<?php echo $afiliado['apellido']; ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" class="form-control" value="<?php echo $afiliado['telefono']; ?>">
                        </div>
                        <div class="col-md-6">
                            <label>Correo</label>
                            <input type="email" name="correo" class="form-control" value="<?php echo $afiliado['correo']; ?>">
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-warning fw-bold mb-3">Configuración del Smart Coach</h6>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label>Tipo de Cuerpo (Somatotipo)</label>
                            <select name="somatotipo" class="form-select">
                                <option value="Ectomorfo" <?php echo ($afiliado['somatotipo'] == 'Ectomorfo') ? 'selected' : ''; ?>>Ectomorfo (Delgado)</option>
                                <option value="Mesomorfo" <?php echo ($afiliado['somatotipo'] == 'Mesomorfo') ? 'selected' : ''; ?>>Mesomorfo (Atlético)</option>
                                <option value="Endomorfo" <?php echo ($afiliado['somatotipo'] == 'Endomorfo') ? 'selected' : ''; ?>>Endomorfo (Ancho)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Objetivo Fitness</label>
                            <select name="objetivo" class="form-select">
                                <option value="Volumen" <?php echo ($afiliado['objetivo'] == 'Volumen') ? 'selected' : ''; ?>>Ganar Volumen</option>
                                <option value="Definición" <?php echo ($afiliado['objetivo'] == 'Definición') ? 'selected' : ''; ?>>Definición / Pérdida de Grasa</option>
                                <option value="Mantenimiento" <?php echo ($afiliado['objetivo'] == 'Mantenimiento') ? 'selected' : ''; ?>>Mantenimiento</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="perfil.php?id=<?php echo $id; ?>" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>