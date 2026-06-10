<?php
// app/index.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'conexion.php';

$mostrarModal = false;
$checkInStatus = "";
$checkInMessage = "";
$atletaNombre = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ==========================================
    // CANAL 1: PROCESAR INGRESO (CHECK-IN EXPRESS)
    // ==========================================
    if (isset($_POST['express_doc'])) {
        $doc = trim($_POST['express_doc']);
        $mostrarModal = true;
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM afiliados WHERE documento = ?");
            $stmt->execute([$doc]);
            $afiliado = $stmt->fetch();
            
            if ($afiliado) {
                $id_atleta = $afiliado['id_afiliado'];
                $atletaNombre = $afiliado['nombre'] . " " . $afiliado['apellido'];
                
                // A) VALIDACIÓN ANTI-PASSBACK: ¿Ya está adentro?
                $stmtLast = $pdo->prepare("SELECT observaciones FROM asistencias WHERE id_afiliado = ? ORDER BY fecha DESC, hora DESC LIMIT 1");
                $stmtLast->execute([$id_atleta]);
                $ultimoReg = $stmtLast->fetch();
                
                if ($ultimoReg && $ultimoReg['observaciones'] === 'Check-in Express') {
                    $checkInStatus = "DENIED";
                    $checkInMessage = "⚠️ ACCESO DENEGADO: Ya figuras dentro del gimnasio. Debes marcar SALIDA antes de volver a entrar.";
                } else {
                    // B) VALIDACIÓN DE LÍMITE: ¿Ya entró 2 veces hoy?
                    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM asistencias WHERE id_afiliado = ? AND fecha = CURDATE() AND observaciones = 'Check-in Express'");
                    $stmtCount->execute([$id_atleta]);
                    $ingresosHoy = $stmtCount->fetchColumn();
                    
                    if ($ingresosHoy >= 2) {
                        $checkInStatus = "DENIED";
                        $checkInMessage = "❌ LÍMITE ALCANZADO: Este atleta ya utilizó sus 2 ingresos permitidos por el día de hoy.";
                    } else {
                        // C) VALIDACIÓN DE MEMBRESÍA VIGENTE
                        $stmtMem = $pdo->prepare("SELECT * FROM membresias WHERE id_afiliado = ? AND estado = 'Activa' AND fecha_fin >= CURDATE() LIMIT 1");
                        $stmtMem->execute([$id_atleta]);
                        $membresia = $stmtMem->fetch();
                        
                        if ($membresia) {
                            $checkInStatus = "SUCCESS";
                            $checkInMessage = "¡INGRESO AUTORIZADO! Cupo diario usado: (" . ($ingresosHoy + 1) . "/2). Vence: " . $membresia['fecha_fin'];
                            
                            $stmtAsis = $pdo->prepare("INSERT INTO asistencias (id_afiliado, fecha, hora, observaciones) VALUES (?, CURDATE(), CURTIME(), 'Check-in Express')");
                            $stmtAsis->execute([$id_atleta]);
                        } else {
                            $checkInStatus = "DENIED";
                            $checkInMessage = "Acceso bloqueado. El atleta no cuenta con membresías activas vigentes.";
                        }
                    }
                }
            } else {
                $checkInStatus = "NOT_FOUND";
                $checkInMessage = "El documento ingresado no se encuentra registrado.";
            }
        } catch (PDOException $e) {
            $checkInStatus = "ERROR"; $checkInMessage = "Error: " . $e->getMessage();
        }
    }

    // ==========================================
    // CANAL 2: PROCESAR SALIDA (SALIDA EXPRESS)
    // ==========================================
    if (isset($_POST['exit_doc'])) {
        $doc = trim($_POST['exit_doc']);
        $mostrarModal = true;
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM afiliados WHERE documento = ?");
            $stmt->execute([$doc]);
            $afiliado = $stmt->fetch();
            
            if ($afiliado) {
                $id_atleta = $afiliado['id_afiliado'];
                $atletaNombre = $afiliado['nombre'] . " " . $afiliado['apellido'];
                
                // VALIDACIÓN: ¿Realmente está adentro?
                $stmtLast = $pdo->prepare("SELECT observaciones FROM asistencias WHERE id_afiliado = ? ORDER BY fecha DESC, hora DESC LIMIT 1");
                $stmtLast->execute([$id_atleta]);
                $ultimoReg = $stmtLast->fetch();
                
                if ($ultimoReg && $ultimoReg['observaciones'] === 'Check-in Express') {
                    $checkInStatus = "EXIT_SUCCESS";
                    $checkInMessage = "Salida confirmada. ¡Gracias por entrenar hoy en Gym Kings!";
                    
                    $stmtAsis = $pdo->prepare("INSERT INTO asistencias (id_afiliado, fecha, hora, observaciones) VALUES (?, CURDATE(), CURTIME(), 'Salida Express')");
                    $stmtAsis->execute([$id_atleta]);
                } else {
                    $checkInStatus = "DENIED";
                    $checkInMessage = "❌ ERROR: No puedes marcar salida porque no figuras dentro del gimnasio actualmente.";
                }
            } else {
                $checkInStatus = "NOT_FOUND";
                $checkInMessage = "El documento ingresado no se encuentra registrado.";
            }
        } catch (PDOException $e) {
            $checkInStatus = "ERROR"; $checkInMessage = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Kings - Inicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { 
            background-color: #111;
            background-image: linear-gradient(rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.85)), url('assets/img/gym-background.jpg');
            background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed;
            height: 100vh; display: flex; flex-direction: column; font-family: 'Segoe UI', sans-serif; color: #fff; 
        }
        .center-wrapper { flex-grow: 1; display: flex; align-items: center; justify-content: center; flex-direction: column; }
        .center-wrapper h1 { color: #fff !important; text-shadow: 0 4px 15px rgba(255,193,7,0.3); }
        
        .search-box { width: 100%; max-width: 650px; box-shadow: 0 10px 30px rgba(0,0,0,0.7); border-radius: 50px; overflow: hidden; transition: all 0.3s ease; border: 1px solid rgba(255, 193, 7, 0.15); }
        .search-box:focus-within { transform: scale(1.02); box-shadow: 0 0 35px rgba(255, 193, 7, 0.35); border-color: #ffc107; }
        .search-input { border: none; padding: 20px 30px; font-size: 1.3rem; background: #fff; color: #000; }
        .search-input:focus { outline: none; box-shadow: none; background: #fff; color: #000; }
        .search-btn { background: #ffc107; color: #000; border: none; padding: 0 35px; font-size: 1.5rem; transition: background 0.3s; }
        .search-btn:hover { background: #fff; color: #000; }
        
        .checkin-express-menu { position: absolute; top: 25px; left: 30px; max-width: 280px; }
        .admin-menu { position: absolute; top: 25px; right: 30px; }

        /* 🚪 BARRA INFERIOR IZQUIERDA EXCLUSIVA DE SALIDAS */
        .checkout-express-bar { 
            position: fixed; bottom: 25px; left: 30px; z-index: 1040; max-width: 280px; width: 100%;
            background: rgba(20, 20, 20, 0.9); backdrop-filter: blur(5px); padding: 15px;
            border-radius: 20px; border: 1px solid rgba(13, 202, 240, 0.3); box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        /* Estilos del Bot */
        .bot-wrapper { position: fixed; bottom: 25px; right: 30px; z-index: 1050; }
        .bot-bubble { width: 65px; height: 65px; background: #212529; color: #ffc107; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; cursor: pointer; box-shadow: 0 8px 25px rgba(0,0,0,0.5); border: 2px solid #ffc107; }
        .bot-card { width: 350px; height: 480px; display: none; flex-direction: column; background: white; border-radius: 20px; box-shadow: 0 15px 50px rgba(0,0,0,0.4); overflow: hidden; color: #000; }
        .bot-header { background: #212529; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .bot-body { flex-grow: 1; padding: 15px; overflow-y: auto; background: #f8f9fa; font-size: 0.92rem; scroll-behavior: smooth; }
        .chat-bubble-bot { background: #e9ecef; color: #212529; padding: 10px 14px; border-radius: 15px 15px 15px 0; margin-bottom: 12px; max-width: 85%; }
        .chat-bubble-user { background: #212529; color: #ffc107; padding: 10px 14px; border-radius: 15px 15px 0 15px; margin-bottom: 12px; max-width: 85%; margin-left: auto; text-align: right; }
        .bot-footer { padding: 10px; background: white; border-top: 1px solid #eee; }
    </style>
</head>
<body>

    <div class="checkin-express-menu">
        <label class="form-label fw-bold mb-1 text-success" style="font-size: 0.85rem; letter-spacing: 0.5px;">
            <i class="bi bi-box-arrow-in-right text-success"></i> REGISTRAR INGRESO (MAX 2)
        </label>
        <form action="index.php" method="POST" class="input-group shadow-sm" style="border-radius: 30px; overflow: hidden; border: 1px solid rgba(25,135,84,0.4);">
            <input type="text" name="express_doc" class="form-control border-0 bg-dark text-white placeholder-light py-2" placeholder="Documento para Entrar..." required>
            <button class="btn btn-success border-0 fw-bold px-3" type="submit">OK</button>
        </form>
    </div>

    <div class="admin-menu dropdown">
        <button class="btn btn-warning btn-lg dropdown-toggle rounded-pill px-4 shadow-sm text-dark fw-bold" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-grid-3x3-gap-fill me-2"></i> Módulos
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-2" style="background: rgba(255,255,255,0.95); backdrop-filter: blur(5px);">
            <li><a class="dropdown-item py-2 rounded" href="afiliados/listar.php"><i class="bi bi-people me-2 text-primary"></i> Afiliados</a></li>
            <li><a class="dropdown-item py-2 rounded" href="instructores/listar.php"><i class="bi bi-person-badge me-2 text-dark"></i> Instructores</a></li>
            <li><a class="dropdown-item py-2 rounded" href="planes/listar.php"><i class="bi bi-card-checklist me-2 text-success"></i> Planes</a></li>
            <li><a class="dropdown-item py-2 rounded" href="membresias/listar.php"><i class="bi bi-tags me-2 text-info"></i> Membresías</a></li>
            <li><a class="dropdown-item py-2 rounded" href="clases/listar.php"><i class="bi bi-bicycle me-2 text-warning"></i> Clases</a></li>
            <li><a class="dropdown-item py-2 rounded" href="asistencias/listar.php"><i class="bi bi-door-open me-2 text-secondary"></i> Asistencias</a></li>
            <li><a class="dropdown-item py-2 rounded" href="reportes/ingresos.php"><i class="bi bi-graph-up-arrow me-2 text-success"></i> Contabilidad y Reportes</a></li>            <li><a class="dropdown-item py-2 rounded" href="backup.php"><i class="bi bi-shield-lock me-2 text-info"></i> Copia de Seguridad</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item py-2 rounded" href="bonos/listar.php"><i class="bi bi-gift-fill me-2 text-danger"></i> King Rewards</a></li>
            <li><a class="dropdown-item py-2 text-danger fw-bold rounded" href="vencimientos/listar.php"><i class="bi bi-exclamation-triangle me-2"></i> Vencimientos</a></li>
        </ul>
    </div>

    <div class="center-wrapper text-center px-4">
        <i class="bi bi-award-fill text-warning mb-2" style="font-size: 5.5rem; filter: drop-shadow(0 0 20px rgba(255,193,7,0.65));"></i>
        <h1 class="display-1 fw-bolder mb-1" style="letter-spacing: -3px;">GYM KINGS</h1>
        <h5 class="fw-light mb-4 fst-italic">"Estando con los buenos, nos volveremos mejores."</h5>
        
        <form action="afiliados/buscar.php" method="GET" class="search-box d-flex bg-white mt-3">
            <input type="search" name="q" class="form-control search-input" placeholder="Buscar atleta por documento o nombre..." required autofocus>
            <button type="submit" class="search-btn"><i class="bi bi-search"></i></button>
        </form>
    </div>

    <div class="checkout-express-bar">
        <label class="form-label fw-bold mb-1 text-info" style="font-size: 0.85rem; letter-spacing: 0.5px;">
            <i class="bi bi-box-arrow-left text-info"></i> MARCAR SALIDA EXPRESS
        </label>
        <form action="index.php" method="POST" class="input-group" style="border-radius: 30px; overflow: hidden;">
            <input type="text" name="exit_doc" class="form-control border-0 bg-dark text-white placeholder-light py-2" placeholder="Documento para Salir..." required>
            <button class="btn btn-info border-0 fw-bold px-3 text-dark" type="submit">SALIR</button>
        </form>
    </div>

    <div class="bot-wrapper">
        <div class="bot-bubble" id="openBotBtn"><i class="bi bi-robot"></i></div>
        <div class="bot-card" id="botCard">
            <div class="bot-header">
                <div class="d-flex align-items-center">
                    <i class="bi bi-cpu-fill text-warning me-2" style="font-size: 1.2rem;"></i>
                    <div><span class="fw-bold d-block" style="font-size: 0.95rem;">Smart Coach</span><small class="text-warning" style="font-size: 0.75rem;">En línea</small></div>
                </div>
                <button type="button" class="btn-close btn-close-white btn-sm" id="closeBotBtn"></button>
            </div>
            <div class="bot-body" id="chatBody">
                <div class="chat-bubble-bot">¡Hola! Digita tu documento para chatear:</div>
            </div>
            <div class="bot-footer">
                <form id="botForm" class="input-group">
                    <input type="text" id="botInput" class="form-control form-control-sm border-secondary-subtle rounded-start-pill ps-3" placeholder="Documento..." required>
                    <button class="btn btn-dark btn-sm rounded-end-pill px-3 text-warning" type="submit"><i class="bi bi-send-fill"></i></button>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="checkInResultModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered p-3">
            <div class="modal-content border-0 shadow-lg" style="color: #000;">
                <?php if ($checkInStatus === 'SUCCESS'): ?>
                    <div class="modal-header bg-success text-white border-0 py-3">
                        <h5 class="modal-title fw-bold"><i class="bi bi-check-circle-fill me-2"></i> INGRESO PERMITIDO</h5>
                    </div>
                    <div class="modal-body text-center p-4">
                        <i class="bi bi-person-check-fill text-success display-3 mb-3 d-block"></i>
                        <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($atletaNombre); ?></h4>
                        <p class="text-secondary mb-0"><?php echo htmlspecialchars($checkInMessage); ?></p>
                    </div>
                <?php elseif ($checkInStatus === 'EXIT_SUCCESS'): ?>
                    <div class="modal-header bg-info text-dark border-0 py-3 fw-bold">
                        <h5 class="modal-title fw-bold"><i class="bi bi-box-arrow-left me-2"></i> SALIDA PROCESADA</h5>
                    </div>
                    <div class="modal-body text-center p-4">
                        <i class="bi bi-door-open-fill text-info display-3 mb-3 d-block"></i>
                        <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($atletaNombre); ?></h4>
                        <p class="text-secondary mb-0"><?php echo htmlspecialchars($checkInMessage); ?></p>
                    </div>
                <?php else: ?>
                    <div class="modal-header bg-danger text-white border-0 py-3">
                        <h5 class="modal-title fw-bold"><i class="bi bi-shield-x me-2"></i> RECHAZO DE ACCESO</h5>
                    </div>
                    <div class="modal-body text-center p-4">
                        <i class="bi bi-person-x-fill text-danger display-3 mb-3 d-block"></i>
                        <h4 class="fw-bold mb-1"><?php echo !empty($atletaNombre) ? htmlspecialchars($atletaNombre) : "Operación Inválida"; ?></h4>
                        <p class="text-secondary mb-0"><?php echo htmlspecialchars($checkInMessage); ?></p>
                    </div>
                <?php endif; ?>
                <div class="modal-footer border-0 bg-light d-flex justify-content-center py-2 rounded-bottom">
                    <button type="button" class="btn btn-dark rounded-pill px-4" data-bs-dismiss="modal">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JS del Bot
        const openBotBtn = document.getElementById('openBotBtn'); const closeBotBtn = document.getElementById('closeBotBtn'); const botCard = document.getElementById('botCard'); const botForm = document.getElementById('botForm'); const botInput = document.getElementById('botInput'); const chatBody = document.getElementById('chatBody');
        let isSessionActive = false; openBotBtn.addEventListener('click', () => { botCard.style.display = 'flex'; openBotBtn.style.display = 'none'; }); closeBotBtn.addEventListener('click', () => { botCard.style.display = 'none'; openBotBtn.style.display = 'flex'; });
        function printMessage(sender, text) { const div = document.createElement('div'); div.className = sender === 'user' ? 'chat-bubble-user' : 'chat-bubble-bot'; div.innerHTML = text; chatBody.appendChild(div); chatBody.scrollTop = chatBody.scrollHeight; }
        botForm.addEventListener('submit', function(e) { e.preventDefault(); const inputVal = botInput.value.trim(); if(!inputVal) return; printMessage('user', inputVal); botInput.value = ""; const formData = new FormData(); if(!isSessionActive) { formData.append('bot_action', 'login'); formData.append('documento', inputVal); } else { formData.append('bot_action', 'chat'); formData.append('mensaje', inputVal); } fetch('index.php', { method: 'POST', body: formData }).then(r => r.json()).then(data => { if(data.status === 'AUTH_SUCCESS') { isSessionActive = true; botInput.placeholder = "Duda..."; printMessage('bot', data.html); } else { printMessage('bot', data.html); } }).catch(err => console.error(err)); });
    </script>
    <?php if ($mostrarModal): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var myModal = new bootstrap.Modal(document.getElementById('checkInResultModal'));
            myModal.show();
        });
    </script>
    <?php endif; ?>
</body>
</html>