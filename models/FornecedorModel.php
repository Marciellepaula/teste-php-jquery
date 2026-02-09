<?php
/**
 * Model: acesso a dados de fornecedores.
 * Apenas operações de banco (Prepared Statements). Sem regra de negócio.
 */

require_once __DIR__ . '/../config/database.php';

class FornecedorModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getConnection();
    }

    /**
     * Lista todos os fornecedores, opcionalmente filtrados por status.
     * @param string|null $status 'A', 'I' ou null para todos
     * @return array
     */
    public function listar(?string $status = 'A'): array
    {
        $sql = 'SELECT id, nome, cnpj, email, telefone, status, created_at, updated_at FROM fornecedores';
        $params = [];
        if ($status !== null) {
            $sql .= ' WHERE status = :status';
            $params[':status'] = $status;
        }
        $sql .= ' ORDER BY nome';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Busca um fornecedor por ID.
     * @return array|null
     */
    public function buscarPorId(int $id): ?array
    {
        $sql = 'SELECT id, nome, cnpj, email, telefone, status, created_at, updated_at FROM fornecedores WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Insere um novo fornecedor.
     * @return int ID do registro inserido
     */
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

    /**
     * Atualiza um fornecedor existente.
     * @return bool
     */
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

    /**
     * Exclui um fornecedor (ou apenas marca como inativo, conforme regra).
     * Aqui: exclusão física.
     * @return bool
     */
    public function excluir(int $id): bool
    {
        $sql = 'DELETE FROM fornecedores WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
