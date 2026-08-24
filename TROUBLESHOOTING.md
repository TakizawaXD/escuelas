# 🔍 Solución de Problemas Comunes (Troubleshooting)

Este documento contiene soluciones a los errores más comunes que ocurren durante la instalación, desarrollo o producción.

---

## 1. Errores de Base de Datos (SQLite)

### Error: `SQLSTATE[HY000]: unable to open database file`
- **Causa:** El servidor web no tiene permisos de escritura en la base de datos o en la carpeta que la contiene. SQLite necesita poder crear archivos temporales en el mismo directorio.
- **Solución:** Otorga permisos de lectura/escritura al usuario del servidor web sobre toda la carpeta `database/`:
  ```bash
  chmod -R 775 database/
  chown -R www-data:www-data database/  # En Debian/Ubuntu
  ```

### Error: `Database is locked`
- **Causa:** Hay múltiples escrituras concurrentes o una transacción abierta que no fue cerrada correctamente.
- **Solución:**
  1. Si estás depurando, detén procesos huérfanos que puedan tener bloqueado el archivo SQLite.
  2. Asegúrate de que todos los bloques de transacciones en PHP utilicen `try-catch` y ejecuten `rollBack()` en el bloque de excepciones.

---

## 2. Errores de Composer y Autoloading

### Error: `Fatal error: Class 'Database' not found`
- **Causa:** El autoloader de Composer no está cargado o no se ha inicializado correctamente.
- **Solución:** Asegúrate de ejecutar `php composer.phar install` y de que el archivo `vendor/autoload.php` exista. Si has agregado clases nuevas y no se encuentran, regenera el autoloader:
  ```bash
  php composer.phar dump-autoload -o
  ```

---

## 3. Conflictos de Puertos (Ecosistema Dual)

### Error: `Address already in use` (Puerto 8080 u 8081)
- **Causa:** El puerto seleccionado ya está ocupado por otra instancia del servidor o servicio.
- **Solución:**
  - Cambia el puerto del portal PHP:
    ```bash
    php -S localhost:9090
    ```
  - Para cambiar el puerto de Spring Boot, edita `src/main/resources/application.properties` y modifica `server.port=8082`.
