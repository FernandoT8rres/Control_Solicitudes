-- ============================================
-- COMECyT Control de Solicitudes
-- Schema de Base de Datos
-- Version: 1.0 | 2026-02-22
-- ============================================
-- Ejecutar como: mysql -u root -p < database/schema.sql
-- ============================================

CREATE DATABASE IF NOT EXISTS comecyt_solicitudes
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE comecyt_solicitudes;

-- ============================================
-- Tabla: administradores
-- Almacena las cuentas del panel de administracion
-- ============================================
CREATE TABLE IF NOT EXISTS administradores (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(150)    NOT NULL,
    email           VARCHAR(200)    NOT NULL UNIQUE,
    password_hash   VARCHAR(255)    NOT NULL,
    activo          TINYINT(1)      NOT NULL DEFAULT 1,
    ultimo_login    DATETIME        NULL,
    fecha_creacion  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_activo (activo)
) ENGINE=InnoDB;

-- ============================================
-- Tabla: solicitudes
-- Registro principal de solicitudes internas
-- ============================================
CREATE TABLE IF NOT EXISTS solicitudes (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    folio               VARCHAR(25)     NOT NULL UNIQUE,
    tipo                ENUM('mantenimiento','atencion','soporte','administracion') NOT NULL,
    solicitante         VARCHAR(150)    NOT NULL,
    email_solicitante   VARCHAR(200)    NOT NULL DEFAULT '',
    area                VARCHAR(150)    NOT NULL,
    prioridad           ENUM('baja','media','alta','urgente') NOT NULL DEFAULT 'media',
    descripcion         TEXT            NOT NULL,
    resuelto_por        VARCHAR(150)    NULL DEFAULT NULL,
    estatus             ENUM('pendiente','en_proceso','completada','cancelada') NOT NULL DEFAULT 'pendiente',
    fecha_creacion      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tipo       (tipo),
    INDEX idx_estatus    (estatus),
    INDEX idx_prioridad  (prioridad),
    INDEX idx_fecha      (fecha_creacion),
    INDEX idx_email_sol  (email_solicitante)
) ENGINE=InnoDB;

-- ============================================
-- Tabla: historial_solicitudes
-- Auditoria de cambios de estatus con comentarios
-- ============================================
CREATE TABLE IF NOT EXISTS historial_solicitudes (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    solicitud_id        INT             NOT NULL,
    estatus_anterior    ENUM('pendiente','en_proceso','completada','cancelada') NULL,
    estatus_nuevo       ENUM('pendiente','en_proceso','completada','cancelada') NOT NULL,
    comentario          TEXT            NULL,
    usuario_nombre      VARCHAR(150)    NULL DEFAULT NULL,
    fecha_cambio        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (solicitud_id) REFERENCES solicitudes(id) ON DELETE CASCADE,
    INDEX idx_solicitud  (solicitud_id),
    INDEX idx_fecha      (fecha_cambio)
) ENGINE=InnoDB;

-- ============================================
-- Datos iniciales: Administrador por defecto
-- Email: admin@comecyt.gob.mx
-- Password: Admin2026!
-- Hash generado con: password_hash('Admin2026!', PASSWORD_BCRYPT)
-- IMPORTANTE: Cambiar la contrasena en produccion
-- ============================================
INSERT INTO administradores (nombre, email, password_hash) VALUES
(
    'Administrador COMECyT',
    'admin@comecyt.gob.mx',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
);
-- NOTA: El hash anterior corresponde a la contrasena 'password' por compatibilidad.
-- Para generar un hash real de 'Admin2026!' ejecutar en PHP:
--   echo password_hash('Admin2026!', PASSWORD_BCRYPT);
-- y reemplazar el valor en administradores.