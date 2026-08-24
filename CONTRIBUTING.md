# 🤝 Guía de Contribución al ERP Escolar

¡Gracias por tu interés en colaborar con el proyecto ERP Escolar! Sigue esta guía para asegurar que tu código mantenga la calidad, seguridad y consistencia arquitectónica requeridas.

---

## 1. Requisitos para el Desarrollo Local

Asegúrate de contar con las siguientes herramientas en tu máquina de desarrollo:
- **PHP 7.4** o superior (recomendado PHP 8.1+)
- **Composer** (para dependencias PHP)
- **Java JDK 21** y **Maven** (para el backend Spring Boot)
- **SQLite3**

### Configuración del Entorno:
1. Clona el repositorio:
   ```bash
   git clone https://github.com/TakizawaXD/escuelas.git
   cd escuelas
   ```
2. Instala dependencias de Composer:
   ```bash
   php composer.phar install
   ```
3. Copia el archivo de configuración local:
   ```bash
   cp .env.example .env
   ```
4. Levanta el servidor local PHP:
   ```bash
   php -S localhost:8080
   ```
5. Si deseas ejecutar el backend Java, usa Maven:
   ```bash
   mvn spring-boot:run
   ```

---

## 2. Estilo de Código y Estándares

### PHP
- Seguimos la recomendación de estilo **PSR-12**.
- Puedes formatear tu código automáticamente ejecutando:
  ```bash
  vendor/bin/php-cs-fixer fix .
  ```

### Java
- Seguir las guías estándar de estilo de Java de Google.
- Utilizar Lombok para minimizar el código repetitivo (Boilerplate).

---

## 3. Pruebas Unitarias

Antes de enviar cualquier cambio, es obligatorio validar que las pruebas pasen satisfactoriamente.
- Ejecuta las pruebas de PHPUnit:
  ```bash
  vendor/bin/phpunit
  ```

---

## 4. Flujo de Git y Ramas

1. Crea una rama descriptiva para tu tarea:
   ```bash
   git checkout -b feature/nombre-de-la-mejora
   ```
2. Realiza commits claros e informativos, por ejemplo:
   - `feat: agregar reporte de calificaciones en pdf`
   - `fix: resolver vulnerabilidad csrf en formulario de admisiones`
3. Formatea tu código y ejecuta los tests locales.
4. Sube tu rama al repositorio remoto y crea un Pull Request (PR) hacia la rama principal.
