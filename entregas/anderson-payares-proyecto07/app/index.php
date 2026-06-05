<?php
// app/index.php
require_once 'conexion.php';

try {
    // 1. Estadísticas para los KPIs
    $stmtAfiliados = $pdo->query("SELECT 
        COUNT(*) as total, 
        SUM(CASE WHEN estado = 'Activo' THEN 1 ELSE 0 END) as activos
        FROM afiliados");
    $statsAfiliados = $stmtAfiliados->fetch();
    
    $total_afiliados = $statsAfiliados['total'] ?? 0;
    $activos = $statsAfiliados['activos'] ?? 0;

    // 2. Ingresos Totales
    $stmtIngresos = $pdo->query("SELECT SUM(valor_pagado) as ingresos_totales FROM membresias");
    $ingresos = $stmtIngresos->fetch()['ingresos_totales'] ?? 0;

} catch (PDOException $e) {
    echo "Error cargando estadísticas: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GymManager PRO - Inicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Efecto hover para las tarjetas del menú */
        .card-menu { transition: transform 0.2s, box-shadow 0.2s; border: none; border-radius: 12px; }
        .card-menu:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
        .icon-huge { font-size: 3rem; color: #0d6efd; }
    </style>
</head>
<body class="bg-light">

    <!-- Navbar Minimalista (El buscador global se queda porque es brutal) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-fire text-warning"></i> GymManager PRO</a>
            <div class="ms-auto d-flex">
                <form action="afiliados/buscar.php" method="GET" class="d-flex">
                    <input class="form-control form-control-sm me-2" type="search" name="q" placeholder="Buscar atleta..." required>
                    <button class="btn btn-outline-warning btn-sm" type="submit"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container">
        
        <?php if (isset($_GET['asistencia']) && $_GET['asistencia'] === 'ok'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill"></i> ¡Check-in exitoso! Entrada registrada correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'no_existe'): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> Error: Documento no encontrado o afiliado inactivo.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- SECCIÓN 1: Check-in Express & KPIs Rápidos -->
        <div class="row mb-4 align-items-center">
            <div class="col-md-5">
                <div class="card shadow-sm border-0 bg-primary text-white" style="border-radius: 15px;">
                    <div class="card-body p-4 text-center">
                        <h4 class="fw-bold mb-3"><i class="bi bi-lightning-charge-fill text-warning"></i> Check-in Express</h4>
                        <p class="small mb-3">Pasa la tarjeta o digita el documento para ingresar</p>
                        <form action="asistencias/registrar_express.php" method="POST">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white border-0"><i class="bi bi-upc-scan text-primary"></i></span>
                                <input type="text" class="form-control border-0" name="documento" placeholder="Ej: 1001" autofocus required>
                                <button class="btn btn-warning fw-bold" type="submit">Entrar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="card shadow-sm border-0 h-100"><div class="card-body text-center">
                            <h6 class="text-muted">Total Afiliados</h6><h2 class="fw-bold"><?php echo $total_afiliados; ?></h2>
                        </div></div>
                    </div>
                    <div class="col-sm-4">
                        <div class="card shadow-sm border-0 h-100"><div class="card-body text-center">
                            <h6 class="text-muted">Activos</h6><h2 class="fw-bold text-success"><?php echo $activos; ?></h2>
                        </div></div>
                    </div>
                    <div class="col-sm-4">
                        <div class="card shadow-sm border-0 h-100"><div class="card-body text-center">
                            <h6 class="text-muted">Ingresos ($)</h6><h4 class="fw-bold text-primary mt-2"><?php echo number_format($ingresos, 0); ?></h4>
                        </div></div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4 text-muted">
        <h4 class="fw-bold mb-4 text-secondary">Módulos del Sistema</h4>

        <!-- SECCIÓN 2: El Portal (Tarjetas como hipervínculos) -->
        <div class="row g-4">
            
            <div class="col-md-4 col-sm-6">
                <div class="card card-menu shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-people-fill icon-huge mb-3 d-block"></i>
                        <h5 class="fw-bold">Directorio de Afiliados</h5>
                        <p class="text-muted small">Registrar, editar y ver historial 360.</p>
                        <a href="afiliados/listar.php" class="stretched-link"></a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6">
                <div class="card card-menu shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-tags-fill icon-huge text-success mb-3 d-block"></i>
                        <h5 class="fw-bold">Venta de Membresías</h5>
                        <p class="text-muted small">Asignar planes y cobrar mensualidades.</p>
                        <a href="membresias/listar.php" class="stretched-link"></a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6">
                <div class="card card-menu shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-exclamation-triangle-fill icon-huge text-danger mb-3 d-block"></i>
                        <h5 class="fw-bold">Centro de Vencimientos</h5>
                        <p class="text-muted small">Alertas de cuentas por cobrar esta semana.</p>
                        <a href="vencimientos/listar.php" class="stretched-link"></a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6">
                <div class="card card-menu shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-card-checklist icon-huge text-info mb-3 d-block"></i>
                        <h5 class="fw-bold">Planes del Gimnasio</h5>
                        <p class="text-muted small">Configurar precios y beneficios.</p>
                        <a href="planes/listar.php" class="stretched-link"></a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6">
                <div class="card card-menu shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-bicycle icon-huge text-warning mb-3 d-block"></i>
                        <h5 class="fw-bold">Clases Dirigidas</h5>
                        <p class="text-muted small">Programar spinning, yoga y cupos.</p>
                        <a href="clases/listar.php" class="stretched-link"></a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6">
                <div class="card card-menu shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-door-open-fill icon-huge text-secondary mb-3 d-block"></i>
                        <h5 class="fw-bold">Historial de Accesos</h5>
                        <p class="text-muted small">Auditar quién entró manual o por Express.</p>
                        <a href="asistencias/listar.php" class="stretched-link"></a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>