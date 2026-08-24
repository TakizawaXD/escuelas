# Changelog

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/)
y este proyecto se adhiere a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-08-23

### Añadido
- Soporte para inicialización de Composer y gestión de dependencias PHP.
- Soporte para variables de entorno mediante un archivo `.env` (`vlucas/phpdotenv`).
- Configuración de pruebas automatizadas con PHPUnit.
- Tests unitarios y de integración iniciales para conexión a base de datos y utilidades de seguridad (`tests/`).
- Documentación técnica del proyecto (`ARCHITECTURE.md`, `CONTRIBUTING.md` y `database/SCHEMA.md`).
- Pipeline de integración continua (CI) en GitHub Actions (`.github/workflows/tests.yml`).
- Configuración de Docker Compose para levantamiento del entorno de desarrollo (`docker-compose.yml`).
- Registro de versiones (`VERSION.txt`).
