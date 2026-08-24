# 📥 Guía de Instalación del ERP Escolar

Sigue estos pasos para instalar y ejecutar el ERP Escolar en tu máquina local.

---

## Requisitos Previos

Asegúrate de tener instalados los siguientes componentes:
- **PHP >= 7.4** (Recomendado 8.1 o superior)
- **Composer** (Manejador de dependencias de PHP)
- **Node.js** (npm)
- **SQLite3**
- **Java JDK 21** (si deseas ejecutar el backend REST Spring Boot)

---

## Pasos de Instalación

### 1. Clonar el repositorio
```bash
git clone https://github.com/TakizawaXD/escuelas.git
cd escuelas
```

### 2. Instalar dependencias de PHP y Node.js
```bash
# Instalar dependencias de PHP
php composer.phar install

# Instalar herramientas de formateo de Node.js
npm install
```

### 3. Configurar variables de entorno
Copia la plantilla de variables de entorno y ajusta las credenciales:
```bash
cp .env.example .env
```

### 4. Inicializar Base de Datos SQLite
La base de datos SQLite se crea automáticamente en `database/escuela_erp.sqlite` la primera vez que cargas el sitio. También puedes forzar su creación importando el esquema:
```bash
sqlite3 database/escuela_erp.sqlite < database/schema_sqlite.sql
```

### 5. Iniciar Servidores de Desarrollo

#### Portal PHP:
Ejecuta el servidor web interno de PHP:
```bash
php -S localhost:8080
```
Abre tu navegador en: [http://localhost:8080](http://localhost:8080).

#### Backend Java Spring Boot (Opcional):
```bash
./mvnw spring-boot:run
```
El backend estará disponible en: [http://localhost:8081](http://localhost:8081).
