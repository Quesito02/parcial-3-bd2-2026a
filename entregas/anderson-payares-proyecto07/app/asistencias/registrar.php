<?php
// entregas/anderson-payares-proyecto07/asistencias/registrar.php
require_once '../conexion.php';

$mensaje = "";

// 1. CARGAR TODOS LOS AFILIADOS PARA EL DESPLEGABLE
try {
    $stmtAtletas = $pdo->query("SELECT id_afiliado, documento, nombre, apellido FROM afiliados ORDER BY nombre ASC");
    $afiliados = $stmtAtletas->fetchAll();
} catch (PDOException $e) {
    $mensaje = "<div class='alert alert-danger fw-bold text-center shadow-sm'>Error al cargar atletas: " . $e->getMessage() . "</div>";
    $afiliados = [];
}

// 2. PROCESAR EL FORMULARIO CUANDO SE DA CLIC EN REGISTRAR ENTRADA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_afiliado = $_POST['id_afiliado'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $observaciones = !empty($_POST['observaciones']) ? trim($_POST['observaciones']) : 'Entrada Manual';

    if (!empty($id_afiliado)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO asistencias (id_afiliado, fecha, hora, observaciones) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id_afiliado, $fecha, $hora, $observaciones]);
            
            $mensaje = "<div class='alert alert-success fw-bold text-center shadow-sm'><i class='bi bi-check-circle-fill me-2'></i> ¡Asistencia registrada con éxito!</div>";
        } catch (PDOException $e) {
            $mensaje = "<div class='alert alert-danger fw-bold text-center shadow-sm'><i class='bi bi-exclamation-triangle-fill me-2'></i> Error al guardar la asistencia: " . $e->getMessage() . "</div>";
        }
    } else {
        $mensaje = "<div class='alert alert-warning fw-bold text-center shadow-sm'>Por favor, seleccione un afiliado válido.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Entrada - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.1">

    <style>
        body { 
            background-color: #111 !important; 
            background-image: linear-gradient(rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.85)), url('../assets/img/gym-background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #fff !important; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .kings-card { 
            background: #212529 !important; 
            border: 1px solid rgba(255,193,7,0.3) !important; 
            border-radius: 20px !important; 
            box-shadow: 0 15px 45px rgba(0,0,0,0.8) !important; 
        }
        .form-control, .form-select { 
            background-color: #151719 !important; 
            border: 1px solid #3c4248 !important; 
            color: #fff !important; 
            padding: 12px 15px !important;
            border-radius: 10px !important;
        }
        .form-control:focus, .form-select:focus { 
            border-color: #ffc107 !important; 
            box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.2) !important; 
            background-color: #151719 !important;
            color: #fff !important;
        }
        .form-select option {
            background-color: #212529 !important;
            color: #fff !important;
        }
        .btn-kings { 
            background-color: #ffc107 !important; 
            color: #000 !important; 
            font-weight: bold !important; 
            transition: all 0.3s !important; 
            padding: 12px !important;
        }
        .btn-kings:hover { background-color: #fff !important; color: #000 !important; }
        .back-link { color: #888; text-decoration: none; transition: 0.3s; }
        .back-link:hover { color: #ffc107; }
    </style>
</head>
<body>

    <div class="container py-5" style="max-width: 600px;">
        <div class="text-center mb-4">
            <i class="bi bi-door-open-fill text-warning" style="font-size: 3.5rem;"></i>
            <h2 class="fw-bolder mt-2" style="letter-spacing: -1px;">REGISTRAR ENTRADA</h2>
            <p class="text-secondary">Panel de Control de Asistencias Manuales</p>
        </div>

        <?php echo $mensaje; ?>

        <div class="kings-card p-4 p-md-5 mt-3">
            <form action="registrar.php" method="POST">
                <div class="row g-3">
                    
                    <div class="col-12">
                        <label class="form-label text-warning fw-bold small">Afiliado que ingresa:</label>
                        <select name="id_afiliado" class="form-select" required>
                            <option value="" disabled selected>-- Seleccione un Afiliado --</option>
                            <?php foreach ($afiliados as $atleta): ?>
                                <option value="<?php echo $atleta['id_afiliado']; ?>">
                                    <?php echo htmlspecialchars($atleta['documento'] . " - " . $atleta['nombre'] . " " . $atleta['apellido']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label text-warning fw-bold small">Fecha:</label>
                        <input type="date" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-warning fw-bold small">Hora de Entrada:</label>
                        <input type="time" name="hora" class="form-control" value="<?php echo date('H:i'); ?>" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-warning fw-bold small">Observaciones (Opcional):</label>
                        <input type="text" name="observaciones" class="form-control" placeholder="Ej: Ingreso sin tarjeta, clase de cortesía...">
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-kings w-100 rounded-pill shadow-sm">
                            <i class="bi bi-plus-circle-fill me-2"></i> REGISTRAR ENTRADA
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="text-center mt-4">
            <a href="../index.php" class="back-link"><i class="bi bi-arrow-left-circle me-1"></i> Volver al panel de control</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>