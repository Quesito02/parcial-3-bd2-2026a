<?php
// app/conexion.php
$host = 'localhost';
$db   = 'gym'; // <-- ¡Asegúrate de poner el nombre real de tu BD aquí!
$user = 'root';
$pass = ''; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     // AQUÍ: El nombre de la variable DEBE ser $pdo
     $pdo = new PDO($dsn, $user, $pass, $options); 
} catch (\PDOException $e) {
     echo "Error de conexión: " . $e->getMessage();
     exit();
}
?>