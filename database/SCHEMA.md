# 🗄️ Esquema de Base de Datos - ERP Escolar

Este documento describe la estructura de la base de datos relacional del ERP Escolar basada en **SQLite**.

---

## Relación de Tablas Principales

El sistema utiliza restricciones de clave foránea (`FOREIGN KEY`) para garantizar la integridad referencial. A continuación se detallan las tablas que estructuran la base de datos:

### 1. Gestión de Acceso y Roles
- **`roles`**: Contiene los roles del sistema (`ADMIN`, `DIRECTOR`, `COORDINADOR`, `DOCENTE`, `ESTUDIANTE`, `PADRE`).
- **`users`**: Almacena las cuentas de usuarios con sus credenciales cifradas, estado de activación, configuración de doble factor (2FA) y correos de verificación.
- **`permissions`** & **`role_permissions`**: Definen los privilegios granulares asignados a cada rol.
- **`login_attempts`**: Controla los intentos fallidos de inicio de sesión por dirección IP para evitar ataques de fuerza bruta.
- **`security_tokens`**: Tokens de recuperación de contraseña y verificación de cuentas con expiración.

### 2. Estructura Académica
- **`academic_years`**: Registra los periodos escolares anuales y su estado de actividad.
- **`courses`**: Cursos o grados (ej. "Primero de Primaria", "Segundo de Primaria").
- **`classrooms`**: Ubicación física y capacidad de las aulas de clase.
- **`subjects`**: Asignaturas académicas vinculadas a un curso y con un docente asignado.
- **`enrollments`**: Vincula un estudiante a un curso dentro de un año académico específico.
- **`schedules`**: Horarios semanales detallando el curso, materia, docente, aula, día de la semana y rango de horas.

### 3. Registro de Estudiantes y Docentes
- **`students`**: Expediente extendido de los alumnos, incluyendo promedio general (GPA), acudiente asignado, dirección, fecha de nacimiento, foto y observaciones.
- **`teachers`**: Especialidad de los docentes vinculados a un usuario.
- **`student_medical_records`**: Datos críticos de salud como tipo de sangre, alergias, condiciones médicas y contactos de emergencia.
- **`discipline_reports`**: Reportes de conducta generados por los profesores para cada estudiante.

### 4. Seguimiento Académico
- **`exams`**: Exámenes creados por docentes para sus respectivas materias.
- **`exam_results`**: Calificaciones individuales de cada estudiante en los exámenes.
- **`grades`**: Calificaciones acumuladas del periodo (examen, talleres, proyectos y nota final) de cada materia y estudiante.
- **`attendance`**: Registro diario de asistencias/inasistencias por materia, con justificación.
- **`certificates`**: Certificados institucionales y académicos emitidos a estudiantes.

### 5. Finanzas
- **`payment_plans`**: Planes o tarifas de pago configuradas en el sistema.
- **`payments`**: Registro individual de cobros, matrículas o pensiones, su monto, fecha límite, fecha de pago y estado de cobro (`Pendiente`, `Pagado`).

### 6. Comunicación y Servicios
- **`messages`**: Mensajería interna directa entre usuarios del sistema.
- **`notifications`**: Tablón de anuncios generales o notificaciones dirigidas a roles de usuario específicos.
- **`news_categories`** & **`news`**: Comunicados de prensa o noticias institucionales emitidas por rectoría.
- **`cafeteria_menus`**: Menús de alimentación organizados por tipo de comida y día.
- **`transport_routes`** & **`transport_assignments`**: Rutas de buses escolares y asignación de paraderos para estudiantes.
- **`library_books`** & **`library_loans`**: Catálogo de libros, copias disponibles, descargas PDF y registro de préstamos de biblioteca.
- **`admission_applications`**: Solicitudes de admisión de nuevos estudiantes presentadas por padres interesados.
- **`settings`**: Configuración institucional de marca (nombre del colegio, logotipo y paleta de colores corporativos).
