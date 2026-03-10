CREATE DATABASE IF NOT EXISTS central_financeira;
USE central_financeira;

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

-- Dia do fechamento da fatura de cada cartão (1-31, padrão 1)
ALTER TABLE cartoes ADD COLUMN dia_fechamento TINYINT NOT NULL DEFAULT 1;

-- Suporte a recorrência e parcelamento
-- tipo_lancamento: 'unico' | 'recorrente' | 'parcelado'
-- grupo_id: liga todas as parcelas/recorrências de um mesmo lançamento
-- parcela_atual: número da parcela (1, 2, 3...) — NULL se único
-- total_parcelas: total de parcelas — NULL se único ou recorrente
ALTER TABLE transacoes ADD COLUMN tipo_lancamento ENUM('unico','recorrente','parcelado') NOT NULL DEFAULT 'unico';
ALTER TABLE transacoes ADD COLUMN grupo_id CHAR(36) NULL;
ALTER TABLE transacoes ADD COLUMN parcela_atual INT NULL;
ALTER TABLE transacoes ADD COLUMN total_parcelas INT NULL;

-- Campo para distinguir assinaturas de transações normais
ALTER TABLE transacoes ADD COLUMN eh_assinatura TINYINT NOT NULL DEFAULT 0;

-- Campo para distinguir transações em cartão de outras formas
ALTER TABLE transacoes ADD COLUMN eh_cartao TINYINT NOT NULL DEFAULT 0;