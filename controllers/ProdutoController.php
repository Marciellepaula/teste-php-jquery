<?php

declare(strict_types=1);

class ProdutoController extends AbstractCrudController
{
    use ValidationTrait;

    private ProdutoModel $model;
    private FornecedorProdutoModel $vinculoModel;

    public function __construct()
    {
        $this->model = new ProdutoModel();
        $this->vinculoModel = new FornecedorProdutoModel();
    }

    protected function getModel(): CrudModelInterface
    {
        return $this->model;
    }

    protected function getEntityName(): string
    {
        return 'Produto';
    }

    private function validar(array $dados, ?int $idParaEdicao = null): array
    {
        $errors = [];
        if ($this->campoObrigatorioEstaVazio($dados, 'nome')) {
            $errors[] = 'nome';
        }
        if ($this->campoObrigatorioEstaVazio($dados, 'descricao')) {
            $errors[] = 'descricao';
        }
        if ($this->campoObrigatorioEstaVazio($dados, 'codigo_interno')) {
            $errors[] = 'codigo_interno';
        } elseif ($this->model->existeCodigoInterno(trim($dados['codigo_interno'] ?? ''), $idParaEdicao)) {
            $errors[] = 'codigo_interno';
        }
        if ($this->campoObrigatorioEstaVazio($dados, 'status')) {
            $errors[] = 'status';
        }
        return $errors;
    }

    private function normalizarPost(): array
    {
        return [
            'nome'           => trim($_POST['nome'] ?? ''),
            'descricao'      => trim($_POST['descricao'] ?? '') ?: null,
            'codigo_interno' => trim($_POST['codigo_interno'] ?? '') ?: null,
            'status'         => (isset($_POST['status']) && $_POST['status'] === 'I') ? 'I' : 'A',
        ];
    }

    public function index(): void
    {
        $produtos = $this->model->listar(null);
        $this->renderView('views/produtos/index.php', ['produtos' => $produtos]);
    }

    public function salvar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }
        $this->requireCsrf();
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
        } catch (Throwable $e) {
            $this->handleException($e, 'Erro ao salvar o produto.');
        }
    }

    public function atualizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }
        $this->requireCsrf();
        $id = $this->getInt('id', 'POST');
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
        } catch (Throwable $e) {
            $this->handleException($e, 'Erro ao atualizar o produto.');
        }
    }

    private function mensagemErroValidacao(array $errors): string
    {
        if (in_array('codigo_interno', $errors)) {
            $codigo = trim($_POST['codigo_interno'] ?? '');
            return $codigo === '' ? 'Código interno é obrigatório.' : 'Código interno já está em uso por outro produto.';
        }
        if (in_array('nome', $errors)) return 'Nome é obrigatório.';
        if (in_array('descricao', $errors)) return 'Descrição é obrigatória.';
        if (in_array('status', $errors)) return 'Status é obrigatório.';
        return 'Verifique os campos.';
    }

    public function listaFornecedores(): void
    {
        $produtoId = $this->getInt('produto_id', 'GET');
        if ($produtoId <= 0) {
            $this->json(['success' => false, 'message' => 'ID do produto inválido.']);
            return;
        }
        $fornecedores = $this->vinculoModel->listarFornecedoresDoProduto($produtoId);
        $this->json(['success' => true, 'data' => $fornecedores]);
    }

    public function buscaFornecedoresParaVincular(): void
    {
        $produtoId = $this->getInt('produto_id', 'GET');
        if ($produtoId <= 0) {
            $this->json(['success' => false, 'message' => 'ID do produto inválido.']);
            return;
        }
        $busca = trim($_GET['q'] ?? '');
        $fornecedores = $this->vinculoModel->listarFornecedoresNaoVinculados($produtoId, $busca);
        $this->json(['success' => true, 'data' => $fornecedores]);
    }

    public function vincularFornecedor(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }
        $this->requireCsrf();
        $produtoId    = $this->getInt('produto_id', 'POST');
        $fornecedorId = $this->getInt('fornecedor_id', 'POST');
        if ($produtoId <= 0 || $fornecedorId <= 0) {
            $this->json(['success' => false, 'message' => 'IDs inválidos.']);
            return;
        }
        if (!$this->vinculoModel->fornecedorEstaAtivo($fornecedorId)) {
            $this->json(['success' => false, 'message' => 'Não é possível vincular fornecedor inativo.']);
            return;
        }
        $ok = $this->vinculoModel->vincular($produtoId, $fornecedorId);
        if (!$ok) {
            $this->json(['success' => false, 'message' => 'Vínculo já existe ou registro não encontrado.']);
            return;
        }
        $this->json(['success' => true, 'message' => 'Fornecedor vinculado com sucesso.']);
    }

    public function desvincularFornecedor(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }
        $this->requireCsrf();
        $produtoId    = $this->getInt('produto_id', 'POST');
        $fornecedorId = $this->getInt('fornecedor_id', 'POST');
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

    public function desvincularTodosFornecedores(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }
        $this->requireCsrf();
        $produtoId = $this->getInt('produto_id', 'POST');
        if ($produtoId <= 0) {
            $this->json(['success' => false, 'message' => 'ID do produto inválido.']);
            return;
        }
        $qtd = $this->vinculoModel->desvincularTodos($produtoId);
        $this->json(['success' => true, 'message' => $qtd > 0 ? $qtd . ' vínculo(s) removido(s).' : 'Nenhum vínculo para remover.', 'count' => $qtd]);
    }

    public function definirFornecedorPrincipal(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }
        $this->requireCsrf();
        $produtoId    = $this->getInt('produto_id', 'POST');
        $fornecedorId = $this->getInt('fornecedor_id', 'POST');
        if ($produtoId <= 0 || $fornecedorId <= 0) {
            $this->json(['success' => false, 'message' => 'IDs inválidos.']);
            return;
        }
        $ok = $this->vinculoModel->setFornecedorPrincipal($produtoId, $fornecedorId);
        if (!$ok) {
            $this->json(['success' => false, 'message' => 'Vínculo não encontrado.']);
            return;
        }
        $this->json(['success' => true, 'message' => 'Fornecedor definido como principal.']);
    }

    public function listaHistoricoVinculos(): void
    {
        $produtoId = $this->getInt('produto_id', 'GET');
        if ($produtoId <= 0) {
            $this->json(['success' => false, 'message' => 'ID do produto inválido.']);
            return;
        }
        $historico = $this->vinculoModel->listarHistorico($produtoId, 30);
        $this->json(['success' => true, 'data' => $historico]);
    }
}
