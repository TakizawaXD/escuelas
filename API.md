# 🔌 Documentación de APIs y Endpoints

Este documento detalla las rutas y endpoints del sistema ERP Escolar (tanto del portal PHP como del backend Spring Boot).

---

## 1. Portal PHP (Rutas Principales de la Aplicación)

Estas rutas manejan la interfaz de usuario e interacciones del portal web.

| Ruta | Método | Rol Requerido | Descripción |
|------|--------|---------------|-------------|
| `/index.php` | `GET` | Cualquiera / Invitado | Panel de control dinámico según el rol o landing page. |
| `/auth/login.php` | `GET/POST` | Invitado | Inicio de sesión del usuario. Soporta limitación de intentos por IP. |
| `/auth/logout.php` | `POST` | Autenticado | Finaliza la sesión del usuario de forma segura. |
| `/modules/students/index.php` | `GET` | `ADMIN`, `DIRECTOR`, `COORDINADOR` | Listado y búsqueda de estudiantes. |
| `/modules/students/create.php` | `GET/POST` | `ADMIN`, `DIRECTOR`, `COORDINADOR` | Registro y matrícula de nuevos estudiantes con acudiente. |
| `/modules/grades/index.php` | `GET` | `ADMIN`, `DIRECTOR`, `COORDINADOR`, `DOCENTE` | Gestión de notas escolares por asignatura. |

---

## 2. Backend Spring Boot (API REST)

Ejecutándose en el puerto `8081` (entorno local). Todas las respuestas son devueltas en formato `application/json`.

### 2.1 Usuarios
#### Obtener listado de usuarios
- **Endpoint:** `GET /api/users`
- **Respuesta Exitosa (200 OK):**
  ```json
  [
    {
      "id": 1,
      "document": "12345678",
      "firstName": "Administrador",
      "lastName": "General",
      "email": "admin@escuela.com",
      "role": "ADMIN"
    }
  ]
  ```

### 2.2 Calificaciones
#### Registrar calificación
- **Endpoint:** `POST /api/grades`
- **Cuerpo de Petición (Request Body):**
  ```json
  {
    "studentId": 12,
    "subjectId": 4,
    "period": 1,
    "examGrade": 4.5,
    "workshopGrade": 4.0,
    "projectGrade": 4.8
  }
  ```
- **Respuesta Exitosa (201 Created):**
  ```json
  {
    "id": 89,
    "finalGrade": 4.47,
    "status": "Aprobado"
  }
  ```
