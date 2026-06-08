<?php
// app/index.php
require_once 'conexion.php';
require_once 'api_key.php'; // <-- Conexión a tu archivo secreto protegido

// --- LÓGICA PARTE 1: CHECK-IN EXPRESS ---
$mostrarModal = false;
$checkInStatus = "";
$checkInMessage = "";
$atletaNombre = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['express_doc'])) {
    $doc = trim($_POST['express_doc']);
    $mostrarModal = true;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM afiliados WHERE documento = ?");
        $stmt->execute([$doc]);
        $afiliado = $stmt->fetch();
        
        if ($afiliado) {
            $atletaNombre = $afiliado['nombre'] . " " . $afiliado['apellido'];
            $stmtMem = $pdo->prepare("SELECT * FROM membresias WHERE id_afiliado = ? AND estado = 'Activa' AND fecha_fin >= CURDATE() LIMIT 1");
            $stmtMem->execute([$afiliado['id_afiliado']]);
            $membresia = $stmtMem->fetch();
            
            if ($membresia) {
                $checkInStatus = "SUCCESS";
                $checkInMessage = "Ingreso autorizado. Su membresía vence el: " . $membresia['fecha_fin'];
                $stmtAsis = $pdo->prepare("INSERT INTO asistencias (id_afiliado, fecha, hora, observaciones) VALUES (?, CURDATE(), CURTIME(), 'Check-in Express')");
                $stmtAsis->execute([$afiliado['id_afiliado']]);
            } else {
                $checkInStatus = "DENIED";
                $checkInMessage = "Acceso bloqueado en puerta. No cuenta con membresías activas vigentes.";
            }
        } else {
            $checkInStatus = "NOT_FOUND";
            $checkInMessage = "El documento ingresado no se encuentra registrado.";
        }
    } catch (PDOException $e) {
        $checkInStatus = "ERROR";
        $checkInMessage = "Error: " . $e->getMessage();
    }
}

// --- LÓGICA PARTE 2: SMARTBOT CON API REAL DE GOOGLE GEMINI ---
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
            $html = "¡Acceso concedido, $nombre! 🔓 IA Conectada. Tienes 5 minutos de chat privado habilitado. ¿En qué te ayudo hoy?";
            echo json_encode(['status' => 'AUTH_SUCCESS', 'nombre' => $nombre, 'html' => $html]);
        } else {
            echo json_encode(['status' => 'ERROR', 'html' => 'Documento no encontrado.']);
        }
        exit();
    }
    
    if ($accion === 'chat') {
        $mensaje = trim($_POST['mensaje']);
        $docUsuario = trim($_POST['documento_usuario']);

        // 1. Buscamos el perfil del atleta para darle contexto a la IA
        $stmtAtleta = $pdo->prepare("SELECT nombre, somatotipo, objetivo FROM afiliados WHERE documento = ?");
        $stmtAtleta->execute([$docUsuario]);
        $atleta = $stmtAtleta->fetch();
        
        $nombreAtleta = $atleta ? $atleta['nombre'] : 'Atleta';
        $tipoCuerpo = $atleta ? $atleta['somatotipo'] : 'Mesomorfo';
        $meta = $atleta ? $atleta['objetivo'] : 'Mantenimiento';

        // 2. CONFIGURA AQUÍ TU API KEY (Llamando a la variable secreta)
        $apiKey = $clave_secreta_gemini; 
        
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey;

        // 3. El "Prompt" Maestro (Personalidad de la IA)
        $promptContexto = "Eres el 'Smart Coach', un entrenador personal de inteligencia artificial del gimnasio 'Gym Kings'. Sé muy breve, motivador y directo (máximo 3 a 4 líneas por respuesta). Tu cliente actual se llama $nombreAtleta, su tipo de cuerpo es $tipoCuerpo y su objetivo actual en el gimnasio es $meta. Responde a su siguiente duda basándote estrictamente en su perfil físico y sus metas, usa emojis. La duda es: " . $mensaje;

        $data = [
            "contents" => [
                ["parts" => [["text" => $promptContexto]]]
            ]
        ];
        $json_data = json_encode($data);

        // 4. Petición cURL al servidor de Google
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        
        // 5. Devolver la respuesta al chat
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $textoBot = $result['candidates'][0]['content']['parts'][0]['text'];
            // Convertimos asteriscos de Markdown a negritas HTML
            $textoFormat = nl2br(preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $textoBot));
            echo json_encode(['status' => 'CHAT_OK', 'html' => $textoFormat]);
        } else {
            echo json_encode(['status' => 'ERROR', 'html' => 'Hubo una desconexión temporal con los servidores satelitales del Smart Coach.']);
        }
        exit();
    }
}
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
            background-color: #111;
            background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/img/gym-background.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            height: 100vh; 
            display: flex; 
            flex-direction: column; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #fff; 
        }
        .center-wrapper { flex-grow: 1; display: flex; align-items: center; justify-content: center; flex-direction: column; }
        .center-wrapper h1 { color: #fff !important; text-shadow: 0 4px 10px rgba(0,0,0,0.5); }
        .center-wrapper h5 { color: #ddd !important; text-shadow: 0 2px 5px rgba(0,0,0,0.5); }
        .search-box { width: 100%; max-width: 650px; box-shadow: 0 15px 40px rgba(0,0,0,0.4); border-radius: 50px; overflow: hidden; transition: transform 0.2s; border: 1px solid rgba(255,255,255,0.1); }
        .search-box:focus-within { transform: scale(1.03); box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6); }
        .search-input { border: none; padding: 20px 30px; font-size: 1.3rem; background: #fff; color: #000; }
        .search-input:focus { outline: none; box-shadow: none; background: #fff; color: #000; }
        .search-btn { background: #ffc107; color: #000; border: none; padding: 0 35px; font-size: 1.5rem; transition: background 0.3s; }
        .search-btn:hover { background: #fff; color: #000; }
        .checkin-express-menu { position: absolute; top: 25px; left: 30px; max-width: 280px; }
        .checkin-express-menu label { color: #fff !important; text-shadow: 0 2px 4px rgba(0,0,0,0.5); }
        .admin-menu { position: absolute; top: 25px; right: 30px; }

        /* Estilos del Bot */
        .bot-wrapper { position: fixed; bottom: 25px; right: 30px; z-index: 1050; }
        .bot-bubble { width: 65px; height: 65px; background: #212529; color: #ffc107; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; cursor: pointer; box-shadow: 0 8px 25px rgba(0,0,0,0.5); transition: all 0.3s; border: 2px solid #ffc107; }
        .bot-bubble:hover { transform: scale(1.1) rotate(5deg); background: #ffc107; color: #000; }
        .bot-card { width: 350px; height: 480px; display: none; flex-direction: column; background: white; border-radius: 20px; box-shadow: 0 15px 50px rgba(0,0,0,0.4); overflow: hidden; border: 1px solid rgba(0,0,0,0.08); color: #000; }
        .bot-header { background: #212529; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .bot-timer { font-size: 0.8rem; background: #ffc107; color: #000; padding: 2px 8px; border-radius: 10px; font-weight: bold; display: none; }
        .bot-body { flex-grow: 1; padding: 15px; overflow-y: auto; background: #f8f9fa; font-size: 0.92rem; scroll-behavior: smooth; }
        .chat-bubble-bot { background: #e9ecef; color: #212529; padding: 10px 14px; border-radius: 15px 15px 15px 0; margin-bottom: 12px; max-width: 85%; line-height: 1.4; box-shadow: 0 2px 5px rgba(0,0,0,0.03); }
        .chat-bubble-user { background: #212529; color: #ffc107; padding: 10px 14px; border-radius: 15px 15px 0 15px; margin-bottom: 12px; max-width: 85%; margin-left: auto; text-align: right; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .bot-footer { padding: 10px; background: white; border-top: 1px solid #eee; }
    </style>
</head>
<body>

    <div class="checkin-express-menu">
        <label class="form-label fw-bold mb-1" style="font-size: 0.85rem; letter-spacing: 0.5px;">
            <i class="bi bi-lightning-charge-fill text-warning"></i> ASISTENCIA EXPRESS
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
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item py-2 rounded" href="bonos/listar.php"><i class="bi bi-gift-fill me-2 text-danger"></i> King Rewards</a></li>
            <li><a class="dropdown-item py-2 text-danger fw-bold rounded" href="vencimientos/listar.php"><i class="bi bi-exclamation-triangle me-2"></i> Vencimientos</a></li>
        </ul>
    </div>

    <div class="center-wrapper text-center px-4">
        <i class="bi bi-award-fill text-warning mb-2" style="font-size: 5.5rem; filter: drop-shadow(0 0 15px rgba(255,193,7,0.6));"></i>
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
                        <small class="text-warning" style="font-size: 0.75rem;">Online - Gemini AI</small>
                    </div>
                </div>
                <div class="bot-timer" id="timerDisplay">5:00</div>
                <button type="button" class="btn-close btn-close-white btn-sm" id="closeBotBtn"></button>
            </div>
            
            <div class="bot-body" id="chatBody">
                <div class="chat-bubble-bot">
                    ¡Hola! 👑 Soy tu coach impulsado por IA. Digita tu documento abajo para habilitar tu chat seguro:
                </div>
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
                        <h5 class="modal-title fw-bold"><i class="bi bi-check-circle-fill me-2"></i> ACCESO AUTORIZADO</h5>
                    </div>
                    <div class="modal-body text-center p-4">
                        <i class="bi bi-person-check-fill text-success display-3 mb-3 d-block"></i>
                        <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($atletaNombre); ?></h4>
                        <p class="text-secondary mb-0"><?php echo htmlspecialchars($checkInMessage); ?></p>
                    </div>
                <?php else: ?>
                    <div class="modal-header bg-danger text-white border-0 py-3">
                        <h5 class="modal-title fw-bold"><i class="bi bi-shield-x me-2"></i> ACCESO DENEGADO</h5>
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
        const openBotBtn = document.getElementById('openBotBtn');
        const closeBotBtn = document.getElementById('closeBotBtn');
        const botCard = document.getElementById('botCard');
        const botForm = document.getElementById('botForm');
        const botInput = document.getElementById('botInput');
        const chatBody = document.getElementById('chatBody');
        const timerDisplay = document.getElementById('timerDisplay');

        let isSessionActive = false;
        let countdownInterval;
        let timeRemaining = 300; 
        let documentoActual = ''; // Guardamos la sesión en el cliente

        openBotBtn.addEventListener('click', () => { botCard.style.display = 'flex'; openBotBtn.style.display = 'none'; });
        closeBotBtn.addEventListener('click', () => { botCard.style.display = 'none'; openBotBtn.style.display = 'flex'; });

        function printMessage(sender, text) {
            const div = document.createElement('div');
            div.className = sender === 'user' ? 'chat-bubble-user' : 'chat-bubble-bot';
            div.innerHTML = text;
            chatBody.appendChild(div);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        function startTimer() {
            timerDisplay.style.display = 'block';
            timeRemaining = 300;
            clearInterval(countdownInterval);
            countdownInterval = setInterval(() => {
                timeRemaining--;
                let minutes = Math.floor(timeRemaining / 60);
                let seconds = timeRemaining % 60;
                timerDisplay.innerText = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
                if(timeRemaining <= 0) {
                    clearInterval(countdownInterval);
                    isSessionActive = false;
                    documentoActual = '';
                    timerDisplay.style.display = 'none';
                    botInput.placeholder = "Tu número de documento...";
                    printMessage('bot', '⏳ <strong>Tu sesión ha expirado.</strong> Ingresa tu documento nuevamente.');
                }
            }, 1000);
        }

        botForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const inputVal = botInput.value.trim();
            if(!inputVal) return;
            printMessage('user', inputVal);
            botInput.value = "";
            
            const formData = new FormData();
            if(!isSessionActive) {
                formData.append('bot_action', 'login');
                formData.append('documento', inputVal);
                documentoActual = inputVal; // Almacenamos para las preguntas
            } else {
                formData.append('bot_action', 'chat');
                formData.append('mensaje', inputVal);
                formData.append('documento_usuario', documentoActual); // Enviamos el doc con la pregunta
                
                // Mostrar indicador de "Escribiendo..." mientras la IA piensa
                const thinkingDiv = document.createElement('div');
                thinkingDiv.id = 'bot-typing';
                thinkingDiv.className = 'chat-bubble-bot text-muted';
                thinkingDiv.innerHTML = '<small><i>El coach está escribiendo...</i></small>';
                chatBody.appendChild(thinkingDiv);
                chatBody.scrollTop = chatBody.scrollHeight;
            }
            
            fetch('index.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                const typingIndicator = document.getElementById('bot-typing');
                if (typingIndicator) typingIndicator.remove(); // Quitamos el "Escribiendo..."

                if(data.status === 'AUTH_SUCCESS') {
                    isSessionActive = true;
                    botInput.placeholder = "Pregúntale a la IA...";
                    printMessage('bot', data.html);
                    startTimer();
                } else if(data.status === 'CHAT_OK' || data.status === 'ERROR') {
                    printMessage('bot', data.html);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                const typingIndicator = document.getElementById('bot-typing');
                if (typingIndicator) typingIndicator.remove();
                printMessage('bot', 'Hubo un error de red. Intenta nuevamente.');
            });
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