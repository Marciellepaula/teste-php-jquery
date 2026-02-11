CREATE DATABASE IF NOT EXISTS teste_php_jquery CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE teste_php_jquery;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `vinculo_historico`;
DROP TABLE IF EXISTS `fornecedor_produto`;
DROP TABLE IF EXISTS `fornecedores`;
DROP TABLE IF EXISTS `produtos`;

CREATE TABLE `fornecedores` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `cnpj` VARCHAR(18) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `telefone` VARCHAR(20) NOT NULL,
  `status` CHAR(1) NOT NULL DEFAULT 'A',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fornecedores_status` (`status`),
  KEY `idx_fornecedores_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `produtos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `descricao` TEXT NOT NULL,
  `codigo_interno` VARCHAR(50) NOT NULL,
  `preco` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` CHAR(1) NOT NULL DEFAULT 'A',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_produtos_codigo_interno` (`codigo_interno`),
  KEY `idx_produtos_status` (`status`),
  KEY `idx_produtos_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `fornecedor_produto` (
  `fornecedor_id` INT UNSIGNED NOT NULL,
  `produto_id` INT UNSIGNED NOT NULL,
  `principal` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`fornecedor_id`, `produto_id`),
  KEY `fk_fp_produto` (`produto_id`),
  CONSTRAINT `fk_fp_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_fp_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `vinculo_historico` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `produto_id` INT UNSIGNED NOT NULL,
  `fornecedor_id` INT UNSIGNED NOT NULL,
  `acao` VARCHAR(20) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vh_produto` (`produto_id`),
  CONSTRAINT `fk_vh_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vh_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
