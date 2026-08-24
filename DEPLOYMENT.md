# 🚀 Guía de Despliegue en Producción

Este documento detalla el procedimiento para realizar el deploy del ERP Escolar a entornos de staging y producción.

---

## 1. Requisitos para Producción

- Servidor Web: **Apache** (con mod_rewrite activo) o **Nginx**.
- Versión PHP: **PHP 8.1** o superior con extensiones `pdo_sqlite`, `openssl`, `mbstring`.
- Permisos de escritura: El servidor web debe tener permisos de escritura sobre la base de datos en `database/` y los logs en `logs/`.

---

## 2. Despliegue Manual (Apache)

1. Sube los archivos del proyecto al directorio web del servidor (ej. `/var/www/html`).
2. Genera las dependencias optimizadas de producción:
   ```bash
   php composer.phar install --no-dev --optimize-autoloader
   ```
3. Copia y configura las variables de entorno para producción:
   ```bash
   cp .env.production .env
   # Genera una SECRET_KEY segura de 32 caracteres y colócala en el .env
   ```
4. Asigna permisos adecuados a los directorios de datos y registros:
   ```bash
   chown -R www-data:www-data database/ logs/ uploads/ cache/
   chmod -R 775 database/ logs/ uploads/ cache/
   ```

---

## 3. Despliegue con Docker Compose

El sistema está containerizado y listo para correr con Docker Compose en entornos productivos:
```bash
docker-compose up -d --build
```
Esto levantará el servidor Apache con PHP 8.3 e instalará automáticamente el driver de SQLite.
*Nota:* En producción, asegúrate de mapear la carpeta `database/` a un volumen persistente para evitar pérdidas de datos al reiniciar contenedores.
