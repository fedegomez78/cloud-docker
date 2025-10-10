<?php
require_once 'jwt.php';
include '../app/db.php';
$secret = getenv('JWT_SECRET') ?: 'MiSecretoSuperSeguro_ChangeMe_123';
$exp_hours = intval(getenv('JWT_EXP_HOURS') ?: '6');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $usuario=trim($_POST['usuario']??''); $password=trim($_POST['password']??'');
  $stmt=$conn->prepare("SELECT id, usuario, password_hash, rol FROM usuarios WHERE usuario = ?"); $stmt->execute([$usuario]); $user=$stmt->fetch();
  if ($user && password_verify($password,$user['password_hash'])) {
    $now=time(); $payload=['iss'=>'auth-service','sub'=>$user['usuario'],'rol'=>$user['rol'],'iat'=>$now,'exp'=>$now+($exp_hours*3600)];
    $token=jwt_encode($payload,$secret);
    setcookie('auth_token',$token,['expires'=>$payload['exp'],'path'=>'/','httponly'=>true,'samesite'=>'Lax']);
    header('Location: http://localhost:8080/'); exit;
  } else { $error="Usuario o contraseña incorrectos."; }
}
?><!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Login - Taller Cloud</title><link rel="stylesheet" href="style.css"></head><body>
<div class="card"><h2>🔐 Iniciar sesión</h2><?php if(!empty($error)) echo "<p class='error'>$error</p>"; ?>
<form method="POST"><label>Usuario</label><input type="text" name="usuario" required><label>Contraseña</label><input type="password" name="password" required><button type="submit">Entrar</button></form>
<p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p></div></body></html>
