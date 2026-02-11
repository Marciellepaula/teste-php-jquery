<?php

declare(strict_types=1);

class ProdutoModel implements CrudModelInterface
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getConnection();
    }

    public function listar(?string $status = null, ?string $busca = null): array
    {
        $sql = 'SELECT id, nome, descricao, codigo_interno, status, created_at, updated_at FROM produtos';
        $params = [];
        $where = [];
        if ($status !== null && $status !== '') {
            $where[] = 'status = :status';
            $params[':status'] = $status;
        }
        if ($busca !== null && trim($busca) !== '') {
            $termo = '%' . trim($busca) . '%';
            $where[] = '(nome LIKE :busca_nome OR codigo_interno LIKE :busca_codigo OR descricao LIKE :busca_desc)';
            $params[':busca_nome'] = $termo;
            $params[':busca_codigo'] = $termo;
            $params[':busca_desc'] = $termo;
        }
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY nome';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = 'SELECT id, nome, descricao, codigo_interno, status, created_at, updated_at FROM produtos WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function existeCodigoInterno(string $codigo, ?int $excluirId = null): bool
    {
        $codigo = trim($codigo);
        if ($codigo === '') {
            return false;
        }
        $sql = 'SELECT 1 FROM produtos WHERE codigo_interno = :codigo';
        $params = [':codigo' => $codigo];
        if ($excluirId !== null) {
            $sql .= ' AND id != :id';
            $params[':id'] = $excluirId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    public function inserir(array $dados): int
    {
        $sql = 'INSERT INTO produtos (nome, descricao, codigo_interno, status) VALUES (:nome, :descricao, :codigo_interno, :status)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nome'           => $dados['nome'],
            ':descricao'      => $dados['descricao'] ?? null,
            ':codigo_interno' => $dados['codigo_interno'] ?? null,
            ':status'         => $dados['status'] ?? 'A',
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $sql = 'UPDATE produtos SET nome = :nome, descricao = :descricao, codigo_interno = :codigo_interno, status = :status WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id'             => $id,
            ':nome'           => $dados['nome'],
            ':descricao'      => $dados['descricao'] ?? null,
            ':codigo_interno' => $dados['codigo_interno'] ?? null,
            ':status'         => $dados['status'] ?? 'A',
        ]);
        return $stmt->rowCount() > 0;
    }

    public function excluir(int $id): bool
    {
        $sql = 'DELETE FROM produtos WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
