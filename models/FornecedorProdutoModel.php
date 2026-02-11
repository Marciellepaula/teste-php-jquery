<?php

declare(strict_types=1);

class FornecedorProdutoModel
{
    private PDO $pdo;

    private ?bool $hasPrincipalColumn = null;

    private ?bool $hasHistoricoTable = null;

    public function __construct()
    {
        $this->pdo = getConnection();
    }

    private function hasPrincipalColumn(): bool
    {
        if ($this->hasPrincipalColumn !== null) {
            return $this->hasPrincipalColumn;
        }
        $stmt = $this->pdo->query("SHOW COLUMNS FROM fornecedor_produto LIKE 'principal'");
        $this->hasPrincipalColumn = $stmt && $stmt->rowCount() > 0;
        return $this->hasPrincipalColumn;
    }

    private function hasHistoricoTable(): bool
    {
        if ($this->hasHistoricoTable !== null) {
            return $this->hasHistoricoTable;
        }
        $stmt = $this->pdo->query("SHOW TABLES LIKE 'vinculo_historico'");
        $this->hasHistoricoTable = $stmt && $stmt->rowCount() > 0;
        return $this->hasHistoricoTable;
    }

    public function listarFornecedoresDoProduto(int $produtoId): array
    {
        if ($this->hasPrincipalColumn()) {
            $sql = 'SELECT f.id, f.nome, f.cnpj, f.email, f.telefone, f.status, COALESCE(fp.principal, 0) AS principal
                    FROM fornecedores f
                    INNER JOIN fornecedor_produto fp ON fp.fornecedor_id = f.id
                    WHERE fp.produto_id = :produto_id
                    ORDER BY fp.principal DESC, f.nome';
        } else {
            $sql = 'SELECT f.id, f.nome, f.cnpj, f.email, f.telefone, f.status, 0 AS principal
                    FROM fornecedores f
                    INNER JOIN fornecedor_produto fp ON fp.fornecedor_id = f.id
                    WHERE fp.produto_id = :produto_id
                    ORDER BY f.nome';
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':produto_id' => $produtoId]);
        return $stmt->fetchAll();
    }

    public function listarFornecedoresNaoVinculados(int $produtoId, string $busca = ''): array
    {
        $sql = 'SELECT f.id, f.nome, f.email
                FROM fornecedores f
                WHERE f.status = \'A\'
                AND f.id NOT IN (
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
        $check = 'SELECT 1 FROM fornecedores WHERE id = :id AND status = :status';
        $stmtCheck = $this->pdo->prepare($check);
        $stmtCheck->execute([':id' => $fornecedorId, ':status' => 'A']);
        if (!$stmtCheck->fetch()) {
            return false;
        }
        if ($this->hasPrincipalColumn()) {
            $sql = 'INSERT IGNORE INTO fornecedor_produto (fornecedor_id, produto_id, principal) VALUES (:fornecedor_id, :produto_id, 0)';
        } else {
            $sql = 'INSERT IGNORE INTO fornecedor_produto (fornecedor_id, produto_id) VALUES (:fornecedor_id, :produto_id)';
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':fornecedor_id' => $fornecedorId,
            ':produto_id'    => $produtoId,
        ]);
        if ($stmt->rowCount() > 0) {
            if ($this->hasHistoricoTable()) {
                $this->registrarHistorico($produtoId, $fornecedorId, 'vinculado');
            }
            return true;
        }
        return false;
    }

    public function desvincular(int $produtoId, int $fornecedorId): bool
    {
        if ($this->hasHistoricoTable()) {
            $this->registrarHistorico($produtoId, $fornecedorId, 'desvinculado');
        }
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
        if ($this->hasHistoricoTable()) {
            $rows = $this->listarFornecedoresDoProduto($produtoId);
            foreach ($rows as $f) {
                $this->registrarHistorico($produtoId, (int) $f['id'], 'desvinculado');
            }
        }
        $sql = 'DELETE FROM fornecedor_produto WHERE produto_id = :produto_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':produto_id' => $produtoId]);
        return $stmt->rowCount();
    }

    public function fornecedorEstaAtivo(int $fornecedorId): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM fornecedores WHERE id = :id AND status = :status');
        $stmt->execute([':id' => $fornecedorId, ':status' => 'A']);
        return (bool) $stmt->fetch();
    }

    public function setFornecedorPrincipal(int $produtoId, int $fornecedorId): bool
    {
        if (!$this->hasPrincipalColumn()) {
            return false;
        }
        $check = 'SELECT 1 FROM fornecedor_produto WHERE produto_id = :pid AND fornecedor_id = :fid';
        $stmt = $this->pdo->prepare($check);
        $stmt->execute([':pid' => $produtoId, ':fid' => $fornecedorId]);
        if (!$stmt->fetch()) {
            return false;
        }
        $this->pdo->prepare('UPDATE fornecedor_produto SET principal = 0 WHERE produto_id = :pid')->execute([':pid' => $produtoId]);
        $stmt2 = $this->pdo->prepare('UPDATE fornecedor_produto SET principal = 1 WHERE produto_id = :pid AND fornecedor_id = :fid');
        $stmt2->execute([':pid' => $produtoId, ':fid' => $fornecedorId]);
        return $stmt2->rowCount() > 0;
    }

    public function registrarHistorico(int $produtoId, int $fornecedorId, string $acao): void
    {
        $sql = 'INSERT INTO vinculo_historico (produto_id, fornecedor_id, acao) VALUES (:pid, :fid, :acao)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pid' => $produtoId, ':fid' => $fornecedorId, ':acao' => $acao]);
    }

    public function listarHistorico(int $produtoId, int $limit = 30): array
    {
        if (!$this->hasHistoricoTable()) {
            return [];
        }
        $limit = max(1, min(500, $limit));
        $sql = 'SELECT vh.id, vh.fornecedor_id, vh.acao, vh.created_at, f.nome AS fornecedor_nome
                FROM vinculo_historico vh
                INNER JOIN fornecedores f ON f.id = vh.fornecedor_id
                WHERE vh.produto_id = :pid
                ORDER BY vh.created_at DESC
                LIMIT :lim';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':pid', $produtoId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
