# 🎓 PORTAL ESCOLAR - SISTEMA ERP MULTI-ESCUELA

Este es un sistema modular de planificación de recursos empresariales (**ERP Escolar**) desarrollado en PHP y SQLite. Permite la gestión completa de alumnos, docentes, materias, calificaciones, asistencias, reportes, comunicados del rector (Blog) y la personalización total de marca para múltiples instituciones (SaaS).

---

## 🚀 Características Principales

- **🎨 Personalización Total de Marca**: Cambia el nombre de la escuela, sube el logotipo de la institución en formato de imagen y personaliza la paleta de colores (Primario y Secundario) desde el panel de ajustes.
- **🏡 Landing Page Estilo WordPress**: Una elegante página de bienvenida con diseño tipo collage bento grid de imágenes (estilo Prepa Anáhuac) y carrusel de información interactivo.
- **📚 Expediente del Alumno Expandido**: Almacenamiento de fotos de perfil de estudiantes, promedios (GPA) y reportes de escalabilidad académica.
- **👨‍🏫 Gestión de Docentes y Materias**: Registro de 12 materias iniciales y asignación de docentes especialistas.
- **📰 Blog / Noticias de la Rectoría**: Publica comunicados del rector con soporte para textos largos e imágenes.
- **🔐 Seguridad y Roles**: Autenticación segura mediante contraseñas cifradas y soporte para múltiples roles: Administrador, Director, Coordinador, Docente, Estudiante y Padre.

---

## 🎁 OPCIONES DE HOSTING 100% GRATUITAS (Para PHP + SQLite)

Si buscas subir la aplicación a internet de forma **completamente gratuita (sin tarjeta de crédito)**, estas son las mejores opciones disponibles:

### 1. InfinityFree.com (100% Gratis para Siempre)
InfinityFree es uno de los mejores proveedores de hosting web gratuito para PHP y SQLite.
- **Paso 1**: Crea una cuenta gratuita en [InfinityFree.com](https://www.infinityfree.com/). No requiere tarjeta de crédito.
- **Paso 2**: Crea una nueva cuenta de hosting y selecciona un subdominio gratuito (por ejemplo, `mi-escuela.infinityfreeapp.com`).
- **Paso 3**: Ve al panel de control (`VistaPanel`) y abre el **Administrador de Archivos (Monsta FTP)**.
- **Paso 4**: Sube todos los archivos del proyecto a la carpeta `htdocs/`.
- **Paso 5**: ¡Listo! Tu sitio estará en línea al instante. La base de datos SQLite persistirá automáticamente en el disco sin borrarse.

---

### 2. Serv00.com (Sin Tarjeta y Permanente)
Serv00 es una plataforma polaca que ofrece hosting gratuito de por vida para PHP, SSH, y SQLite.
- **Paso 1**: Regístrate de forma gratuita en [Serv00.com](https://www.serv00.com/).
- **Paso 2**: En tu panel de administración, crea un nuevo sitio y actívale el soporte para PHP.
- **Paso 3**: Sube tus archivos mediante **SSH** o el Administrador de Archivos a la carpeta asignada.
- **Paso 4**: El archivo `.sqlite` se almacena directamente en el servidor sin límites de tiempo de expiración.

---

### 3. Render.com (Plan Gratuito de Render)
Render ofrece un plan gratuito para aplicaciones web PHP.
- **Paso 1**: Crea una cuenta en [Render.com](https://render.com/). No requiere tarjeta de crédito.
- **Paso 2**: Conecta tu repositorio de GitHub con el código del proyecto.
- **Paso 3**: Despliega la aplicación usando el plan **Free Web Service**.
- *Nota*: En el plan gratuito de Render, la base de datos se restablece cada vez que se reinicia el servidor. Se recomienda para probar la app.

---

## ☁️ Guía de Despliegue en la Nube (Opciones Avanzadas)

Para subir el proyecto a la nube de manera profesional:

### 1. Railway.app
Railway permite desplegar aplicaciones PHP conectadas a un repositorio de GitHub de forma automática.
- **Paso 1**: Sube tu código a un repositorio privado o público en GitHub.
- **Paso 2**: Crea una cuenta en [Railway.app](https://railway.app/).
- **Paso 3**: Selecciona **New Project** → **Deploy from GitHub repo** y vincula el repositorio de la app.
- **Paso 4**: **IMPORTANTE (Persistencia)**: Para que tu base de datos SQLite y las imágenes subidas no se borren en cada despliegue, añade un **Volume** (Disco persistente) en la configuración del servicio en Railway y configúralo para que apunte a la carpeta del proyecto.

---

### 2. Hosting Clásico con cPanel (Hostinger, Bluehost, Namecheap)
Si prefieres un hosting web tradicional:
- **Paso 1**: Comprime todos los archivos del proyecto en un archivo `.zip`.
- **Paso 2**: Súbelo mediante el **Administrador de Archivos de cPanel** o por **FTP** a la carpeta `public_html`.
- **Paso 3**: SQLite funciona de forma nativa en estos servidores sin necesidad de instalar controladores adicionales. No requiere configuración de persistencia ya que el almacenamiento en disco es permanente.

---

## 🌐 Servicios de Base de Datos en la Nube

Si en el futuro decides desacoplar la base de datos de los archivos locales (migrando de SQLite a una base de datos administrada en la nube):

### 1. Turso (SQLite en la Nube / Edge DB)
[Turso.tech](https://turso.tech/) es un servicio en la nube específico para bases de datos SQLite.
- Permite replicar tus datos globalmente a una velocidad extremadamente rápida.
- Puedes conectar esta app de PHP cambiando tu archivo `config/database.php` para usar el driver de Turso.

### 2. Aiven.io (Managed MySQL / PostgreSQL)
[Aiven.io](https://aiven.io/) ofrece planes gratuitos y de pago para bases de datos relacionales administradas.
- Te permite crear bases de datos MySQL en AWS o Google Cloud con un solo clic.

---

## 📁 Estructura del Proyecto

```text
├── auth/                  # Inicio de sesión, registro y cierre de sesión.
├── config/                # Conexión a la base de datos SQLite y Auth Middleware.
├── database/              # Archivos de esquema de base de datos SQL.
├── modules/               # Módulos del ERP (Alumnos, Materias, Notas, Ajustes, etc.).
├── uploads/               # Almacenamiento de logotipos e imágenes subidas.
├── views/                 # Plantillas de diseño de la interfaz (Header, Sidebar, Footer).
├── index.php              # Dashboard de usuario autenticado / Landing Page de invitados.
└── README.md              # Documentación oficial del sistema.
```
