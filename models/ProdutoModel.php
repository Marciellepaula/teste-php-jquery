<?php
/**
 * Model: acesso a dados de produtos.
 * Apenas operações de banco (Prepared Statements). Sem regra de negócio.
 */

require_once __DIR__ . '/../config/database.php';

class ProdutoModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getConnection();
    }

    /**
     * Lista todos os produtos, opcionalmente filtrados por status.
     * @param string|null $status 'A', 'I' ou null para todos
     * @return array
     */
    public function listar(?string $status = null): array
    {
        $sql = 'SELECT id, nome, descricao, codigo_interno, status, created_at, updated_at FROM produtos';
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
     * Busca um produto por ID.
     * @return array|null
     */
    public function buscarPorId(int $id): ?array
    {
        $sql = 'SELECT id, nome, descricao, codigo_interno, status, created_at, updated_at FROM produtos WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Verifica se já existe produto com o mesmo código interno (excluindo um ID).
     * @return bool
     */
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

    /**
     * Insere um novo produto.
     * @return int ID do registro inserido
     */
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

    /**
     * Atualiza um produto existente.
     * @return bool
     */
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

    /**
     * Exclui um produto.
     * @return bool
     */
    public function excluir(int $id): bool
    {
        $sql = 'DELETE FROM produtos WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
