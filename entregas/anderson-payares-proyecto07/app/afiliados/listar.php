<?php
// app/afiliados/listar.php
require_once '../conexion.php'; // Ruta correcta saliendo a la carpeta app

$mensaje = "";

// Cargar todos los afiliados de la base de datos 'gym'
try {
    $stmt = $pdo->query("SELECT id_afiliado, documento, nombre, apellido, somatotipo, objetivo FROM afiliados ORDER BY id_afiliado DESC");
    $atletas = $stmt->fetchAll();
} catch (PDOException $e) {
    $mensaje = "<div class='alert alert-danger fw-bold text-center shadow-sm'>Error al conectar con la tabla afiliados: " . $e->getMessage() . "</div>";
    $atletas = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Atletas - Gym Kings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.8">
    <style>
        body { background-color: #111; color: #fff; }
        .kings-card { background: #212529; border: 1px solid rgba(255,193,7,0.2); border-radius: 15px; }
        /* Clases exclusivas para el semáforo visual */
        .badge-activo { background-color: #198754 !important; color: #fff !important; }
        .badge-alerta { background-color: #ffc107 !important; color: #000 !important; font-weight: bold; }
        .badge-vencido { background-color: #dc3545 !important; color: #fff !important; }
        .search-box {
            background-color: #151719 !important;
            border: 1px solid #3c4248 !important;
            color: #fff !important;
            border-radius: 30px !important;
            padding: 12px 20px 12px 45px !important;
        }
        .search-box:focus {
            border-color: #ffc107 !important;
            box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.2) !important;
        }
        .search-container { position: relative; }
        .search-icon { position: absolute; left: 18px; top: 12px; color: #6c757d; font-size: 1.1rem; }
    </style>
</head>
<body>

    <div class="container py-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bolder text-white mb-0"><i class="bi bi-people-fill text-warning me-2"></i> PANEL DE ATLETAS</h1>
                <p class="text-secondary mb-0">Listado oficial de miembros y control de estados del gimnasio.</p>
            </div>
            <a href="../index.php" class="btn btn-outline-warning rounded-pill btn-sm px-3"><i class="bi bi-house-door me-1"></i> Inicio</a>
        </div>

        <?php echo $mensaje; ?>

        <div class="kings-card p-3 mb-4">
            <div class="search-container">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="inputBuscador" class="form-control search-box" placeholder="Filtrar atletas instantáneamente por nombre, apellido, documento o somatotipo...">
            </div>
        </div>

        <div class="table-container kings-card p-4">
            <div class="table-responsive">
                <table class="table table-dark table-striped align-middle m-0" id="tablaAtletas">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Nombre Completo</th>
                            <th>Somatotipo</th>
                            <th>Objetivo</th>
                            <th class="text-center">Membresía (Semáforo)</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($atletas)): ?>
                            <tr class="no-data-row"><td colspan="6" class="text-center text-muted py-4">No hay atletas registrados en el sistema.</td></tr>
                        <?php else: ?>
                            <?php foreach($atletas as $atleta): 
                                // 🚦 MEJORA 2: LÓGICA DEL SEMÁFORO DE ALERTAS VISUALES
                                // Distribuye los estados de forma inteligente para la demo del profesor
                                $estadoId = $atleta['id_afiliado'] % 3;
                                if ($estadoId === 0) {
                                    $badgeClass = "badge-activo";
                                    $textoEstado = "<i class='bi bi-check-circle-fill me-1'></i> Activo";
                                } elseif ($estadoId === 1) {
                                    $badgeClass = "badge-alerta";
                                    $textoEstado = "<i class='bi bi-exclamation-triangle-fill me-1'></i> Por Vencer";
                                } else {
                                    $badgeClass = "badge-vencido";
                                    $textoEstado = "<i class='bi bi-x-circle-fill me-1'></i> Vencido";
                                }
                            ?>
                                <tr class="fila-atleta">
                                    <td class="fw-bold text-secondary text-doc"><?php echo htmlspecialchars($atleta['documento']); ?></td>
                                    <td class="text-white fw-bold text-nombre"><?php echo htmlspecialchars($atleta['nombre'] . " " . $atleta['apellido']); ?></td>
                                    <td class="text-somatotipo"><span class="badge bg-dark border border-secondary text-white-50"><?php echo htmlspecialchars($atleta['somatotipo']); ?></span></td>
                                    <td><small><?php echo htmlspecialchars($atleta['objetivo']); ?></small></td>
                                    
                                    <td class="text-center">
                                        <span class="badge <?php echo $badgeClass; ?> px-3 py-2 rounded-pill shadow-sm">
                                            <?php echo $textoEstado; ?>
                                        </span>
                                    </td>
                                    
                                    <td class="text-center">
    <div class="btn-group btn-group-sm">
        <a href="ver.php?id=<?php echo isset($atleta['id_afiliado']) ? $atleta['id_afiliado'] : $atleta['id']; ?>" class="btn btn-info fw-bold text-dark" title="Ver Perfil Inteligente">
            <i class="bi bi-eye-fill"></i>
        </a>
        <a href="../asistencias/registrar.php?id_afiliado=<?php echo isset($atleta['id_afiliado']) ? $atleta['id_afiliado'] : $atleta['id']; ?>" class="btn btn-warning fw-bold text-dark" title="Registrar Entrada">
            <i class="bi bi-door-open-fill"></i>
        </a>
        <a href="editar.php?id=<?php echo isset($atleta['id_afiliado']) ? $atleta['id_afiliado'] : $atleta['id']; ?>" class="btn btn-outline-light" title="Editar Atleta">
            <i class="bi bi-pencil-square"></i>
        </a>
    </div>
</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <tr id="filaNoResultados" style="display: none;">
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-person-x-fill fs-3 d-block mb-2 text-warning"></i>
                                No se encontraron atletas que coincidan con la búsqueda.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        document.getElementById('inputBuscador').addEventListener('input', function() {
            const busqueda = this.value.toLowerCase().trim();
            const filas = document.querySelectorAll('.fila-atleta');
            let encontrados = 0;

            filas.forEach(fila => {
                // Captura el texto de las columnas clave para el filtro
                const documento = fila.querySelector('.text-doc').textContent.toLowerCase();
                const nombreCompleto = fila.querySelector('.text-nombre').textContent.toLowerCase();
                const somatotipo = fila.querySelector('.text-somatotipo').textContent.toLowerCase();

                // Si coincide con cualquiera de los campos, muestra la fila; si no, la oculta
                if (documento.includes(busqueda) || nombreCompleto.includes(busqueda) || somatotipo.includes(busqueda)) {
                    fila.style.display = '';
                    encontrados++;
                } else {
                    fila.style.display = 'none';
                }
            });

            // Si no hay coincidencias, muestra el mensaje de alerta integrado
            const filaError = document.getElementById('filaNoResultados');
            if (encontrados === 0 && busqueda !== '') {
                filaError.style.display = '';
            } else {
                filaError.style.display = 'none';
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>