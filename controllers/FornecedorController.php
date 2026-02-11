<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../core/ValidationTrait.php';
require_once __DIR__ . '/../models/FornecedorModel.php';

class FornecedorController extends BaseController
{
    use ValidationTrait;

    private FornecedorModel $model;

    public function __construct()
    {
        $this->model = new FornecedorModel();
    }

    private function validar(array $dados): array
    {
        $errors = [];
        if ($this->validarObrigatorio($dados, 'nome')) {
            $errors[] = 'nome';
        }
        if ($this->validarObrigatorio($dados, 'cnpj')) {
            $errors[] = 'cnpj';
        }
        if ($this->validarObrigatorio($dados, 'telefone')) {
            $errors[] = 'telefone';
        }
        if ($this->validarObrigatorio($dados, 'email')) {
            $errors[] = 'email';
        } elseif (!$this->validarEmail($dados['email'])) {
            $errors[] = 'email';
        }
        return $errors;
    }

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

    private function mensagemErroFornecedor(array $errors): string
    {
        if (in_array('email', $errors)) {
            $email = trim($_POST['email'] ?? '');
            return $email === '' ? 'E-mail é obrigatório.' : 'E-mail inválido.';
        }
        if (in_array('nome', $errors)) return 'Nome é obrigatório.';
        if (in_array('cnpj', $errors)) return 'CNPJ é obrigatório.';
        if (in_array('telefone', $errors)) return 'Telefone é obrigatório.';
        return 'Verifique os campos.';
    }

    public function index(): void
    {
        $fornecedores = $this->model->listar(null);
        $this->renderView('views/fornecedores/index.php', ['fornecedores' => $fornecedores]);
    }

    public function lista(): void
    {
        $status = null;
        if (isset($_GET['status']) && $_GET['status'] === 'I') {
            $status = 'I';
        } elseif (isset($_GET['status']) && $_GET['status'] === 'A') {
            $status = 'A';
        }
        $fornecedores = $this->model->listar($status);
        $this->json(['success' => true, 'data' => $fornecedores]);
    }

    public function buscar(): void
    {
        $id = $this->getInt('id', 'GET');
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

    public function salvar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }
        $dados = $this->normalizarPost();
        $errors = $this->validar($dados);
        if (!empty($errors)) {
            $msg = $this->mensagemErroFornecedor($errors);
            $this->json(['success' => false, 'message' => $msg, 'errors' => $errors]);
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

    public function atualizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }
        $id = $this->getInt('id', 'POST');
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID inválido.']);
            return;
        }
        $dados = $this->normalizarPost();
        $errors = $this->validar($dados);
        if (!empty($errors)) {
            $msg = $this->mensagemErroFornecedor($errors);
            $this->json(['success' => false, 'message' => $msg, 'errors' => $errors]);
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

    public function excluir(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }
        $id = $this->getInt('id', 'POST');
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
