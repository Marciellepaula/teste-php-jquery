-- ============================================================
-- Schema: Sistema Fornecedores x Produtos (N:N)
-- MySQL 5.7+ / MariaDB 10.2+
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- Tabela: fornecedores
-- Status: A = Ativo, I = Inativo
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `fornecedor_produto`;
DROP TABLE IF EXISTS `fornecedores`;
DROP TABLE IF EXISTS `produtos`;

CREATE TABLE `fornecedores` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `cnpj` VARCHAR(18) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `telefone` VARCHAR(20) DEFAULT NULL,
  `status` CHAR(1) NOT NULL DEFAULT 'A' COMMENT 'A=Ativo, I=Inativo',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fornecedores_status` (`status`),
  KEY `idx_fornecedores_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabela: produtos
-- Status: A = Ativo, I = Inativo
-- ------------------------------------------------------------
CREATE TABLE `produtos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `descricao` TEXT DEFAULT NULL,
  `codigo_interno` VARCHAR(50) DEFAULT NULL COMMENT 'Código interno do produto',
  `preco` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` CHAR(1) NOT NULL DEFAULT 'A' COMMENT 'A=Ativo, I=Inativo',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_produtos_codigo_interno` (`codigo_interno`),
  KEY `idx_produtos_status` (`status`),
  KEY `idx_produtos_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabela: fornecedor_produto (N:N)
-- Um produto pode ter vários fornecedores e vice-versa
-- ------------------------------------------------------------
CREATE TABLE `fornecedor_produto` (
  `fornecedor_id` INT UNSIGNED NOT NULL,
  `produto_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`fornecedor_id`, `produto_id`),
  KEY `fk_fp_produto` (`produto_id`),
  CONSTRAINT `fk_fp_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_fp_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Exemplos de JOIN: listar produtos com seus fornecedores
-- ============================================================

-- Produtos com quantidade de fornecedores
-- SELECT p.id, p.nome, p.preco, p.status,
--        COUNT(fp.fornecedor_id) AS qtd_fornecedores
-- FROM produtos p
-- LEFT JOIN fornecedor_produto fp ON fp.produto_id = p.id
-- WHERE p.status = 'A'
-- GROUP BY p.id, p.nome, p.preco, p.status;

-- Produtos com nomes dos fornecedores (um registro por fornecedor do produto)
-- SELECT p.id AS produto_id, p.nome AS produto_nome, p.preco,
--        f.id AS fornecedor_id, f.nome AS fornecedor_nome, f.email
-- FROM produtos p
-- INNER JOIN fornecedor_produto fp ON fp.produto_id = p.id
-- INNER JOIN fornecedores f ON f.id = fp.fornecedor_id AND f.status = 'A'
-- WHERE p.status = 'A'
-- ORDER BY p.nome, f.nome;

-- Fornecedores com quantidade de produtos
-- SELECT f.id, f.nome, f.cnpj, f.status,
--        COUNT(fp.produto_id) AS qtd_produtos
-- FROM fornecedores f
-- LEFT JOIN fornecedor_produto fp ON fp.fornecedor_id = f.id
-- WHERE f.status = 'A'
-- GROUP BY f.id, f.nome, f.cnpj, f.status;
