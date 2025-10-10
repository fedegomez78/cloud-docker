# Taller Docker con Autenticación (JWT)
Servicios:
- `app` (PHP/Apache) — Aplicación protegida por JWT
- `auth` (PHP/Apache) — Login/registro que emite token JWT
- `db` (MySQL 8.0) — Base de datos compartida
- `phpmyadmin` — Administración de la BD

## Puertos
- App: http://localhost:8080
- Auth: http://localhost:8082
- phpMyAdmin: http://localhost:8081 (host: db, user: root, pass: .env)
- MySQL: localhost:3307 (externo)

## Ejecución
```
docker compose down -v
docker compose up -d --build
```

Luego:
1. Regístrate: http://localhost:8082/register.php
2. Inicia sesión: http://localhost:8082/login.php
3. Serás redirigido a la app: http://localhost:8080
4. Cerrar sesión: http://localhost:8082/logout.php

## Variables (.env)
- MYSQL_* (credenciales)
- JWT_SECRET (cámbialo en producción)
- JWT_EXP_HOURS (caducidad del token)

## Nota
Se usa JWT porque las sesiones de PHP no se comparten entre contenedores. El token va en cookie `auth_token` y se valida en `app/index.php`.
