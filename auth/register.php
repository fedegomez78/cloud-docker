<?php
include '../app/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $usuario=trim($_POST['usuario']??''); $password=trim($_POST['password']??'');
  if ($usuario===''||$password===''){ $error="Completa usuario y contraseña."; }
  else {
    try { $hash=password_hash($password,PASSWORD_BCRYPT); $stmt=$conn->prepare("INSERT INTO usuarios (usuario, password_hash) VALUES (?, ?)"); $stmt->execute([$usuario,$hash]); header('Location: login.php'); exit; }
    catch(PDOException $e){ $error="No se pudo registrar (¿usuario ya existe?)."; }
  }
}
?><!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Registro</title><link rel="stylesheet" href="style.css"></head><body>
<div class="card"><h2>🧾 Registro de usuario</h2><?php if(!empty($error)) echo "<p class='error'>$error</p>"; ?>
<form method="POST"><label>Usuario</label><input type="text" name="usuario" required><label>Contraseña</label><input type="password" name="password" required><button type="submit">Registrar</button></form>
<p><a href="login.php">Volver al login</a></p></div></body></html>
