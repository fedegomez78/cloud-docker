<?php
function base64url_encode($data) { return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); }
function base64url_decode($data) { return base64_decode(strtr($data, '-_', '+/')); }
function jwt_encode($payload, $secret) {
  $header = ['alg' => 'HS256', 'typ' => 'JWT'];
  $segments = [];
  $segments[] = base64url_encode(json_encode($header));
  $segments[] = base64url_encode(json_encode($payload));
  $signing_input = implode('.', $segments);
  $signature = hash_hmac('sha256', $signing_input, $secret, true);
  $segments[] = base64url_encode($signature);
  return implode('.', $segments);
}
function jwt_decode($jwt, $secret) {
  $parts = explode('.', $jwt);
  if (count($parts) !== 3) { return [false, 'Formato inválido']; }
  list($h64, $p64, $s64) = $parts;
  $payload = json_decode(base64url_decode($p64), true);
  $sig = base64url_decode($s64);
  $calc = hash_hmac('sha256', "$h64.$p64", $secret, true);
  if (!hash_equals($calc, $sig)) { return [false, 'Firma inválida']; }
  if (isset($payload['exp']) && time() >= $payload['exp']) { return [false, 'Token expirado']; }
  return [true, $payload];
}
?>