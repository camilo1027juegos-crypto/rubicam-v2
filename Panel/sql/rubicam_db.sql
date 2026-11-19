-- ============================================================
--  Rubicam · Base de datos del panel de trabajo
--  Importar en phpMyAdmin (XAMPP).  Crea la BD rubicam_db,
--  las tablas y un usuario administrador de arranque.
--
--  Usuario de arranque:  admin   /   admin123
--  >>> CAMBIA esa contraseña desde Administración → Usuarios
--      en cuanto entres por primera vez.
-- ============================================================

CREATE DATABASE IF NOT EXISTS rubicam_db
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rubicam_db;

-- Para poder reimportar sin errores, se borran si ya existen
-- (respetando el orden de las llaves foráneas).
DROP TABLE IF EXISTS recordatorios_log;
DROP TABLE IF EXISTS documentos_do;
DROP TABLE IF EXISTS procesos_do;
DROP TABLE IF EXISTS permisos;
DROP TABLE IF EXISTS secciones;
DROP TABLE IF EXISTS usuarios;

-- ------------------------------------------------------------
-- 1) USUARIOS  (quién puede entrar)
-- ------------------------------------------------------------
CREATE TABLE usuarios (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario          VARCHAR(50)  NOT NULL,
  password_hash    VARCHAR(255) NOT NULL,
  rol              ENUM('admin','operador','consulta') NOT NULL DEFAULT 'consulta',
  nombre_completo  VARCHAR(150) NOT NULL,
  sucursal         VARCHAR(100) DEFAULT NULL,
  activo           TINYINT(1)   NOT NULL DEFAULT 1,
  creado_en        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuario (usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2) SECCIONES  (los módulos del dashboard sobre los que se dan permisos)
-- ------------------------------------------------------------
CREATE TABLE secciones (
  clave   VARCHAR(40)  NOT NULL,
  nombre  VARCHAR(100) NOT NULL,
  grupo   VARCHAR(40)  NOT NULL DEFAULT 'General',
  orden   INT          NOT NULL DEFAULT 0,
  PRIMARY KEY (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3) PERMISOS  (qué puede ver / editar cada usuario en cada sección)
--    El rol 'admin' no necesita filas aquí: ve y edita todo.
-- ------------------------------------------------------------
CREATE TABLE permisos (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id    INT UNSIGNED NOT NULL,
  seccion       VARCHAR(40)  NOT NULL,
  puede_ver     TINYINT(1)   NOT NULL DEFAULT 0,
  puede_editar  TINYINT(1)   NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuario_seccion (usuario_id, seccion),
  CONSTRAINT fk_permisos_usuario FOREIGN KEY (usuario_id)
    REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_permisos_seccion FOREIGN KEY (seccion)
    REFERENCES secciones(clave) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4) PROCESOS_DO  (las operaciones / declaraciones de operación)
-- ------------------------------------------------------------
CREATE TABLE procesos_do (
  id                       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  do_codigo                VARCHAR(30)  NOT NULL,
  cliente                  VARCHAR(150) NOT NULL,
  nit                      VARCHAR(20)  DEFAULT NULL,
  email                    VARCHAR(150) DEFAULT NULL,
  telefono                 VARCHAR(30)  DEFAULT NULL,
  responsable_nombre       VARCHAR(150) DEFAULT NULL,
  responsable_email        VARCHAR(150) DEFAULT NULL,
  responsable_whatsapp     VARCHAR(20)  DEFAULT NULL,
  regimen                  ENUM('Importación','Exportación','Tránsito') NOT NULL,
  sucursal                 VARCHAR(100) DEFAULT NULL,
  estado                   VARCHAR(60)  DEFAULT 'Abierta',
  fecha_apertura           DATE         DEFAULT NULL,
  fecha_vencimiento        DATE         DEFAULT NULL,
  recordatorio_fecha       DATE         DEFAULT NULL,
  recordatorio_nota        VARCHAR(255) DEFAULT NULL,
  recordatorio_canales     VARCHAR(20)  DEFAULT NULL,
  recordatorio_enviado_en  DATETIME     DEFAULT NULL,
  archivo_adjunto          VARCHAR(255) DEFAULT NULL,
  creado_en                TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_do_codigo (do_codigo),
  KEY idx_estado (estado),
  KEY idx_regimen (regimen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5) DOCUMENTOS_DO  (archivos subidos de cada operación)
-- ------------------------------------------------------------
CREATE TABLE documentos_do (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  do_id           INT UNSIGNED NOT NULL,
  nombre_archivo  VARCHAR(255) NOT NULL,
  ruta_archivo    VARCHAR(255) NOT NULL,
  tipo_documento  ENUM('Declaración de Importación','Factura Comercial','Lista de Empaque','RUT','BL/Guía aérea','Otro') DEFAULT 'Otro',
  fecha_subida    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_doc_do (do_id),
  CONSTRAINT fk_documentos_do FOREIGN KEY (do_id)
    REFERENCES procesos_do(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 6) RECORDATORIOS_LOG  (registro de recordatorios enviados)
-- ------------------------------------------------------------
CREATE TABLE recordatorios_log (
  id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  do_id               INT UNSIGNED NOT NULL,
  canal               ENUM('email','whatsapp') NOT NULL,
  recordatorio_fecha  DATE         DEFAULT NULL,
  estado              ENUM('ok','error') NOT NULL,
  destino             VARCHAR(150) DEFAULT NULL,
  detalle             VARCHAR(255) DEFAULT NULL,
  creado_en           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_log_do (do_id),
  CONSTRAINT fk_log_do FOREIGN KEY (do_id)
    REFERENCES procesos_do(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  DATOS INICIALES
-- ============================================================

-- Secciones del dashboard (sobre estas se asignan los permisos)
INSERT INTO secciones (clave, nombre, grupo, orden) VALUES
  ('inicio',       'Inicio',               'Principal',    1),
  ('chatbot',      'Chatbot',              'Principal',    2),
  ('tareas',       'Mis tareas',           'Principal',    3),
  ('agenda',       'Agenda',               'Principal',    4),
  ('equipos',      'Equipos',              'Trabajo',      5),
  ('cotizaciones', 'Cotizaciones',         'Trabajo',      6),
  ('pendientes',   'Pendientes',           'Trabajo',      7),
  ('reuniones',    'Reuniones',            'Trabajo',      8),
  ('plantillas',   'Plantillas',           'Conocimiento', 9),
  ('guias',        'Paso a paso',          'Conocimiento', 10),
  ('informacion',  'Información',           'Conocimiento', 11),
  ('operaciones',  'Operaciones DO',       'Gestión',      12),
  ('clientes',     'Clientes',             'Gestión',      13),
  ('documentos',   'Documentos',           'Gestión',      14),
  ('solicitudes',  'Solicitudes internas', 'Gestión',      15),
  ('reportes',     'Reportes',             'Gestión',      16);

-- Usuario administrador de arranque.  Contraseña: admin123  (CÁMBIALA)
INSERT INTO usuarios (usuario, password_hash, rol, nombre_completo, sucursal, activo) VALUES
  ('admin', '$2y$10$BC9WjlCoVxDbbt/JsSdVHetm8yY3AteqGOtOhYhGbUwxIpaPoA3Xm', 'admin', 'Administrador', 'Cúcuta', 1);
