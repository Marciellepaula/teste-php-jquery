ALTER TABLE `fornecedor_produto`
  ADD COLUMN `principal` TINYINT(1) NOT NULL DEFAULT 0 AFTER `created_at`;

CREATE TABLE IF NOT EXISTS `vinculo_historico` (
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
