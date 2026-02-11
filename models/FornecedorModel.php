<?php

require_once __DIR__ . '/../config/database.php';

class FornecedorModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getConnection();
    }

    public function listar(?string $status = null, ?string $busca = null): array
    {
        $sql = 'SELECT id, nome, cnpj, email, telefone, status, created_at, updated_at FROM fornecedores';
        $params = [];
        $where = [];
        if ($status !== null && $status !== '') {
            $where[] = 'status = :status';
            $params[':status'] = $status;
        }
        if ($busca !== null && trim($busca) !== '') {
            $termo = '%' . trim($busca) . '%';
            $where[] = '(nome LIKE :busca_nome OR email LIKE :busca_email OR cnpj LIKE :busca_cnpj OR telefone LIKE :busca_tel)';
            $params[':busca_nome'] = $termo;
            $params[':busca_email'] = $termo;
            $params[':busca_cnpj'] = $termo;
            $params[':busca_tel'] = $termo;
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
        $sql = 'SELECT id, nome, cnpj, email, telefone, status, created_at, updated_at FROM fornecedores WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function inserir(array $dados): int
    {
        $sql = 'INSERT INTO fornecedores (nome, cnpj, email, telefone, status) VALUES (:nome, :cnpj, :email, :telefone, :status)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nome'     => $dados['nome'],
            ':cnpj'     => $dados['cnpj'] ?? null,
            ':email'    => $dados['email'] ?? null,
            ':telefone' => $dados['telefone'] ?? null,
            ':status'   => $dados['status'] ?? 'A',
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $sql = 'UPDATE fornecedores SET nome = :nome, cnpj = :cnpj, email = :email, telefone = :telefone, status = :status WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id'       => $id,
            ':nome'     => $dados['nome'],
            ':cnpj'     => $dados['cnpj'] ?? null,
            ':email'    => $dados['email'] ?? null,
            ':telefone' => $dados['telefone'] ?? null,
            ':status'   => $dados['status'] ?? 'A',
        ]);
        return $stmt->rowCount() > 0;
    }

    public function excluir(int $id): bool
    {
        $sql = 'DELETE FROM fornecedores WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
