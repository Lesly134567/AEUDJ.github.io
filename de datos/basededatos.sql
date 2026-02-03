-- ============================================
-- BASE DE DATOS AEUDJ - Sistema de Transporte
-- ============================================

CREATE DATABASE IF NOT EXISTS aeudj 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

USE aeudj;

-- ============================================
-- TABLA: usuarios
-- Almacena la información de los estudiantes
-- ============================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricula VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(255) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    universidad VARCHAR(100) NOT NULL,
    bloqueado TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLA: votos
-- Almacena las reservas de horarios de transporte
-- ============================================
CREATE TABLE IF NOT EXISTS votos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    horario VARCHAR(100) NOT NULL,
    fecha DATE NOT NULL,
    se_monto INT DEFAULT NULL COMMENT 'NULL=pending, 0=no subió, 1=subió, 2=llegó tarde',
    en_espera TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLA: cambios
-- Registra cambios de horarios realizados por usuarios
-- ============================================
CREATE TABLE IF NOT EXISTS cambios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo ENUM('antes', 'despues', 'otros') NOT NULL,
    nuevo_horario VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLA: notificaciones
-- Mensajes para el panel de administración
-- ============================================
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mensaje TEXT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- ÍNDICES para mejorar rendimiento
-- ============================================
CREATE INDEX idx_votos_fecha ON votos(fecha);
CREATE INDEX idx_votos_usuario ON votos(usuario_id);
CREATE INDEX idx_votos_horario ON votos(horario);
CREATE INDEX idx_cambios_usuario ON cambios(usuario_id);
CREATE INDEX idx_cambios_fecha ON cambios(created_at);