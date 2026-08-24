# 🛡️ Política de Seguridad

Nos tomamos muy en serio la seguridad de los datos de estudiantes, acudientes y personal docente. Este documento detalla nuestras políticas de seguridad y cómo reportar incidentes.

---

## 1. Mecanismos de Seguridad Implementados

1. **Protección CSRF**: Todos los formularios del sistema que realicen peticiones mutativas (`POST`, `PUT`, `DELETE`) deben incluir un token CSRF generado dinámicamente (`Auth::csrfToken()`) que es verificado antes de procesar cualquier acción.
2. **Sanitización de Entradas**: Las entradas recibidas por formularios pasan por un limpiador recursivo de entidades HTML (`BaseController::sanitizeInput` y `Auth::sanitize`) para mitigar vulnerabilidades XSS.
3. **Control de Intentos de Acceso (Rate Limiting)**: Bloqueo dinámico de inicios de sesión fallidos por dirección IP en `Auth::checkBruteForce` para evitar ataques de fuerza bruta.
4. **Capa de Transporte Seguro (TLS/SSL)**: Se requiere habilitar HTTPS en producción para proteger las cookies de sesión y transferencias de contraseñas.
5. **Políticas de CSP**: Configuración de cabeceras de Content-Security-Policy estrictas a través de `ContentSecurityPolicyMiddleware`.

---

## 2. Reportar una Vulnerabilidad

Si descubres un problema de seguridad en este ERP, **no abras un Issue público** en GitHub. En su lugar, envía un correo detallado a `seguridad@tudominio.com` incluyendo:
- Una descripción de la vulnerabilidad.
- Pasos detallados para reproducir el fallo (PoC).
- Posible impacto de explotación.

Responderemos a los reportes de seguridad en un plazo máximo de 48 horas y coordinaremos la publicación del parche corrector.
