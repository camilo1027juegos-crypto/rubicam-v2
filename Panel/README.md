# Rubicam · Panel — Acceso y permisos (Fase 1)

Login real + base de datos MySQL + herramienta de administrador para gestionar
usuarios y permisos por sección. Hecho en **PHP + MySQL** (XAMPP).

---

## 1. Instalación en XAMPP (una sola vez)

1. Copia la carpeta `rubicam-panel` dentro de `C:\xampp\htdocs\` (o donde tengas tu proyecto).
2. En el **XAMPP Control Panel**, enciende **Apache** y **MySQL**.
3. Abre phpMyAdmin: `http://localhost/phpmyadmin`
4. Pestaña **Importar** → elige el archivo `sql/rubicam_db.sql` → **Continuar**.
   Esto crea la base `rubicam_db`, todas las tablas y el usuario administrador.
5. Entra al panel: `http://localhost/.../rubicam-panel/`

> Si tu MySQL tiene contraseña (no la de XAMPP por defecto), ponla en `config/conexion.php`.

## 2. Primer ingreso

- **Usuario:** `admin`
- **Contraseña:** `admin123`

👉 **Cambia esa contraseña de inmediato** en *Administración → Usuarios → Editar*.

---

## 3. Qué incluye esta fase

- **Login** (`login.php`) con contraseñas cifradas (bcrypt), token CSRF y protección de sesión.
- **Base de datos ordenada** (`sql/rubicam_db.sql`):
  - `usuarios` · `procesos_do` · `documentos_do` · `recordatorios_log` (las de tu diagrama)
  - `secciones` y `permisos` (para el control de accesos)
- **Administración de usuarios** (`admin/usuarios.php`): crear, editar, activar/desactivar y eliminar.
- **Administración de permisos** (`admin/permisos.php`): por cada persona, marcar qué
  secciones puede **ver** y cuáles puede **editar**.
- **Roles:**
  - `admin` → ve y edita **todo** (no necesita permisos individuales; por ejemplo el CEO).
  - `operador` / `consulta` → solo lo que el admin les marque.
- El **dashboard** (`dashboard-nuevo.php`) ya **exige iniciar sesión** para abrirse.

## 4. Estructura

```
rubicam-panel/
├── index.php                 → redirige a login o al destino
├── login.php                 → inicio de sesión
├── logout.php                → cerrar sesión
├── dashboard-nuevo.php       → tu dashboard (ahora protegido con login)
├── config/
│   └── conexion.php          → conexión PDO a MySQL
├── includes/
│   ├── auth.php              → sesión, usuario actual, guardias
│   ├── permisos.php          → puede_ver() / puede_editar()
│   └── layout.php            → cabecera y menú del área admin
├── admin/
│   ├── usuarios.php          → gestión de usuarios
│   └── permisos.php          → gestión de permisos por sección
├── assets/
│   └── panel.css             → estilos del login y la administración
└── sql/
    └── rubicam_db.sql        → base de datos completa (importar en phpMyAdmin)
```

---

## 5. Fase 2 — El dashboard ya guarda en MySQL

Ahora el dashboard **guarda y lee de la base de datos** y **respeta los permisos**.

### Instalación de la Fase 2 (una sola vez)
1. En phpMyAdmin, con la base `rubicam_db` seleccionada, **Importar** el archivo
   `sql/dashboard_modulos.sql` (crea una tabla por módulo del dashboard).
2. Listo. Entra con tu usuario y abre el dashboard.
3. Para comprobar que todo quedó bien, abre `estado.php` (menú no visible; escribe
   `http://localhost/.../rubicam-panel/estado.php`): te dice si falta alguna tabla.

### Cómo funciona
- `ws-adapter.js` → le da al dashboard su "conexión" (define `window.claude`).
- `ws-api.php` → recibe los cambios y los guarda en MySQL. **Aplica los permisos**:
  si un usuario no puede *editar* una sección, el servidor rechaza el cambio; si no
  puede *ver* una sección, no le envía esos datos.
- El **menú del dashboard oculta** las secciones que el usuario no puede ver.
- `ws-chat.php` → el chatbot con IA queda como marcador (necesita un servicio externo
  para funcionar; no da error).

### Modelo de datos
Una **tabla por módulo** (`ws_tareas`, `ws_cotizaciones`, `ws_operaciones`…): las
columnas principales son propias y consultables, y un campo `extra` (JSON) guarda lo
secundario o en lista (asistentes, acuerdos, checklist) para no perder nada.

> Nota: las tablas de tu diagrama (`procesos_do`, `documentos_do`, `recordatorios_log`)
> están pensadas para el envío de recordatorios por correo/WhatsApp, que el dashboard
> todavía no tiene. Quedan creadas y listas para cuando construyamos esa función.

## 6. Prueba rápida
1. Entra como **admin**.
2. Crea una persona en *Usuarios* (rol operador) y dale permisos en *Permisos*
   (por ejemplo, ver Cotizaciones pero no editarlas).
3. Abre el **dashboard**, crea una cotización → recarga la página: sigue ahí (está en MySQL).
4. Cierra sesión, entra con el operador: verás solo las secciones permitidas.

---

_Seguridad aplicada: contraseñas con `password_hash` (bcrypt), consultas con
sentencias preparadas (PDO), tokens CSRF en los formularios, y protección para no
quedarte sin acceso (no puedes desactivarte ni quitarte el rol admin a ti mismo)._
