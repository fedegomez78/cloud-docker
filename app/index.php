<?php
require_once 'jwt.php';
include 'db.php';
$secret = getenv('JWT_SECRET') ?: 'MiSecretoSuperSeguro_ChangeMe_123';
$token = $_COOKIE['auth_token'] ?? null;
if (!$token) { header('Location: http://localhost:8082/login.php'); exit; }
list($ok, $data) = jwt_decode($token, $secret);
if (!$ok) { header('Location: http://localhost:8082/login.php'); exit; }
$usuario_actual = $data['sub'] ?? 'usuario';
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre=trim($_POST['nombre']??''); $correo=trim($_POST['correo']??''); $telefono=trim($_POST['telefono']??''); $categoria=trim($_POST['categoria']??''); $mensajeF=trim($_POST['mensaje']??'');
  if ($nombre===''||$correo===''||$mensajeF==='') {
    $message = "<div class='alert warning'>⚠️ Completa nombre, correo y mensaje.</div>";
  } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $message = "<div class='alert warning'>⚠️ El correo no es válido.</div>";
  } else {
    try { $stmt=$conn->prepare("INSERT INTO mensajes (nombre, correo, telefono, categoria, mensaje) VALUES (?, ?, ?, ?, ?)"); $stmt->execute([$nombre,$correo,$telefono,$categoria,$mensajeF]); $message="<div class='alert success'>✅ Mensaje guardado.</div>"; }
    catch(PDOException $e){ $message="<div class='alert error'>❌ Error al guardar.</div>"; error_log($e->getMessage()); }
  }
}
try { $stmt=$conn->query("SELECT id,nombre,correo,telefono,categoria,mensaje,fecha FROM mensajes ORDER BY fecha DESC"); $mensajes=$stmt->fetchAll(); }
catch(PDOException $e){ $mensajes=[]; $message="<div class='alert error'>❌ Error al consultar.</div>"; error_log($e->getMessage()); }
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/><title>App protegida — Arquitectura Cloud</title><link rel="stylesheet" href="style.css"/></head>
<body>
<header><h1>🌐 App protegida (Docker)</h1><div class="userbar">👤 <?= htmlspecialchars($usuario_actual) ?> | <a href="http://localhost:8082/logout.php">Cerrar sesión</a></div></header>
<main>
<section class="form-section"><h3>📝 Enviar mensaje</h3><?= $message ?>
<form method="POST">
<div class="grid">
<div><label>Nombre *</label><input type="text" name="nombre" placeholder="Tu nombre completo"/></div>
<div><label>Correo *</label><input type="email" name="correo" placeholder="ejemplo@correo.com"/></div>
<div><label>Teléfono</label><input type="tel" name="telefono" placeholder="+57 300 000 0000"/></div>
<div><label>Categoría</label><select name="categoria"><option value="">Selecciona...</option><option>Soporte</option><option>Ventas</option><option>Académico</option><option>Otro</option></select></div>
</div>
<label>Mensaje *</label><textarea name="mensaje" rows="4" placeholder="Escribe tu mensaje..."></textarea>
<button type="submit">Guardar mensaje</button>
</form></section>
<section class="table-section"><h3>📋 Mensajes registrados</h3>
<table><thead><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Categoría</th><th>Mensaje</th><th>Fecha</th></tr></thead><tbody>
<?php if (!empty($mensajes)): foreach ($mensajes as $f): ?>
<tr><td><?= htmlspecialchars($f['id']) ?></td><td><?= htmlspecialchars($f['nombre']) ?></td><td><?= htmlspecialchars($f['correo']) ?></td><td><?= htmlspecialchars($f['telefono']??'') ?></td><td><?= htmlspecialchars($f['categoria']??'') ?></td><td><?= htmlspecialchars($f['mensaje']) ?></td><td><?= htmlspecialchars($f['fecha']) ?></td></tr>
<?php endforeach; else: ?><tr><td colspan="7" style="text-align:center;">Sin registros aún.</td></tr><?php endif; ?>
</tbody></table></section></main><footer><p>© 2025 Taller Docker — Arquitectura Cloud</p></footer></body></html>
