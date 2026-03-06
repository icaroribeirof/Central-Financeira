CREATE DATABASE IF NOT EXISTS central_financeira;
USE controle_financeiro;

-- Tabela de Categorias
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE
);

-- Tabela de Cartões
CREATE TABLE IF NOT EXISTS cartoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    limite DECIMAL(10, 2) NOT NULL
);

-- Tabela de Transações (Movimentações)
CREATE TABLE IF NOT EXISTS transacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data DATE NOT NULL,
    tipo ENUM('receita', 'despesa') NOT NULL,
    categoria VARCHAR(100),
    metodo VARCHAR(100)
);

-- Inserir algumas categorias padrão para começar
INSERT IGNORE INTO categorias (nome) VALUES ('Alimentação'), ('Lazer'), ('Salário'), ('Saúde'), ('Transporte');

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserir depois

ALTER TABLE categorias ADD COLUMN usuario_id INT;
ALTER TABLE cartoes ADD COLUMN usuario_id INT;
ALTER TABLE transacoes ADD COLUMN usuario_id INT;