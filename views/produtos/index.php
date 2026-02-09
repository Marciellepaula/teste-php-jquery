<?php
/**
 * View: listagem de produtos e formulário (modal) para cadastro/edição.
 * Variável disponível: $produtos (array)
 */
$produtos = $produtos ?? [];
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
$apiBase = ($baseUrl ? $baseUrl . '/' : '') . 'index.php?controller=produto';
$indexUrl = ($baseUrl ? $baseUrl . '/' : '') . 'index.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos</title>
    <link rel="stylesheet" href="<?= $baseUrl ? htmlspecialchars($baseUrl) . '/css/style.css' : 'css/style.css' ?>">
</head>
<body>
    <div class="container">
        <nav class="page-nav">
            <a href="<?= htmlspecialchars($indexUrl) ?>?controller=fornecedor&action=index">Fornecedores</a>
            <a href="<?= htmlspecialchars($indexUrl) ?>?controller=produto&action=index" class="active">Produtos</a>
        </nav>
        <header class="page-header">
            <h1>Produtos</h1>
            <button type="button" class="btn btn-primary" id="btn-novo-produto">Novo produto</button>
        </header>

        <div id="mensagem" class="mensagem" role="alert" aria-live="polite"></div>

        <div class="card">
            <table class="tabela" id="tabela-produtos">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Código interno</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos as $p): ?>
                    <tr data-id="<?= (int) $p['id'] ?>">
                        <td><?= (int) $p['id'] ?></td>
                        <td><?= htmlspecialchars($p['nome']) ?></td>
                        <td class="cell-descricao"><?= htmlspecialchars(mb_substr($p['descricao'] ?? '', 0, 60)) ?><?= mb_strlen($p['descricao'] ?? '') > 60 ? '…' : '' ?></td>
                        <td><?= htmlspecialchars($p['codigo_interno'] ?? '') ?></td>
                        <td><span class="badge badge-<?= $p['status'] === 'A' ? 'ativo' : 'inativo' ?>"><?= $p['status'] === 'A' ? 'Ativo' : 'Inativo' ?></span></td>
                        <td>
                            <button type="button" class="btn btn-small btn-fornecedores" data-id="<?= (int) $p['id'] ?>" data-nome="<?= htmlspecialchars($p['nome']) ?>">Fornecedores</button>
                            <button type="button" class="btn btn-small btn-editar" data-id="<?= (int) $p['id'] ?>">Editar</button>
                            <button type="button" class="btn btn-small btn-excluir" data-id="<?= (int) $p['id'] ?>">Excluir</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p id="tabela-vazia" class="tabela-vazia" style="<?= count($produtos) > 0 ? 'display:none' : '' ?>">Nenhum produto cadastrado.</p>
        </div>
    </div>

    <!-- Modal: formulário de cadastro/edição -->
    <div id="modal-produto" class="modal" role="dialog" aria-labelledby="modal-titulo" aria-hidden="true">
        <div class="modal-backdrop" id="modal-backdrop"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-titulo">Novo produto</h2>
                <button type="button" class="modal-close" id="modal-close" aria-label="Fechar">&times;</button>
            </div>
            <form id="form-produto" class="modal-body">
                <input type="hidden" name="id" id="produto-id" value="">
                <div class="form-group">
                    <label for="nome">Nome <span class="obrigatorio">*</span></label>
                    <input type="text" id="nome" name="nome" required maxlength="150" placeholder="Nome do produto">
                </div>
                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea id="descricao" name="descricao" rows="3" maxlength="2000" placeholder="Descrição opcional"></textarea>
                </div>
                <div class="form-group">
                    <label for="codigo_interno">Código interno</label>
                    <input type="text" id="codigo_interno" name="codigo_interno" maxlength="50" placeholder="Ex: PROD-001">
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="A">Ativo</option>
                        <option value="I">Inativo</option>
                    </select>
                </div>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btn-cancelar">Cancelar</button>
                <button type="submit" form="form-produto" class="btn btn-primary" id="btn-salvar">Salvar</button>
            </div>
        </div>
    </div>

    <!-- Modal: Fornecedores do produto (vínculos N:N) -->
    <div id="modal-fornecedores-produto" class="modal modal-vinculos" role="dialog" aria-labelledby="modal-vinculos-titulo" aria-hidden="true">
        <div class="modal-backdrop" id="modal-vinculos-backdrop"></div>
        <div class="modal-content modal-content-wide">
            <div class="modal-header">
                <h2 id="modal-vinculos-titulo">Fornecedores do produto</h2>
                <button type="button" class="modal-close" id="modal-vinculos-close" aria-label="Fechar">&times;</button>
            </div>
            <div class="modal-body modal-body-vinculos">
                <input type="hidden" id="vinculos-produto-id" value="">
                <div class="vinculos-painel">
                    <div class="vinculos-lista-section">
                        <div class="vinculos-section-header">
                            <h3>Fornecedores vinculados</h3>
                            <button type="button" class="btn btn-small btn-secondary" id="btn-remover-todos-vinculos" disabled>Remover todos</button>
                        </div>
                        <div id="vinculos-lista-loading" class="loading-inline" style="display:none;"><span class="spinner"></span> Carregando…</div>
                        <ul id="vinculos-lista" class="vinculos-lista"></ul>
                        <p id="vinculos-lista-vazia" class="vinculos-vazia">Nenhum fornecedor vinculado.</p>
                    </div>
                    <div class="vinculos-add-section">
                        <h3>Adicionar fornecedor</h3>
                        <p class="vinculos-hint">Digite para buscar fornecedores disponíveis (busca dinâmica).</p>
                        <div class="form-group">
                            <label for="vinculos-busca">Buscar fornecedor</label>
                            <input type="text" id="vinculos-busca" placeholder="Nome ou e-mail..." autocomplete="off">
                        </div>
                        <div id="vinculos-busca-loading" class="loading-inline" style="display:none;"><span class="spinner"></span> Buscando…</div>
                        <ul id="vinculos-resultados" class="vinculos-resultados"></ul>
                        <p id="vinculos-resultados-vazia" class="vinculos-vazia" style="display:none;">Nenhum resultado ou já vinculados.</p>
                    </div>
                </div>
                <div id="vinculos-mensagem" class="mensagem mensagem-inline" role="alert"></div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script>
        window.API_PRODUTO = {
            lista: '<?= $apiBase ?>&action=lista',
            buscar: '<?= $apiBase ?>&action=buscar',
            salvar: '<?= $apiBase ?>&action=salvar',
            atualizar: '<?= $apiBase ?>&action=atualizar',
            excluir: '<?= $apiBase ?>&action=excluir',
            listaFornecedores: '<?= $apiBase ?>&action=listaFornecedores',
            buscaFornecedoresParaVincular: '<?= $apiBase ?>&action=buscaFornecedoresParaVincular',
            vincularFornecedor: '<?= $apiBase ?>&action=vincularFornecedor',
            desvincularFornecedor: '<?= $apiBase ?>&action=desvincularFornecedor',
            desvincularTodosFornecedores: '<?= $apiBase ?>&action=desvincularTodosFornecedores'
        };
    </script>
    <script src="<?= $baseUrl ? htmlspecialchars($baseUrl) . '/js/produtos.js' : 'js/produtos.js' ?>"></script>
</body>
</html>
