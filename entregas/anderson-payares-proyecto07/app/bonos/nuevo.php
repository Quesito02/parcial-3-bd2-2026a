<?php
// app/bonos/nuevo.php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_afiliado = $_POST['id_afiliado'];
    $motivo = $_POST['motivo'];
    $tipo_bono = $_POST['tipo_bono'];
    $cantidad = $_POST['cantidad'];

    try {
        // Se inserta el bono con estado 'Disponible' y la fecha actual del sistema
        $sql = "INSERT INTO bonos (id_afiliado, motivo, tipo_bono, cantidad, estado, fecha_emision) VALUES (?, ?, ?, ?, 'Disponible', CURDATE())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_afiliado, $motivo, $tipo_bono, $cantidad]);
        
        header("Location: listar.php");
        exit();
    } catch (PDOException $e) {
        die("Error al otorgar el bono: " . $e->getMessage());
    }
}

// Traemos los afiliados activos para mostrarlos en la lista desplegable
$afiliados = $pdo->query("SELECT id_afiliado, nombre, apellido, documento FROM afiliados WHERE estado = 'Activo' ORDER BY nombre ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Otorgar Bono - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.1">
</head>
<body class="bg-light p-4">
    <div class="container" style="max-width: 600px;">
        <div class="card shadow border-0 mt-3">
            <div class="card-header bg-dark text-white fw-bold py-3 d-flex align-items-center">
                <i class="bi bi-gift-fill text-warning me-2 fs-5"></i> 
                <span>Otorgar King Reward</span>
            </div>
            <div class="card-body p-4">
                <form action="nuevo.php" method="POST">
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Atleta Ganador / Beneficiario</label>
                        <select name="id_afiliado" class="form-select border-secondary-subtle" required>
                            <option value="">-- Selecciona un afiliado --</option>
                            <?php foreach ($afiliados as $a): ?>
                                <option value="<?php echo $a['id_afiliado']; ?>">
                                    <?php echo htmlspecialchars($a['nombre'] . ' ' . $a['apellido'] . ' (Doc: ' . $a['documento'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Motivo del Bono</label>
                        <input type="text" name="motivo" class="form-control border-secondary-subtle" placeholder="Ej: Ganador Reto Dominadas, Promoción Cumpleaños..." required>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo de Recompensa</label>
                            <select name="tipo_bono" id="tipo_bono" class="form-select border-secondary-subtle" required>
                                <option value="Descuento $">Descuento en Dinero ($)</option>
                                <option value="Días Extra">Días Extra de Membresía</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" id="label_cantidad">Valor del Descuento</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark text-warning border-0" id="icono_cantidad"><i class="bi bi-currency-dollar"></i></span>
                                <input type="number" name="cantidad" class="form-control border-secondary-subtle" placeholder="Ej: 15000" required>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-5">
                        <a href="listar.php" class="btn btn-outline-secondary rounded-pill px-4">Cancelar</a>
                        <button type="submit" class="btn btn-warning fw-bold rounded-pill px-5 text-dark shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Asignar Bono
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Script interactivo para cambiar el icono y el texto según el tipo de bono elegido
        const selectTipo = document.getElementById('tipo_bono');
        const labelCantidad = document.getElementById('label_cantidad');
        const iconoCantidad = document.getElementById('icono_cantidad');

        selectTipo.addEventListener('change', function() {
            if (this.value === 'Días Extra') {
                labelCantidad.innerText = 'Cantidad de Días';
                iconoCantidad.innerHTML = '<i class="bi bi-calendar-plus"></i>';
            } else {
                labelCantidad.innerText = 'Valor del Descuento';
                iconoCantidad.innerHTML = '<i class="bi bi-currency-dollar"></i>';
            }
        });
    </script>
</body>
</html>