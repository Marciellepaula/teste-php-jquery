<?php
/**
 * Controller: regra de negócio e orquestração para Produtos.
 * Valida entrada, chama Model e retorna JSON (AJAX) ou carrega View.
 */

require_once __DIR__ . '/../models/ProdutoModel.php';
require_once __DIR__ . '/../models/FornecedorProdutoModel.php';

class ProdutoController
{
    private ProdutoModel $model;
    private FornecedorProdutoModel $vinculoModel;

    public function __construct()
    {
        $this->model = new ProdutoModel();
        $this->vinculoModel = new FornecedorProdutoModel();
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
     * Validação básica dos campos do produto.
     * @return array Lista de erros (nomes dos campos)
     */
    private function validar(array $dados, ?int $idParaEdicao = null): array
    {
        $errors = [];
        $nome = trim($dados['nome'] ?? '');
        if ($nome === '') {
            $errors[] = 'nome';
        }
        $codigo = trim($dados['codigo_interno'] ?? '');
        if ($codigo !== '' && $this->model->existeCodigoInterno($codigo, $idParaEdicao)) {
            $errors[] = 'codigo_interno';
        }
        return $errors;
    }

    /**
     * Normaliza dados vindos do POST para o Model.
     */
    private function normalizarPost(): array
    {
        return [
            'nome'           => trim($_POST['nome'] ?? ''),
            'descricao'      => trim($_POST['descricao'] ?? '') ?: null,
            'codigo_interno' => trim($_POST['codigo_interno'] ?? '') ?: null,
            'status'         => (isset($_POST['status']) && $_POST['status'] === 'I') ? 'I' : 'A',
        ];
    }

    /**
     * Lista produtos (página HTML).
     */
    public function index(): void
    {
        $produtos = $this->model->listar(null);
        $viewPath = __DIR__ . '/../views/produtos/index.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo '<p>View não encontrada.</p>';
        }
    }

    /**
     * API: retorna lista de produtos em JSON (para AJAX).
     * GET status: 'A', 'I' ou omitir = todos.
     */
    public function lista(): void
    {
        $status = null;
        if (isset($_GET['status']) && $_GET['status'] === 'I') {
            $status = 'I';
        } elseif (isset($_GET['status']) && $_GET['status'] === 'A') {
            $status = 'A';
        }
        $produtos = $this->model->listar($status);
        $this->json(['success' => true, 'data' => $produtos]);
    }

    /**
     * API: retorna um produto por ID em JSON.
     */
    public function buscar(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID inválido.']);
            return;
        }
        $produto = $this->model->buscarPorId($id);
        if (!$produto) {
            $this->json(['success' => false, 'message' => 'Produto não encontrado.']);
            return;
        }
        $this->json(['success' => true, 'data' => $produto]);
    }

    /**
     * API: cadastra novo produto (POST). Retorno JSON.
     */
    public function salvar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }
        $dados = $this->normalizarPost();
        $errors = $this->validar($dados, null);
        if (!empty($errors)) {
            $msg = $this->mensagemErroValidacao($errors);
            $this->json(['success' => false, 'message' => $msg, 'errors' => $errors]);
            return;
        }
        try {
            $id = $this->model->inserir($dados);
            $this->json([
                'success' => true,
                'message' => 'Produto cadastrado com sucesso.',
                'data'    => ['id' => $id] + $dados,
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Erro ao salvar: ' . $e->getMessage()]);
        }
    }

    /**
     * API: atualiza produto existente (POST). Retorno JSON.
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
        $errors = $this->validar($dados, $id);
        if (!empty($errors)) {
            $msg = $this->mensagemErroValidacao($errors);
            $this->json(['success' => false, 'message' => $msg, 'errors' => $errors]);
            return;
        }
        try {
            $ok = $this->model->atualizar($id, $dados);
            if (!$ok) {
                $this->json(['success' => false, 'message' => 'Produto não encontrado ou sem alteração.']);
                return;
            }
            $this->json([
                'success' => true,
                'message' => 'Produto atualizado com sucesso.',
                'data'    => ['id' => $id] + $dados,
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Erro ao atualizar: ' . $e->getMessage()]);
        }
    }

    /**
     * API: exclui produto (POST). Retorno JSON.
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
                $this->json(['success' => false, 'message' => 'Produto não encontrado.']);
                return;
            }
            $this->json(['success' => true, 'message' => 'Produto excluído com sucesso.']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Erro ao excluir: ' . $e->getMessage()]);
        }
    }

    private function mensagemErroValidacao(array $errors): string
    {
        if (in_array('codigo_interno', $errors)) {
            return 'Código interno já está em uso por outro produto.';
        }
        if (in_array('nome', $errors)) {
            return 'Nome é obrigatório.';
        }
        return 'Verifique os campos.';
    }

    // ---------- Vínculo produto × fornecedor ----------

    /**
     * API: lista fornecedores vinculados a um produto (JSON).
     * GET produto_id
     */
    public function listaFornecedores(): void
    {
        $produtoId = (int) ($_GET['produto_id'] ?? 0);
        if ($produtoId <= 0) {
            $this->json(['success' => false, 'message' => 'ID do produto inválido.']);
            return;
        }
        $fornecedores = $this->vinculoModel->listarFornecedoresDoProduto($produtoId);
        $this->json(['success' => true, 'data' => $fornecedores]);
    }

    /**
     * API: busca fornecedores não vinculados ao produto (para adicionar). Busca dinâmica.
     * GET produto_id, q (termo de busca)
     */
    public function buscaFornecedoresParaVincular(): void
    {
        $produtoId = (int) ($_GET['produto_id'] ?? 0);
        if ($produtoId <= 0) {
            $this->json(['success' => false, 'message' => 'ID do produto inválido.']);
            return;
        }
        $busca = trim($_GET['q'] ?? '');
        $fornecedores = $this->vinculoModel->listarFornecedoresNaoVinculados($produtoId, $busca);
        $this->json(['success' => true, 'data' => $fornecedores]);
    }

    /**
     * API: cria vínculo produto-fornecedor (POST).
     */
    public function vincularFornecedor(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }
        $produtoId    = (int) ($_POST['produto_id'] ?? 0);
        $fornecedorId = (int) ($_POST['fornecedor_id'] ?? 0);
        if ($produtoId <= 0 || $fornecedorId <= 0) {
            $this->json(['success' => false, 'message' => 'IDs inválidos.']);
            return;
        }
        $ok = $this->vinculoModel->vincular($produtoId, $fornecedorId);
        if (!$ok) {
            $this->json(['success' => false, 'message' => 'Vínculo já existe ou registro não encontrado.']);
            return;
        }
        $this->json(['success' => true, 'message' => 'Fornecedor vinculado com sucesso.']);
    }

    /**
     * API: remove vínculo entre produto e fornecedor (POST).
     */
    public function desvincularFornecedor(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }
        $produtoId    = (int) ($_POST['produto_id'] ?? 0);
        $fornecedorId = (int) ($_POST['fornecedor_id'] ?? 0);
        if ($produtoId <= 0 || $fornecedorId <= 0) {
            $this->json(['success' => false, 'message' => 'IDs inválidos.']);
            return;
        }
        $ok = $this->vinculoModel->desvincular($produtoId, $fornecedorId);
        if (!$ok) {
            $this->json(['success' => false, 'message' => 'Vínculo não encontrado.']);
            return;
        }
        $this->json(['success' => true, 'message' => 'Vínculo removido.']);
    }

    /**
     * API: remove todos os vínculos do produto (POST).
     */
    public function desvincularTodosFornecedores(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }
        $produtoId = (int) ($_POST['produto_id'] ?? 0);
        if ($produtoId <= 0) {
            $this->json(['success' => false, 'message' => 'ID do produto inválido.']);
            return;
        }
        $qtd = $this->vinculoModel->desvincularTodos($produtoId);
        $this->json(['success' => true, 'message' => $qtd > 0 ? $qtd . ' vínculo(s) removido(s).' : 'Nenhum vínculo para remover.', 'count' => $qtd]);
    }
}
