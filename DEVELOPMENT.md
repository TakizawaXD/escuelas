# 💻 Guía de Desarrollo para Colaboradores

Esta guía está diseñada para desarrolladores que desean extender el código del ERP Escolar.

---

## 1. Estándares de Codificación

### Estilo de Código PHP
- Seguimos estrictamente el estándar **PSR-12**.
- Formatea tu código antes de cada commit usando:
  ```bash
  vendor/bin/php-cs-fixer fix .
  ```
  O usando npm:
  ```bash
  npm run format
  ```

### Arquitectura Modular
Toda lógica nueva orientada a objetos en PHP debe ir dentro del directorio `src_php/` bajo el namespace `App\`:
- **Controladores:** En `src_php/Controllers/` heredando de `BaseController`.
- **Modelos:** En `src_php/Models/` heredando de `BaseModel`.
- **Servicios:** En `src_php/Services/` para lógica de negocio aislada.
- **Validadores:** En `src_php/Validators/` utilizando la clase `Validator` centralizada.

---

## 2. Pruebas Unitarias y de Integración

Las pruebas se dividen en pruebas unitarias de algoritmos (bajo `tests/Unit/`) y pruebas de integración/BD (bajo `tests/Feature/`).

Para ejecutar toda la suite de pruebas:
```bash
vendor/bin/phpunit
```

---

## 3. Control de Versiones de Base de Datos (Migraciones)

Si modificas el esquema de base de datos SQLite:
1. No edites directamente bases de datos existentes.
2. Añade un nuevo archivo de migración SQL en `database/migrations/` con el prefijo cronológico (ej. `0002_add_logs.sql`).
3. Ejecuta el script de migraciones:
   ```bash
   bash scripts/migrate.sh
   ```
