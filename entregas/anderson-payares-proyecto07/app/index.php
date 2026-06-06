<?php
// app/index.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Kings - Inicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { 
            background-color: #f4f7f6; 
            height: 100vh; 
            display: flex; 
            flex-direction: column; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .center-wrapper { flex-grow: 1; display: flex; align-items: center; justify-content: center; flex-direction: column; }
        .search-box { width: 100%; max-width: 650px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border-radius: 50px; overflow: hidden; transition: transform 0.2s; }
        .search-box:focus-within { transform: scale(1.02); box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15); }
        .search-input { border: none; padding: 20px 30px; font-size: 1.3rem; }
        .search-input:focus { outline: none; box-shadow: none; }
        .search-btn { background: #212529; color: #ffc107; border: none; padding: 0 35px; font-size: 1.5rem; transition: background 0.3s; }
        .search-btn:hover { background: #000; }
        .admin-menu { position: absolute; top: 25px; right: 30px; }
    </style>
</head>
<body>

    <div class="admin-menu dropdown">
        <button class="btn btn-dark btn-lg dropdown-toggle rounded-pill px-4 shadow-sm" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-grid-3x3-gap-fill text-warning me-2"></i> Módulos
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
            <li><a class="dropdown-item py-2" href="afiliados/listar.php"><i class="bi bi-people me-2 text-primary"></i> Afiliados</a></li>
            <li><a class="dropdown-item py-2" href="instructores/listar.php"><i class="bi bi-person-badge me-2 text-dark"></i> Instructores</a></li>
            <li><a class="dropdown-item py-2" href="planes/listar.php"><i class="bi bi-card-checklist me-2 text-success"></i> Planes</a></li>
            <li><a class="dropdown-item py-2" href="membresias/listar.php"><i class="bi bi-tags me-2 text-info"></i> Membresías</a></li>
            <li><a class="dropdown-item py-2" href="clases/listar.php"><i class="bi bi-bicycle me-2 text-warning"></i> Clases</a></li>
            <li><a class="dropdown-item py-2" href="asistencias/listar.php"><i class="bi bi-door-open me-2 text-secondary"></i> Asistencias</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item py-2 text-danger fw-bold" href="vencimientos/listar.php"><i class="bi bi-exclamation-triangle me-2"></i> Vencimientos</a></li>
        </ul>
    </div>

    <div class="center-wrapper text-center px-4">
        <i class="bi bi-award-fill text-warning mb-2" style="font-size: 5rem; filter: drop-shadow(0 0 10px rgba(255,193,7,0.4));"></i>
        <h1 class="display-2 fw-bolder mb-1 text-dark" style="letter-spacing: -2px;">GYM KINGS</h1>
        <h5 class="text-secondary fw-light mb-4 fst-italic">"Estando con los buenos, nos volveremos mejores."</h5>
        
        <form action="afiliados/buscar.php" method="GET" class="search-box d-flex bg-white mt-3">
            <input type="search" name="q" class="form-control search-input" placeholder="Buscar atleta por documento o nombre..." required autofocus>
            <button type="submit" class="search-btn"><i class="bi bi-search"></i></button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>