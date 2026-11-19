-- ============================================================
--  Rubicam · Tablas de los módulos del dashboard (Fase 2)
--  Importar DESPUÉS de rubicam_db.sql, sobre la misma base rubicam_db.
--
--  Una tabla por módulo (normalizada). Las columnas principales son
--  propias y consultables; el campo JSON "extra" guarda los datos
--  secundarios o en lista (asistentes, acuerdos, checklist…) para no
--  perder nada. El id es el mismo que genera el dashboard (texto).
-- ============================================================
USE rubicam_db;

DROP TABLE IF EXISTS ws_tareas;
DROP TABLE IF EXISTS ws_reuniones;
DROP TABLE IF EXISTS ws_cotizaciones;
DROP TABLE IF EXISTS ws_pendientes;
DROP TABLE IF EXISTS ws_operaciones;
DROP TABLE IF EXISTS ws_clientes;
DROP TABLE IF EXISTS ws_documentos;
DROP TABLE IF EXISTS ws_solicitudes;
DROP TABLE IF EXISTS ws_equipo;
DROP TABLE IF EXISTS ws_avisos;
DROP TABLE IF EXISTS ws_actividad;
DROP TABLE IF EXISTS ws_progreso;

CREATE TABLE ws_tareas (
  id           VARCHAR(40) PRIMARY KEY,
  titulo       VARCHAR(255),
  area         VARCHAR(40),
  prioridad    VARCHAR(20),
  vence        VARCHAR(25),
  responsable  VARCHAR(40),
  ref          VARCHAR(255),
  hecha        TINYINT(1) DEFAULT 0,
  completada   VARCHAR(25),
  creada       VARCHAR(25),
  extra        JSON,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_tareas_resp (responsable), KEY idx_tareas_area (area)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ws_reuniones (
  id           VARCHAR(40) PRIMARY KEY,
  titulo       VARCHAR(255),
  inicio       VARCHAR(25),
  duracion     INT,
  area         VARCHAR(40),
  lugar        VARCHAR(255),
  externos     INT,
  resumen      TEXT,
  extra        JSON,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_reu_inicio (inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ws_cotizaciones (
  id           VARCHAR(40) PRIMARY KEY,
  numero       VARCHAR(30),
  estado       VARCHAR(30),
  cliente      VARCHAR(40),
  servicio     VARCHAR(40),
  valor        BIGINT,
  vence        VARCHAR(25),
  responsable  VARCHAR(40),
  creada       VARCHAR(25),
  extra        JSON,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_cot_estado (estado), KEY idx_cot_cliente (cliente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ws_pendientes (
  id           VARCHAR(40) PRIMARY KEY,
  titulo       VARCHAR(255),
  esperando    VARCHAR(40),
  desde        VARCHAR(25),
  ref          VARCHAR(255),
  responsable  VARCHAR(40),
  resuelto     TINYINT(1) DEFAULT 0,
  extra        JSON,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ws_operaciones (
  id           VARCHAR(40) PRIMARY KEY,
  numero       VARCHAR(30),
  ciudad       VARCHAR(10),
  tipo         VARCHAR(10),
  estado       VARCHAR(30),
  apertura     VARCHAR(25),
  servicio     VARCHAR(40),
  cliente      VARCHAR(40),
  responsable  VARCHAR(40),
  cotizacion   VARCHAR(40),
  extra        JSON,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_op_estado (estado), KEY idx_op_cliente (cliente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ws_clientes (
  id           VARCHAR(40) PRIMARY KEY,
  nombre       VARCHAR(150),
  nit          VARCHAR(30),
  ciudad       VARCHAR(80),
  contacto     VARCHAR(150),
  cargo        VARCHAR(100),
  email        VARCHAR(150),
  telefono     VARCHAR(40),
  extra        JSON,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_cli_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ws_documentos (
  id           VARCHAR(40) PRIMARY KEY,
  tipo         VARCHAR(80),
  estado       VARCHAR(30),
  nombre       VARCHAR(255),
  operacion    VARCHAR(40),
  fecha        VARCHAR(25),
  responsable  VARCHAR(40),
  extra        JSON,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_doc_op (operacion), KEY idx_doc_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ws_solicitudes (
  id           VARCHAR(40) PRIMARY KEY,
  tipo         VARCHAR(40),
  solicitante  VARCHAR(40),
  estado       VARCHAR(30),
  desde        VARCHAR(25),
  hasta        VARCHAR(25),
  creada       VARCHAR(25),
  extra        JSON,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_sol_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ws_equipo (
  id           VARCHAR(40) PRIMARY KEY,
  nombre       VARCHAR(150),
  iniciales    VARCHAR(5),
  area         VARCHAR(40),
  rol          VARCHAR(100),
  color        VARCHAR(20),
  cumple       VARCHAR(25),
  extra        JSON,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ws_avisos (
  id           VARCHAR(40) PRIMARY KEY,
  texto        TEXT,
  fijado       TINYINT(1) DEFAULT 0,
  fecha        VARCHAR(25),
  extra        JSON,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ws_actividad (
  id           VARCHAR(40) PRIMARY KEY,
  texto        VARCHAR(255),
  uid          VARCHAR(40),
  fecha        VARCHAR(25),
  extra        JSON,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_act_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ws_progreso (
  id           VARCHAR(40) PRIMARY KEY,
  extra        JSON,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
