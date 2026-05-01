-- ============================================
-- Sistema de Agendamento para Terapia
-- Banco de dados: MySQL / MariaDB
-- ============================================

CREATE DATABASE IF NOT EXISTS terapia_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE terapia_db;

-- Tabela de configuração do admin (terapeuta)
CREATE TABLE IF NOT EXISTS admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    crp VARCHAR(20),
    bio TEXT,
    duracao_sessao INT DEFAULT 50, -- minutos
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de disponibilidade semanal
CREATE TABLE IF NOT EXISTS disponibilidade (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dia_semana TINYINT NOT NULL COMMENT '0=Dom, 1=Seg, ..., 6=Sáb',
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    INDEX idx_dia (dia_semana)
);

-- Tabela de bloqueios/folgas específicas
CREATE TABLE IF NOT EXISTS bloqueios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    data_bloqueio DATE NOT NULL,
    hora_inicio TIME,
    hora_fim TIME,
    motivo VARCHAR(200),
    dia_inteiro BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_data (data_bloqueio)
);

-- Tabela de pacientes
CREATE TABLE IF NOT EXISTS pacientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(20),
    data_nascimento DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
);

-- Tabela de agendamentos
CREATE TABLE IF NOT EXISTS agendamentos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    paciente_id INT NOT NULL,
    data_sessao DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    status ENUM('pendente','confirmado','cancelado','concluido') DEFAULT 'pendente',
    observacoes TEXT,
    token_cancelamento VARCHAR(64),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE,
    INDEX idx_data_sessao (data_sessao),
    INDEX idx_status (status)
);

-- Admin padrão (senha: Admin@123 — troque após o primeiro login!)
INSERT INTO admin (nome, email, senha, crp, duracao_sessao)
VALUES (
    'Dra. Ana Souza',
    'admin@terapia.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Admin@123
    'CRP 00/00000',
    50
) ON DUPLICATE KEY UPDATE id=id;

-- Disponibilidade padrão: Seg-Sex, 8h-18h
INSERT INTO disponibilidade (dia_semana, hora_inicio, hora_fim) VALUES
(1, '08:00:00', '18:00:00'),
(2, '08:00:00', '18:00:00'),
(3, '08:00:00', '18:00:00'),
(4, '08:00:00', '18:00:00'),
(5, '08:00:00', '18:00:00');
