-- =============================================
-- Sistema de Agendamento - Condomínio
-- =============================================

CREATE DATABASE IF NOT EXISTS condominio_agendamento
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE condominio_agendamento;

-- Moradores
CREATE TABLE moradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    apartamento VARCHAR(20) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    ativo TINYINT(1) DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Controle de créditos semanais de lavanderia (resetado toda segunda-feira)
CREATE TABLE creditos_lavanderia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    morador_id INT NOT NULL,
    semana_inicio DATE NOT NULL,  -- sempre segunda-feira da semana
    horas_usadas DECIMAL(4,2) DEFAULT 0.00,
    FOREIGN KEY (morador_id) REFERENCES moradores(id) ON DELETE CASCADE,
    UNIQUE KEY uk_morador_semana (morador_id, semana_inicio)
);

-- Controle de créditos mensais de churrasqueira
CREATE TABLE creditos_churrasqueira (
    id INT AUTO_INCREMENT PRIMARY KEY,
    morador_id INT NOT NULL,
    mes_ano VARCHAR(7) NOT NULL,  -- formato: 2025-04
    usos_realizados INT DEFAULT 0,
    FOREIGN KEY (morador_id) REFERENCES moradores(id) ON DELETE CASCADE,
    UNIQUE KEY uk_morador_mes (morador_id, mes_ano)
);

-- Agendamentos de lavanderia
CREATE TABLE agendamentos_lavanderia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    morador_id INT NOT NULL,
    data_agendamento DATE NOT NULL,
    horario_inicio TIME NOT NULL,
    horario_fim TIME NOT NULL,
    duracao_minutos INT NOT NULL,  -- múltiplos de 30
    status ENUM('confirmado', 'cancelado', 'concluido') DEFAULT 'confirmado',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (morador_id) REFERENCES moradores(id) ON DELETE CASCADE
);

-- Agendamentos de churrasqueira
CREATE TABLE agendamentos_churrasqueira (
    id INT AUTO_INCREMENT PRIMARY KEY,
    morador_id INT NOT NULL,
    data_agendamento DATE NOT NULL,
    turno ENUM('manha', 'tarde', 'noite') NOT NULL,
    status ENUM('confirmado', 'cancelado', 'concluido') DEFAULT 'confirmado',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (morador_id) REFERENCES moradores(id) ON DELETE CASCADE,
    UNIQUE KEY uk_data_turno (data_agendamento, turno)
);

-- Admin
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Configurações do sistema
CREATE TABLE configuracoes (
    chave VARCHAR(50) PRIMARY KEY,
    valor VARCHAR(255) NOT NULL
);

INSERT INTO configuracoes (chave, valor) VALUES
    ('horas_lavanderia_semana', '4'),
    ('usos_churrasqueira_mes', '2'),
    ('horario_abertura', '07:00'),
    ('horario_fechamento', '22:00'),
    ('nome_condominio', 'Condomínio');

-- Admin padrão (senha: admin123)
-- Hash gerado e verificado com BCrypt $2b$12$
INSERT INTO admins (usuario, senha_hash)
VALUES ('admin', '$2b$12$xEfOJ5r290MnxVt8C26JLuIUZEKqJsVWIZdi4p8ZVXdSNMBTrXKg6');

-- Moradores de exemplo (senha: admin123)
INSERT INTO moradores (nome, apartamento, senha_hash) VALUES
('João Silva',    '101', '$2b$12$RX5hV20RIEP/UbeKVG65QuoFDY6.Ud.SEMcPHEeAsRTH2pXfutBaO'),
('Maria Santos',  '102', '$2b$12$UOElH/AS2Vk1MH1LGfy1nuXvHqseTTRcquK3qDwZlBYFoWcPcP7sK'),
('Pedro Oliveira','201', '$2b$12$TzV9B5ELTUWnH29UViASr.z6AhwnHTCvDvxjNcBARVunZ033WOI0C');

-- Índices para performance
CREATE INDEX idx_agend_lav_data ON agendamentos_lavanderia(data_agendamento, status);
CREATE INDEX idx_agend_chur_data ON agendamentos_churrasqueira(data_agendamento, status);
CREATE INDEX idx_creditos_lav_semana ON creditos_lavanderia(morador_id, semana_inicio);
