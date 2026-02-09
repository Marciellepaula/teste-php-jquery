<?php
/**
 * Controller: regra de negócio e orquestração para Fornecedores.
 * Valida entrada, chama Model e retorna JSON (AJAX) ou carrega View.
 */

require_once __DIR__ . '/../models/FornecedorModel.php';

class FornecedorController
{
    private FornecedorModel $model;

    public function __construct()
    {
        $this->model = new FornecedorModel();
    }

    /**
     * Responde em JSON para o front.
     */
    private function json(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Validação básica dos campos do fornecedor.
     * @return array Lista de erros (vazia se válido)
     */
    private function validar(array $dados): array
    {
        $errors = [];
        $nome = trim($dados['nome'] ?? '');
        if ($nome === '') {
            $errors[] = 'nome';
        }
        if (!empty($dados['email']) && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'email';
        }
        return $errors;
    }

    /**
     * Normaliza dados vindos do POST para o Model.
     */
    private function normalizarPost(): array
    {
        return [
            'nome'     => trim($_POST['nome'] ?? ''),
            'cnpj'     => trim($_POST['cnpj'] ?? '') ?: null,
            'email'    => trim($_POST['email'] ?? '') ?: null,
            'telefone' => trim($_POST['telefone'] ?? '') ?: null,
            'status'   => (isset($_POST['status']) && $_POST['status'] === 'I') ? 'I' : 'A',
        ];
    }

    /**
     * Lista fornecedores (página HTML).
     */
    public function index(): void
    {
        $fornecedores = $this->model->listar(null);
        $viewPath = __DIR__ . '/../views/fornecedores/index.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo '<p>View não encontrada.</p>';
        }
    }

    /**
     * API: retorna lista de fornecedores em JSON (para AJAX).
     */
    public function lista(): void
    {
        $status = isset($_GET['status']) && $_GET['status'] === 'I' ? 'I' : 'A';
        $fornecedores = $this->model->listar($status);
        $this->json(['success' => true, 'data' => $fornecedores]);
    }

    /**
     * API: retorna um fornecedor por ID em JSON.
     */
    public function buscar(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID inválido.']);
            return;
        }
        $fornecedor = $this->model->buscarPorId($id);
        if (!$fornecedor) {
            $this->json(['success' => false, 'message' => 'Fornecedor não encontrado.']);
            return;
        }
        $this->json(['success' => true, 'data' => $fornecedor]);
    }

    /**
     * API: cadastra novo fornecedor (POST). Retorno JSON.
     */
    public function salvar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }
        $dados = $this->normalizarPost();
        $errors = $this->validar($dados);
        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Nome é obrigatório.', 'errors' => $errors]);
            return;
        }
        try {
            $id = $this->model->inserir($dados);
            $this->json([
                'success' => true,
                'message' => 'Fornecedor cadastrado com sucesso.',
                'data'    => ['id' => $id] + $dados,
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Erro ao salvar: ' . $e->getMessage()]);
        }
    }

    /**
     * API: atualiza fornecedor existente (POST). Retorno JSON.
     */
    public function atualizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID inválido.']);
            return;
        }
        $dados = $this->normalizarPost();
        $errors = $this->validar($dados);
        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Nome é obrigatório.', 'errors' => $errors]);
            return;
        }
        try {
            $ok = $this->model->atualizar($id, $dados);
            if (!$ok) {
                $this->json(['success' => false, 'message' => 'Fornecedor não encontrado ou sem alteração.']);
                return;
            }
            $this->json([
                'success' => true,
                'message' => 'Fornecedor atualizado com sucesso.',
                'data'    => ['id' => $id] + $dados,
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Erro ao atualizar: ' . $e->getMessage()]);
        }
    }

    /**
     * API: exclui fornecedor (POST). Retorno JSON.
     */
    public function excluir(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID inválido.']);
            return;
        }
        try {
            $ok = $this->model->excluir($id);
            if (!$ok) {
                $this->json(['success' => false, 'message' => 'Fornecedor não encontrado.']);
                return;
            }
            $this->json(['success' => true, 'message' => 'Fornecedor excluído com sucesso.']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Erro ao excluir: ' . $e->getMessage()]);
        }
    }
}
