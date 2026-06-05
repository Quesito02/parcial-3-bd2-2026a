<?php
// asistencias/registrar_express.php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['documento'])) {
    
    $documento = trim($_POST['documento']);
    
    try {
        // 1. Buscamos al afiliado por su cédula
        $stmt = $pdo->prepare("SELECT id_afiliado FROM afiliados WHERE documento = ? AND estado = 'Activo'");
        $stmt->execute([$documento]);
        $afiliado = $stmt->fetch();

        if ($afiliado) {
            // 2. Si existe, guardamos la entrada con la hora actual
            date_default_timezone_set('America/Bogota');
            $fecha = date('Y-m-d');
            $hora = date('H:i:s');
            
            $sqlInsert = "INSERT INTO asistencias (id_afiliado, fecha, hora, observaciones) VALUES (?, ?, ?, 'Check-in Express')";
            $stmtInsert = $pdo->prepare($sqlInsert);
            $stmtInsert->execute([$afiliado['id_afiliado'], $fecha, $hora]);

            // Devolvemos al inicio con mensaje de éxito
            header("Location: ../index.php?asistencia=ok");
            exit();
        } else {
            // Si no existe o está inactivo
            header("Location: ../index.php?error=no_existe");
            exit();
        }

    } catch (PDOException $e) {
        die("Error de BD: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit();
}