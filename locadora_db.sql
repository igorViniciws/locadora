-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS locadora_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- Seleciona o banco de dados
USE locadora_db;

-- Criação da tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    perfil VARCHAR(20) NOT NULL
) ENGINE=InnoDB;

-- Criação da tabela de veículos
CREATE TABLE IF NOT EXISTS veiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(10) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    placa VARCHAR(10) NOT NULL UNIQUE,
    disponivel BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB;

-- Insere usuários padrão
-- Nota: As senhas estão em formato hash, geradas com password_hash()
-- admin123 e user123 são as senhas em texto puro
INSERT INTO usuarios (username, password, perfil) VALUES 
    ('admin', '$2y$10$4gAzJ/Kq4NFc.K3nXi.l0OQsRHxqZJ8/Z2MtMrjorJX66IvPZOOym', 'admin'),
    ('usuario', '$2y$10$reDVMnCMBItvD.Ru13M/Heqn0K5C3t8cL7.jxvAfLk1xEFXbqB9HG', 'usuario');

-- Insere veículos padrão para demonstração
INSERT INTO veiculos (tipo, modelo, placa, disponivel) VALUES 
    ('Carro', 'Sandero', 'FMA-6680', FALSE),
    ('Moto', 'Ninja', 'FMA-6600', TRUE),
    ('Carro', 'Onix', 'ABC-1234', TRUE),
    ('Moto', 'Honda CB 500', 'DEF-5678', TRUE);