<div align="center">
  <img src="assets/MARCA.png" alt="COMECyT Logo" width="200"/>
  <h1>Portal de Control y Gestión de Solicitudes</h1>
  <p><strong>Consejo Mexiquense de Ciencia y Tecnología (COMECyT)</strong></p>
</div>

---

## 📖 Descripción del Proyecto

El **Portal de Control de Solicitudes** es una plataforma web desarrollada a la medida para el **COMECyT**. Su propósito es digitalizar, agilizar y transparentar la recepción y gestión interna de solicitudes por parte de la ciudadanía y otras áreas gubernamentales. 

La herramienta cuenta con un portal público para que la ciudadanía genere sus folios de seguimiento, y un robusto **Panel de Administración (Dashboard)** privado donde el equipo interno puede clasificar, priorizar y resolver dichas solicitudes, manteniendo un historial de trazabilidad completo.

---

## 🚀 Características Principales

### 🌐 Interfaz Pública (Ciudadanía)
- **Registro Simplificado:** Formulario claro para levantar solicitudes clasificadas por tipo (Atención, Soporte, Mantenimiento, Administración).
- **Generación de Folio Único:** Al registrarse, el ciudadano recibe un folio rastreable (ej. `CMCT-2026-0034`).
- **Consulta de Estatus en Tiempo Real:** Portal de seguimiento donde el ciudadano ingresa su folio y visualiza una línea de tiempo (Timeline) con los avances, dictámenes y el nombre del responsable de su gestión.

### 🔒 Panel de Administración (Uso Interno)
- **Dashboard Estadístico:** Gráficas en tiempo real de volumen de atención, estatus general y alertas de criticidad (Urgentes).
- **Gestión de Estados (PRG):** Flujo de trabajo centralizado que mueve la solicitud entre los estados: `Pendiente` ➔  `En Proceso` ➔ `Completada` (o `Cancelada`).
- **Trazabilidad Absoluta:** Cada cambio de estado genera un evento inmutable en el historial, registrando fecha, hora, comentarios y el **nombre del administrador responsable**.
- **Gestión de Accesos:** Modulo independiente para que los administradores principales generen, editen o regulen el acceso (Soft Delete) de los miembros del equipo.
- **Exportación de Datos:** Botón nativo para exportar todo el catálogo de solicitudes a formato **CSV** para su análisis en Excel.

---

## 🛠️ Stack Tecnológico

La arquitectura está construida bajo un modelo **Server-Side Rendering (SSR)**, garantizando despliegues inmediatos en infraestructuras institucionales (servidores compartidos, cPanel, VPS) sin necesidad de Node.js o contenedores pesados.

- **Backend:** PHP (v7.4+) puro con sentencias PDO preparadas.
- **Base de Datos:** MySQL / MariaDB estructurado con Foreign Keys y relaciones en cascada.
- **Frontend:** HTML5 Semántico + Vanilla CSS (Custom Properties) + JS minimalista (sin frameworks reactivos).
- **Seguridad:** `.htaccess` estricto, mitigación XSS mediante helpers personalizados de sanitización, autenticación Session-Based y contraseñas Bcrypt.
- **UI:** FontAwesome 6 (Iconografía) y Chart.js (Estadísticas).

---

## 📂 Estructura del Código

```text
Control_Solicitudes/
├── admin/               # Lógica y vistas del Dashboard protegido (Dashboard, Detalles, Exportación, Login)
├── assets/              # Sistema de Diseño CSS (Light Theme Guinda/Dorado), JS puro y Logotipos
├── config/              # Centralización de configuración (BDD persistente y Sesiones seguras)
├── database/            # Scripts DDL para la estructura SQL (Tablas principales e historiales)
├── docs/                # Manual Técnico de mantenimiento interno (manual.md)
├── includes/            # Componentes reutilizables de UI (Navbars, Modales y Helpers PHP)
└── public/              # Controladores de la cara pública ciudadana (index.php, tracking)
```

---

## ⚙️ Requisitos y Despliegue Rápido

### Prerrequisitos
- Servidor Web (Apache/Nginx)
- PHP >= 7.4
- MySQL / MariaDB

### Instalación en Entorno Local/Servidor
1. **Clona el repositorio** en tu carpeta pública (ej. `htdocs` o `www`):
   ```bash
   git clone https://github.com/FernandoT8rres/Control_Solicitudes.git
   ```
2. **Crea la base de datos:** Importa el esquema ubicado en `database/schema.sql`.
3. **Configura credenciales:** Edita el archivo `config/database.php` e ingresa tu usario y contraseña de BD local:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'tu_usuario');
   define('DB_PASS', 'tu_contrasena');
   define('DB_NAME', 'comecyt_solicitudes');
   ```
4. **Acceso Inicial:** Visita `http://localhost/Control_Solicitudes/admin/login.php` e ingresa con la siguiente cuenta maestra (incluida en el script SQL):
   - **Correo:** `admin.comecyt@edomex.gob.mx`
   - **Clave:** `Admin2026!`

---

<div align="center">
  <p><i>Sistema desarrollado para cumplir con altos estandares de eficiencia gubernamental y modernización tecnológica.</i></p>
</div>