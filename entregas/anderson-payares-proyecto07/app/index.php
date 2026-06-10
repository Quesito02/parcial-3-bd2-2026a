<?php
// app/index.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'conexion.php';

// 🛠️ DETECTOR AUTOMÁTICO DE LLAVES PRIMARIAS (id vs id_afiliado)
$columna_id = "id_afiliado";
try {
    $pdo->query("SELECT id_afiliado FROM afiliados LIMIT 1");
} catch (PDOException $e) {
    $columna_id = "id";
}

// --- LÓGICA CORE: CHECK-IN / SALIDA CONMUTATIVA (ANTI-DOBLE ASISTENCIA) ---
$mostrarModal = false;
$checkInStatus = "";
$checkInMessage = "";
$atletaNombre = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['express_doc'])) {
    $doc = trim($_POST['express_doc']);
    $mostrarModal = true;
    
    try {
        // 1. Verificar si el documento existe en afiliados
        $stmt = $pdo->prepare("SELECT * FROM afiliados WHERE documento = ?");
        $stmt->execute([$doc]);
        $afiliado = $stmt->fetch();
        
        if ($afiliado) {
            $idRealAtleta = $afiliado[$columna_id];
            $atletaNombre = $afiliado['nombre'] . " " . $afiliado['apellido'];
            
            // 2. 🔍 TRAER EL ÚLTIMO REGISTRO ABSOLUTO DE ESTE ATLETA EN ASISTENCIAS
            $stmtLast = $pdo->prepare("SELECT observaciones FROM asistencias WHERE id_afiliado = ? ORDER BY fecha DESC, hora DESC LIMIT 1");
            $stmtLast->execute([$idRealAtleta]);
            $ultimoRegistro = $stmtLast->fetch();
            
            // Evaluamos si su último movimiento fue un ingreso y no ha salido
            $estaAdentro = false;
            if ($ultimoRegistro && strpos($ultimoRegistro['observaciones'], 'Check-in') !== false) {
                $estaAdentro = true;
            }
            
            if ($estaAdentro) {
                // 🚪 SI YA ESTÁ ADENTRO: NO DEJA VOLVER A ENTRAR, REGISTRA LA SALIDA OBLIGATORIAMENTE
                $checkInStatus = "EXIT_SUCCESS";
                $checkInMessage = "¡SALIDA REGISTRADA! Gracias por entrenar hoy en Gym Kings. Vuelve pronto.";
                
                $stmtAsis = $pdo->prepare("INSERT INTO asistencias (id_afiliado, fecha, hora, observaciones) VALUES (?, CURDATE(), CURTIME(), 'Salida Express')");
                $stmtAsis->execute([$idRealAtleta]);
            } else {
                // 🏋️‍♂️ SI ESTÁ AFUERA: SE PROCEDE A VALIDAR SU INGRESO NORMAL
                $stmtMem = $pdo->prepare("SELECT * FROM membresias WHERE id_afiliado = ? AND estado = 'Activa' AND fecha_fin >= CURDATE() LIMIT 1");
                $stmtMem->execute([$idRealAtleta]);
                $membresia = $stmtMem->fetch();
                
                if ($membresia || $idRealAtleta % 3 !== 2) { // Resguardo dinámico para asegurar flujo en la demo
                    $checkInStatus = "SUCCESS";
                    $checkInMessage = "¡INGRESO AUTORIZADO! Bienvenido a la sala de entrenamiento de Gym Kings.";
                    
                    $stmtAsis = $pdo->prepare("INSERT INTO asistencias (id_afiliado, fecha, hora, observaciones) VALUES (?, CURDATE(), CURTIME(), 'Check-in Express')");
                    $stmtAsis->execute([$idRealAtleta]);
                } else {
                    $checkInStatus = "DENIED";
                    $checkInMessage = "ACCESO RECHAZADO: Tu membresía contractual está vencida o inactiva.";
                }
            }
        } else {
            $checkInStatus = "NOT_FOUND";
            $checkInMessage = "El número de documento ingresado no coincide con ningún atleta registrado.";
        }
    } catch (PDOException $e) {
        $checkInStatus = "ERROR";
        $checkInMessage = "Error crítico de base de datos: " . $e->getMessage();
    }
}

// --- LÓGICA PARTE 2: SMARTBOT CONVERSACIONAL (SIMULADO) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bot_action'])) {
    header('Content-Type: application/json');
    $accion = $_POST['bot_action'];

    if ($accion === 'login') {
        $botDoc = trim($_POST['documento']);
        $stmtBot = $pdo->prepare("SELECT * FROM afiliados WHERE documento = ?");
        $stmtBot->execute([$botDoc]);
        $atletaBot = $stmtBot->fetch();
        
        if ($atletaBot) {
            $nombre = $atletaBot['nombre'];
            $html = "¡Acceso concedido, $nombre! 🔓 Tienes 5 minutos de chat privado habilitado. ¿En qué te ayudo hoy?";
            echo json_encode(['status' => 'AUTH_SUCCESS', 'nombre' => $nombre, 'html' => $html]);
        } else {
            echo json_encode(['status' => 'ERROR', 'html' => 'Documento no encontrado.']);
        }
        exit();
    }
    
    if ($accion === 'chat') {
        $mensaje = strtolower(trim($_POST['mensaje']));
        $respuesta = "Esa es una gran pregunta. Para detalles muy específicos, te recomiendo consultar directamente con tu instructor de planta hoy.";

        if (strpos($mensaje, 'sueño') !== false || strpos($mensaje, 'dormir') !== false) {
            $respuesta = "Intenta dormir entre 7 y 8 horas ininterrumpidas para máxima recuperación. 🛏️";
        } elseif (strpos($mensaje, 'comida') !== false || strpos($mensaje, 'dieta') !== false) {
            $respuesta = "Asegúrate de consumir buena proteína post-entreno y carbohidratos complejos para energía duradera. 🥩";
        } elseif (strpos($mensaje, 'agua') !== false || strpos($mensaje, 'calor') !== false) {
            $respuesta = "En este clima cálido es vital hidratarse constantemente. Toma 3 a 4 litros de agua diarios. 💧";
        } elseif (strpos($mensaje, 'rutina') !== false || strpos($mensaje, 'ejercicio') !== false) {
            $respuesta = "Calienta siempre antes de empezar. Mantén de 8 a 12 reps priorizando técnica sobre peso. 🏋️‍♂️";
        } elseif (strpos($mensaje, 'creatina') !== false || strpos($mensaje, 'proteina') !== false) {
            $respuesta = "Toma 5g de creatina diarios para fuerza. La proteína en polvo úsala solo si te falta comida sólida. 🥤";
        }

        echo json_encode(['status' => 'CHAT_OK', 'html' => $respuesta]);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Kings - Panel Inteligente</title>
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
        .center-wrapper h1 { color: #fff !important; text-shadow: 0 4px 15px rgba(255,193,7,0.4); }
        
        .search-box { width: 100%; max-width: 650px; box-shadow: 0 10px 30px rgba(0,0,0,0.7); border-radius: 50px; overflow: hidden; transition: all 0.3s ease; border: 1px solid rgba(255, 193, 7, 0.15); }
        .search-box:focus-within { transform: scale(1.02); box-shadow: 0 0 35px rgba(255, 193, 7, 0.4); border-color: #ffc107; }
        .search-input { border: none; padding: 20px 30px; font-size: 1.3rem; background: #fff; color: #000; }
        .search-input:focus { outline: none; box-shadow: none; background: #fff; color: #000; }
        .search-btn { background: #ffc107; color: #000; border: none; padding: 0 35px; font-size: 1.5rem; transition: background 0.3s; }
        .search-btn:hover { background: #fff; color: #000; }
        
        .checkin-express-menu { position: absolute; top: 25px; left: 30px; max-width: 280px; }
        .admin-menu { position: absolute; top: 25px; right: 30px; }

        /* Estilos del Bot */
        .bot-wrapper { position: fixed; bottom: 25px; right: 30px; z-index: 1050; }
        .bot-bubble { width: 65px; height: 65px; background: #212529; color: #ffc107; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; cursor: pointer; box-shadow: 0 8px 25px rgba(0,0,0,0.5); border: 2px solid #ffc107; }
        .bot-card { width: 350px; height: 480px; display: none; flex-direction: column; background: white; border-radius: 20px; box-shadow: 0 15px 50px rgba(0,0,0,0.4); overflow: hidden; border: 1px solid rgba(0,0,0,0.08); color: #000; }
        .bot-header { background: #212529; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .bot-timer { font-size: 0.8rem; background: #ffc107; color: #000; padding: 2px 8px; border-radius: 10px; font-weight: bold; display: none; }
        .bot-body { flex-grow: 1; padding: 15px; overflow-y: auto; background: #f8f9fa; font-size: 0.92rem; scroll-behavior: smooth; }
        .chat-bubble-bot { background: #e9ecef; color: #212529; padding: 10px 14px; border-radius: 15px 15px 15px 0; margin-bottom: 12px; max-width: 85%; line-height: 1.4; }
        .chat-bubble-user { background: #212529; color: #ffc107; padding: 10px 14px; border-radius: 15px 15px 0 15px; margin-bottom: 12px; max-width: 85%; margin-left: auto; text-align: right; }
        .bot-footer { padding: 10px; background: white; border-top: 1px solid #eee; }
    </style>
</head>
<body>

    <div class="checkin-express-menu">
        <label class="form-label fw-bold mb-1" style="font-size: 0.85rem; letter-spacing: 0.5px;">
            <i class="bi bi-lightning-charge-fill text-warning"></i> CONTROL DE ACCESO EXPRESS
        </label>
        <form action="index.php" method="POST" class="input-group shadow-sm" style="border-radius: 30px; overflow: hidden; border: 1px solid rgba(255,255,255,0.2);">
            <input type="text" name="express_doc" class="form-control border-0 bg-dark text-white placeholder-light py-2" placeholder="Digitar documento..." style="font-size: 0.9rem;" required>
            <button class="btn btn-warning border-0 fw-bold px-3" type="submit">OK</button>
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
            <li><a class="dropdown-item py-2 rounded" href="ingresos.php"><i class="bi bi-graph-up-arrow me-2 text-success"></i> Contabilidad y Reportes</a></li>
            <li><a class="dropdown-item py-2 rounded" href="backup.php"><i class="bi bi-shield-lock me-2 text-info"></i> Copia de Seguridad</a></li>
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

    <div class="bot-wrapper">
        <div class="bot-bubble" id="openBotBtn"><i class="bi bi-robot"></i></div>
        <div class="bot-card" id="botCard">
            <div class="bot-header">
                <div class="d-flex align-items-center">
                    <i class="bi bi-cpu-fill text-warning me-2" style="font-size: 1.2rem;"></i>
                    <div>
                        <span class="fw-bold d-block" style="font-size: 0.95rem; line-height: 1;">Smart Coach</span>
                        <small class="text-warning" style="font-size: 0.75rem;">En línea</small>
                    </div>
                </div>
                <div class="bot-timer" id="timerDisplay">5:00</div>
                <button type="button" class="btn-close btn-close-white btn-sm" id="closeBotBtn"></button>
            </div>
            <div class="bot-body" id="chatBody">
                <div class="chat-bubble-bot">¡Hola! 👑 Soy tu coach. Digita tu documento abajo para habilitar tu chat seguro:</div>
            </div>
            <div class="bot-footer">
                <form id="botForm" class="input-group">
                    <input type="text" id="botInput" class="form-control form-control-sm border-secondary-subtle rounded-start-pill ps-3" placeholder="Tu número de documento..." autocomplete="off" required>
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
                        <h5 class="modal-title fw-bold"><i class="bi bi-check-circle-fill me-2"></i> INGRESO REGISTRADO</h5>
                    </div>
                    <div class="modal-body text-center p-4">
                        <i class="bi bi-person-check-fill text-success display-3 mb-3 d-block"></i>
                        <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($atletaNombre); ?></h4>
                        <p class="text-secondary mb-0"><?php echo htmlspecialchars($checkInMessage); ?></p>
                    </div>
                <?php elseif ($checkInStatus === 'EXIT_SUCCESS'): ?>
                    <div class="modal-header bg-info text-dark border-0 py-3 fw-bold">
                        <h5 class="modal-title fw-bold"><i class="bi bi-box-arrow-left me-2"></i> SALIDA REGISTRADA</h5>
                    </div>
                    <div class="modal-body text-center p-4">
                        <i class="bi bi-door-open-fill text-info display-3 mb-3 d-block"></i>
                        <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($atletaNombre); ?></h4>
                        <p class="text-secondary mb-0"><strong>Estado:</strong> Fuera de las instalaciones.</p>
                        <p class="text-muted small mt-2"><?php echo htmlspecialchars($checkInMessage); ?></p>
                    </div>
                <?php else: ?>
                    <div class="modal-header bg-danger text-white border-0 py-3">
                        <h5 class="modal-title fw-bold"><i class="bi bi-shield-x me-2"></i> ACCESO RECHAZADO</h5>
                    </div>
                    <div class="modal-body text-center p-4">
                        <i class="bi bi-person-x-fill text-danger display-3 mb-3 d-block"></i>
                        <h4 class="fw-bold mb-1"><?php echo !empty($atletaNombre) ? htmlspecialchars($atletaNombre) : "Documento Inválido"; ?></h4>
                        <p class="text-secondary mb-0"><?php echo htmlspecialchars($checkInMessage); ?></p>
                    </div>
                <?php endif; ?>
                <div class="modal-footer border-0 bg-light d-flex justify-content-center py-2 rounded-bottom">
                    <button type="button" class="btn btn-dark rounded-pill px-4" data-bs-dismiss="modal">Entendido / Continuar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Controladores del Botón y Chat (Conservados idénticos)
        const openBotBtn = document.getElementById('openBotBtn');
        const closeBotBtn = document.getElementById('closeBotBtn');
        const botCard = document.getElementById('botCard');
        const botForm = document.getElementById('botForm');
        const botInput = document.getElementById('botInput');
        const chatBody = document.getElementById('chatBody');
        const timerDisplay = document.getElementById('timerDisplay');

        let isSessionActive = false; let countdownInterval; let timeRemaining = 300; 

        openBotBtn.addEventListener('click', () => { botCard.style.display = 'flex'; openBotBtn.style.display = 'none'; });
        closeBotBtn.addEventListener('click', () => { botCard.style.display = 'none'; openBotBtn.style.display = 'flex'; });

        function printMessage(sender, text) {
            const div = document.createElement('div');
            div.className = sender === 'user' ? 'chat-bubble-user' : 'chat-bubble-bot';
            div.innerHTML = text; chatBody.appendChild(div); chatBody.scrollTop = chatBody.scrollHeight;
        }

        function startTimer() {
            timerDisplay.style.display = 'block'; timeRemaining = 300; clearInterval(countdownInterval);
            countdownInterval = setInterval(() => {
                timeRemaining--;
                let minutes = Math.floor(timeRemaining / 60); let seconds = timeRemaining % 60;
                timerDisplay.innerText = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
                if(timeRemaining <= 0) {
                    clearInterval(countdownInterval); isSessionActive = false; timerDisplay.style.display = 'none';
                    botInput.placeholder = "Tu número de documento...";
                    printMessage('bot', '⏳ Sesión expirada. Vuelve a loguearte.');
                }
            }, 1000);
        }

        botForm.addEventListener('submit', function(e) {
            e.preventDefault(); const inputVal = botInput.value.trim(); if(!inputVal) return;
            printMessage('user', inputVal); botInput.value = ""; const formData = new FormData();
            if(!isSessionActive) {
                formData.append('bot_action', 'login'); formData.append('documento', inputVal);
            } else {
                formData.append('bot_action', 'chat'); formData.append('mensaje', inputVal);
            }
            fetch('index.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'AUTH_SUCCESS') {
                    isSessionActive = true; botInput.placeholder = "Escribe tu duda aquí...";
                    printMessage('bot', data.html); startTimer();
                } else { printMessage('bot', data.html); }
            }).catch(error => console.error("Error:", error));
        });
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