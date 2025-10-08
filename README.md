# 🐳 Taller de Arquitectura Cloud - Docker + PHP + MySQL

Aplicación CRUD completa con Docker Compose, PHP 8.2 y MySQL 8.0, incluyendo sistema de carga de imágenes.

## 📋 Características

- ✅ **CRUD completo** (Crear, Leer, Actualizar, Eliminar)
- 📝 Formulario con validaciones JavaScript completas
- 📸 Sistema de carga y visualización de fotos
- 🔍 Búsqueda en tiempo real
- 📱 Diseño responsive y moderno
- 🎨 Animaciones y transiciones fluidas
- 🐳 Completamente containerizado con Docker

## 🛠️ Campos del Formulario

- Nombre completo (obligatorio)
- Correo electrónico (obligatorio)
- Teléfono (obligatorio, con formato automático)
- Ciudad (obligatorio)
- País (obligatorio)
- Foto de perfil (opcional, máx. 5MB)
- Mensaje (obligatorio, 10-500 caracteres)

## 📦 Estructura del Proyecto

```
proyecto/
├── app/
│   ├── Dockerfile
│   ├── index.php
│   ├── db.php
│   ├── style.css
│   ├── validaciones.js
│   └── uploads/          # Directorio para imágenes (se crea automáticamente)
├── initdb/
│   └── 01_schema.sql
├── .env
├── docker-compose.yml
└── README.md
```

## 🚀 Instalación y Despliegue

### Prerrequisitos

- Docker instalado
- Docker Compose instalado

### Pasos para Desplegar

1. **Clonar o crear el proyecto con la estructura anterior**

2. **Navegar al directorio del proyecto:**
```bash
cd proyecto
```

3. **Construir y levantar los contenedores:**
```bash
docker-compose up -d --build
```

4. **Verificar que los contenedores estén corriendo:**
```bash
docker-compose ps
```

Deberías ver 3 contenedores activos:
- `app` (aplicación PHP)
- `db` (MySQL)
- `phpmyadmin` (administrador de base de datos)

5. **Acceder a la aplicación:**

- **Aplicación principal:** http://localhost:8080
- **phpMyAdmin:** http://localhost:8081
  - Usuario: `root`
  - Contraseña: `root123`

## 🔧 Comandos Útiles

### Ver logs de los contenedores:
```bash
docker-compose logs -f
```

### Ver logs solo de la app:
```bash
docker-compose logs -f app
```

### Reiniciar los contenedores:
```bash
docker-compose restart
```

### Detener los contenedores:
```bash
docker-compose stop
```

### Detener y eliminar contenedores:
```bash
docker-compose down
```

### Detener y eliminar contenedores incluyendo volúmenes (⚠️ esto elimina la base de datos):
```bash
docker-compose down -v
```

### Reconstruir la aplicación después de cambios:
```bash
docker-compose up -d --build app
```

### Acceder al contenedor de la aplicación:
```bash
docker-compose exec app bash
```

### Acceder al contenedor de MySQL:
```bash
docker-compose exec db mysql -u root -p
```
Contraseña: `root123`

## 🗄️ Base de Datos

### Esquema de la Tabla `mensajes`

```sql
CREATE TABLE mensajes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  correo VARCHAR(120) NOT NULL,
  telefono VARCHAR(20),
  ciudad VARCHAR(100),
  pais VARCHAR(100),
  foto VARCHAR(255),
  mensaje TEXT,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Backup de la Base de Datos

```bash
docker-compose exec db mysqldump -u root -proot123 appdb > backup.sql
```

### Restaurar Base de Datos

```bash
docker-compose exec -T db mysql -u root -proot123 appdb < backup.sql
```

## 📝 Validaciones Implementadas

### Validaciones del lado del Cliente (JavaScript)

- **Nombre:** Solo letras, 2-100 caracteres
- **Correo:** Formato de email válido
- **Teléfono:** Formato numérico con espacios/guiones permitidos
- **Ciudad/País:** 2-100 caracteres
- **Mensaje:** 10-500 caracteres
- **Foto:** JPG, PNG, GIF, WEBP, máximo 5MB

### Validaciones del lado del Servidor (PHP)

- Sanitización de datos con `htmlspecialchars()`
- Validación de tipos de archivo
- Validación de tamaño de archivo
- Prepared statements para prevenir SQL injection

## 🎨 Características de Diseño

- **Paleta de colores moderna** con gradientes
- **Animaciones suaves** en transiciones
- **Modal para vista de imágenes** ampliadas
- **Sistema de notificaciones** toast
- **Iconos Font Awesome** 6.4.0
- **Diseño responsive** mobile-first
- **Efectos hover** interactivos
- **Badges informativos** en el header

## 🔒 Seguridad

- Prepared statements para consultas SQL
- Sanitización de entradas del usuario
- Validación de tipos de archivo
- Límites de tamaño de archivo
- Variables de entorno para credenciales
- Permisos adecuados en directorios

## 🐛 Solución de Problemas

### Error: "Puerto ya en uso"

Si el puerto 8080 o 3306 ya está en uso, puedes cambiarlos en `docker-compose.yml`:

```yaml
ports:
  - "8090:80"  # Cambiar 8080 por 8090
```

### Error: "Cannot create directory uploads"

Asegúrate de que el directorio tenga permisos correctos:

```bash
docker-compose exec app chmod -R 777 /var/www/html/uploads
```

### Las imágenes no se cargan

Verifica que el directorio `uploads` exista y tenga permisos:

```bash
docker-compose exec app ls -la /var/www/html/
docker-compose exec app mkdir -p /var/www/html/uploads
docker-compose exec app chmod -R 755 /var/www/html/uploads
```

### La base de datos no se conecta

Verifica que el contenedor de MySQL esté corriendo:

```bash
docker-compose ps
docker-compose logs db
```

Espera unos segundos después de `docker-compose up` para que MySQL se inicialice completamente.

## 📊 Monitoreo

### Ver uso de recursos:
```bash
docker stats
```

### Ver información de los contenedores:
```bash
docker-compose ps
docker inspect <container_name>
```

## 🔄 Actualización de la Aplicación

1. Realizar cambios en los archivos
2. Reconstruir el contenedor:
```bash
docker-compose up -d --build app
```

3. Si cambiaste la estructura de la base de datos, necesitas recrear el volumen:
```bash
docker-compose down -v
docker-compose up -d --build
```

## 👨‍🏫 Créditos

**Docente:** Juan Carlos López Henao  
**Curso:** Taller de Arquitectura Cloud  
**Tecnologías:** Docker, PHP 8.2, MySQL 8.0, Apache

## 📄 Licencia

Material educativo para el Taller de Arquitectura Cloud.

---

## 🎯 Objetivos de Aprendizaje

- ✅ Comprender la arquitectura de contenedores
- ✅ Trabajar con Docker Compose
- ✅ Conectar múltiples servicios (PHP + MySQL + phpMyAdmin)
- ✅ Implementar un CRUD completo
- ✅ Manejar persistencia de datos con volúmenes
- ✅ Trabajar con variables de entorno
- ✅ Subir y almacenar archivos en contenedores

---

**¡Feliz aprendizaje! 🚀**