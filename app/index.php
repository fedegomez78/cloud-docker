<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang='es'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>Arquitectura Cloud - Taller Docker</title>
  <link rel='stylesheet' href='style.css'>
</head>
<body>
  <h1>Bienvenidos a la Clase de Contenedores con Docker</h1>
  <h2>Arquitectura Cloud - Nivel Avanzado</h2>
  <p>Demostración de una aplicación PHP + MySQL corriendo en contenedores.</p>

  <form method='POST'>
    <input type='text' name='numero_identificacion' placeholder='Numero De Identificacion' required>
    <input type='text' name='nombre' placeholder='Nombre' required>
    <input type='email' name='correo' placeholder='Correo' required>
    <textarea name='mensaje' placeholder='Mensaje'></textarea>
    <button type='submit'>Enviar</button>
  </form>

  <?php
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_identificacion = $_POST['numero_identificacion'];
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $mensaje = $_POST['mensaje'];

    $stmt = $pdo->prepare('INSERT INTO mensajes (numero_identificacion, nombre, correo, mensaje) VALUES (?, ?, ?, ?)');
    $stmt->execute([$numero_identificacion,$nombre, $correo, $mensaje]);

    echo "<p class='ok'>Mensaje guardado correctamente ✅</p>";
  }

  $result = $pdo->query('SELECT * FROM mensajes ORDER BY fecha DESC')->fetchAll();
  if ($result) {
    echo "<h3>Mensajes registrados:</h3><table><tr><th>Numero De Identificacion</th><th>Nombre</th><th>Correo</th><th>Mensaje</th><th>Fecha</th></tr>";
    foreach ($result as $row) {
      echo "<tr><td>{$row['numero_identificacion']}</td><td>{$row['nombre']}</td><td>{$row['correo']}</td><td>{$row['mensaje']}</td><td>{$row['fecha']}</td></tr>";
    }
    echo "</table>";
  }
  ?>
  <footer>© 2025 | Curso Arquitectura Cloud - Grupo 16 | 10.7.25-1</footer>
</body>
</html>
