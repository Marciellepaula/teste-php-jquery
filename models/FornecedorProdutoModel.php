<?php

require_once __DIR__ . '/../config/database.php';

class FornecedorProdutoModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getConnection();
    }

    public function listarFornecedoresDoProduto(int $produtoId): array
    {
        $sql = 'SELECT f.id, f.nome, f.cnpj, f.email, f.telefone, f.status
                FROM fornecedores f
                INNER JOIN fornecedor_produto fp ON fp.fornecedor_id = f.id
                WHERE fp.produto_id = :produto_id
                ORDER BY f.nome';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':produto_id' => $produtoId]);
        return $stmt->fetchAll();
    }

    public function listarFornecedoresNaoVinculados(int $produtoId, string $busca = ''): array
    {
        $sql = 'SELECT f.id, f.nome, f.email
                FROM fornecedores f
                WHERE f.id NOT IN (
                    SELECT fp.fornecedor_id FROM fornecedor_produto fp WHERE fp.produto_id = :produto_id
                )';
        $params = [':produto_id' => $produtoId];
        if ($busca !== '') {
            $sql .= ' AND (f.nome LIKE :busca OR f.email LIKE :busca2)';
            $termo = '%' . trim($busca) . '%';
            $params[':busca'] = $termo;
            $params[':busca2'] = $termo;
        }
        $sql .= ' ORDER BY f.nome LIMIT 20';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function vincular(int $produtoId, int $fornecedorId): bool
    {
        $sql = 'INSERT IGNORE INTO fornecedor_produto (fornecedor_id, produto_id) VALUES (:fornecedor_id, :produto_id)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':fornecedor_id' => $fornecedorId,
            ':produto_id'    => $produtoId,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function desvincular(int $produtoId, int $fornecedorId): bool
    {
        $sql = 'DELETE FROM fornecedor_produto WHERE produto_id = :produto_id AND fornecedor_id = :fornecedor_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':produto_id'    => $produtoId,
            ':fornecedor_id' => $fornecedorId,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function desvincularTodos(int $produtoId): int
    {
        $sql = 'DELETE FROM fornecedor_produto WHERE produto_id = :produto_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':produto_id' => $produtoId]);
        return $stmt->rowCount();
    }
}
