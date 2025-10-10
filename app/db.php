<?php
date_default_timezone_set(getenv('TZ') ?: 'America/Bogota');
$host = getenv('MYSQL_HOST') ?: 'db';
$dbname = getenv('MYSQL_DATABASE') ?: 'appdb';
$username = getenv('MYSQL_USER') ?: 'appuser';
$password = getenv('MYSQL_PASSWORD') ?: 'app123';
try {
  $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
  ]);
} catch (PDOException $e) {
  http_response_code(500);
  echo "❌ Error de conexión a la base de datos.";
  error_log($e->getMessage());
  exit;
}
?>