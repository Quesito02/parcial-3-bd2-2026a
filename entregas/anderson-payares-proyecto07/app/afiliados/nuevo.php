<?php
// afiliados/nuevo.php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $documento = $_POST['documento'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $somatotipo = $_POST['somatotipo'];
    $objetivo = $_POST['objetivo'];

    try {
        $sql = "INSERT INTO afiliados (documento, nombre, apellido, telefono, correo, estado, somatotipo, objetivo) VALUES (?, ?, ?, ?, ?, 'Activo', ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$documento, $nombre, $apellido, $telefono, $correo, $somatotipo, $objetivo]);
        header("Location: listar.php");
        exit();
    } catch (PDOException $e) {
        die("Error al registrar: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Atleta - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light p-4">
    <div class="container" style="max-width: 600px;">
        <div class="card shadow border-0 mt-3">
            <div class="card-header bg-dark text-white fw-bold py-3">
                <i class="bi bi-person-plus-fill text-warning me-2"></i> Registrar Nuevo Atleta
            </div>
            <div class="card-body p-4">
                <form action="nuevo.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Documento de Identidad</label>
                        <input type="text" name="documento" class="form-control" placeholder="Ej: 1005..." required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Apellido</label>
                            <input type="text" name="apellido" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="text" name="telefono" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Correo Electrónico</label>
                            <input type="email" name="correo" class="form-control">
                        </div>
                    </div>
                    <hr class="text-muted">
                    <h6 class="text-warning fw-bold mb-3">Perfil de Evaluación Smart Coach</h6>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo de Cuerpo</label>
                            <select name="somatotipo" class="form-select">
                                <option value="Ectomorfo">Ectomorfo (Delgado)</option>
                                <option value="Mesomorfo" selected>Mesomorfo (Atlético)</option>
                                <option value="Endomorfo">Endomorfo (Ancho)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Objetivo Fitness</label>
                            <select name="objetivo" class="form-select">
                                <option value="Volumen">Ganar Masa Muscular</option>
                                <option value="Definición">Definición / Quema de Grasa</option>
                                <option value="Mantenimiento" selected>Mantenimiento</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="listar.php" class="btn btn-secondary rounded-pill px-4">Cancelar</a>
                        <button type="submit" class="btn btn-dark rounded-pill px-4">Guardar Atleta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>