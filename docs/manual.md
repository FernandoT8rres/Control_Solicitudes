# COMECyT — Control de Solicitudes Internas
## Manual Tecnico y de Mantenimiento v1.0

---

## 1. Arquitectura del Sistema

El sistema esta construido bajo un modelo **Server-Side Rendering (SSR)** nativo en **PHP (v7.4+)** y **MySQL/MariaDB**, prescindiendo de frameworks externos pesados o dependencias manejadas por Node.js/Composer para asegurar la maxima facilidad de despliegue y mantenimiento en servidores compartidos o institucionales.

- **Backend**: PHP limpio con PDO para interaccion segura con base de datos.
- **Frontend**: HTML5 Semantico renderizado en servidor.
- **Estilos**: Vanilla CSS con variables nativas en `assets/css/main.css` utilizando un tema claro (Light Theme) para maxima legibilidad.
- **Identidad**: Uso intensivo del logotipo institucional `MARCA.png` como punto focal.
- **Interactividad**: JavaScript minimalista (`assets/js/app.js`) unicamente para UI (modales, alertas, sidebar), sin logica de negocio ni fetch APIs. El flujo de datos usa el patron **PRG (Post-Redirect-Get)** tradicional a traves de formularios.

---

## 2. Estructura de Directorios (Como Moverse)

```
COMECyT_Solicitudes/
├── config/                  # Constantes base. Aqui modificas la conexion a BD (database.php).
│                            # Tambien se maneja la seguridad de sesion (auth.php).
├── includes/                # Fragmentos reutilizables: headers, footers y helpers.php.
│                            # [MODIFICAR helpers.php PARA AGREGAR NUEVAS ETIQUETAS/COLORES]
├── admin/                   # Vistas del negocio. Todas protegidas por verificarSesionAdmin().
│                            # Contiene CRUD (solicitudes.php, detalle.php, export_csv.php).
├── public/                  # Vistas publicas sin autenticacion.
│                            # (index.php para registro, consulta.php para tracking).
├── assets/                  # CSS, fuentes y JS del cliente.
├── database/                # Schema estructural de la base de datos MySQL original.
└── docs/                    # Documentacion del proyecto.
```

---

## 3. Mantenimiento y Modificaciones Comunes

### 3.1 Añadir un Nuevo Tipo de Solicitud o Estatus

Toda la "traduccion" de variables de base de datos a elementos visuales se concentra en **un solo archivo**: `includes/helpers.php`.

Para anadir un estatus nuevo (ej. `en_revision`), debes:
1. Actualizar el ENUM en la base de datos (tabla `solicitudes`).
2. Anadirlo al diccionario `ETIQUETAS_ESTATUS` en `helpers.php`.
3. Asignarle una clase en la funcion `getBadgeClase()` dentro de `helpers.php`.
4. Asignarle un icono FontAwesome en `getIconoEstatus()` dentro de `helpers.php`.
5. Si es un estatus, definir sus reglas en la variable `$transiciones` en `admin/detalle.php` y `admin/solicitudes.php` para especificar hacia que otros estados puede moverse un ticket.

### 3.2 Modificar los Colores e Identidad (Branding)

El sistema usa CSS Custom Properties en la raiz del documento. Ve al archivo `assets/css/main.css`, a la seccion `:root`. Los colores primarios actuales se originan explícitamente del archivo `assets/MARCA.png`.

```css
:root {
    --color-primary:       #662331; /* Tinto institucional (De logo) */
    --color-accent:        #B19A6D; /* Dorado institucional (De logo) */
    
    /* Fondos - Tema Claro (Light Theme) */
    --bg-base: #F3F4F6;
    --bg-card: #FFFFFF;
}
```

Para cambiar el escudo o logotipo principal, **no modifiques el HTML**. Simplemente reemplaza el archivo fisico `assets/MARCA.png` manteniendo ese mismo nombre y extension, ya que las vistas `public/index.php`, `public/consulta.php`, `admin/login.php` y el `header_admin.php` apuntan dinamica y proporcionalmente a dicho archivo.

### 3.3 Gestion de Administradores

El sistema cuenta con un modulo visual independiente (`admin/administradores.php`), accesible desde el menu lateral bajo la seccion "Gestion". Este modulo permite a los usuarios con sesion activa:

1. **Crear Administradores:** Generar nuevas cuentas especificando nombre, correo electronico y contrasena inicial (almacenada de forma segura mediante bcrypt).
2. **Editar Administradores:** Modificar datos generales e incluso forzar el reestablecimiento de contrasenas.
3. **Control de Acceso (Soft Delete):** Para prevenir la corrupcion del historial de solicitudes o la perdida de trazabilidad de acciones pasadas, no existe un borrado fisico de usuarios. En su lugar, el sistema permite *"Activar"* o *"Desactivar"* dinamicamente el acceso al sistema de cualquier administrador (es decir, alternando internamente el valor del campo `activo`), conservando intactas sus registros asociados.

### 3.4 Modificar Tiempos de Expiracion de Sesion

Para evitar sesiones "fantasma" dejadas en equipos publicos, el sistema cierra la sesion despues de inactividad.
- Archivo a modificar: `config/auth.php`
- Constante: `define('SESSION_TIMEOUT', 7200);` (valor en segundos).

---

## 4. Modelo de Datos y Rendimiento

El sistema esta construido alrededor de 3 tablas principales. El esquema esta optimizado y usa el motor InnoDB.

- **`solicitudes`**: Tabla principal con los datos del ciudadano, detalle del requerimiento, folios e indices de busqueda. Tambien incorpora la columna `resuelto_por` para estampar automaticamente al administrador responsable de haber finalizado el proceso de atencion.
- **`historial_solicitudes`**: Tabla de trazabilidad lineal pivoteada por `solicitud_id`. Cada transicion de estatus inserta un nuevo evento aqui. Incluye la columna `usuario_nombre` para reportar que administrador especifico efectuo cada movimiento en la linea de tiempo publica y privada. Nunca se le hace `UPDATE`, solo insercion o eliminacion en cascada.
- **`administradores`**: Gestiona los accesos, contrasenas, fechas de ultimo acceso y el estado `activo`/`inactivo` para la gestion rapida desde su propio modulo.

### 4.1 Generacion de Folios (Tolerancia a Concurrencia)
La generacion de folios en `generarFolio()` (`config/database.php`) busca usar el anio actual concatenado y extrae matematicamente el ultimo autoincremento para evitar brechas.

### 4.2 Limpieza de Datos (Borrado Permanente)
La accion de _"Eliminar solicitud"_ en el panel de administracion es destructiva. Usa sentencias PDO preparadas que eliminan en cascada la trazabilidad (historico) y luego la fila maestra para conservar la integridad referencial y no dejar tablas huérfanas.

---

## 5. Criterios de Seguridad Implementados

Al realizar actualizaciones de codigo futuro, el equipo de desarrollo debe adherirse a los siguientes patrones ya implementados en la base estructural:

1. **SQL Injection**: Prohibido concatenar variables directas en el `$pdo->query`. Absolutamente toda insercion de origen de usuario usa `$pdo->prepare()` y ejecucion indexada (`?`).
2. **Cross-Site Scripting (XSS)**: Cualquier renderizado de texto libre (nombres, areas, descripciones) en DOM DEBE estar forzosamente envuelto en la funcion helper `esc($string)` (que inyecta `htmlspecialchars`).
3. **Session Fixation**: El login (`config/auth.php`) aplica automaticamente `session_regenerate_id(true)` upon success.
4. **Exposure Rules**: En servidores Apache, las carpetas criticas deben mantenerse siempre con bloqueos de lectura directa (`Options -Indexes`). Los includes no pueden ser accedidos fuera del contexto require.
5. **Cross-Site Request Forgery (CSRF)**: La interaccion critica (como eliminar o cambiar estatus) esta protegida unicamente a postbacks verificados por una sesion de admin inicializada activa y validada (`_accion` hidden fields).
