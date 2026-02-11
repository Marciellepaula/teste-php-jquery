<?php
$produtos = $produtos ?? [];
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
$apiBase = ($baseUrl ? $baseUrl . '/' : '') . 'index.php?controller=produto';
$indexUrl = ($baseUrl ? $baseUrl . '/' : '') . 'index.php';
$cssUrl = $baseUrl ? $baseUrl . '/css/style.css' : 'css/style.css';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token ?? '') ?>">
    <title>Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars($cssUrl) ?>">
</head>
<body class="bg-light">
    <div class="container py-4">
        <ul class="nav nav-pills mb-4">
            <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($indexUrl) ?>?controller=fornecedor&action=index">Fornecedores</a></li>
            <li class="nav-item"><a class="nav-link active" href="<?= htmlspecialchars($indexUrl) ?>?controller=produto&action=index">Produtos</a></li>
        </ul>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">Produtos</h1>
            <button type="button" class="btn btn-primary" id="btn-novo-produto">Novo produto</button>
        </div>

        <div id="mensagem" class="alert d-none" role="alert"></div>

        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        <label for="filtro-status" class="form-label mb-0 small">Status</label>
                        <select id="filtro-status" class="form-select form-select-sm" style="width:auto">
                            <option value="">Todos</option>
                            <option value="A">Ativo</option>
                            <option value="I">Inativo</option>
                        </select>
                    </div>
                    <div class="col-auto flex-grow-1">
                        <label for="busca-produto" class="form-label mb-0 small">Busca</label>
                        <input type="text" id="busca-produto" class="form-control form-control-sm" placeholder="Nome, código interno ou descrição..." autocomplete="off">
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0" id="tabela-produtos">
                    <thead class="table-light">
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
                            <td class="text-truncate" style="max-width:200px"><?= htmlspecialchars(mb_substr($p['descricao'] ?? '', 0, 60)) ?><?= mb_strlen($p['descricao'] ?? '') > 60 ? '…' : '' ?></td>
                            <td><?= htmlspecialchars($p['codigo_interno'] ?? '') ?></td>
                            <td><span class="badge bg-<?= $p['status'] === 'A' ? 'success' : 'danger' ?>"><?= $p['status'] === 'A' ? 'Ativo' : 'Inativo' ?></span></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary btn-fornecedores" data-id="<?= (int) $p['id'] ?>" data-nome="<?= htmlspecialchars($p['nome']) ?>">Fornecedores</button>
                                <button type="button" class="btn btn-sm btn-success btn-editar" data-id="<?= (int) $p['id'] ?>">Editar</button>
                                <button type="button" class="btn btn-sm btn-danger btn-excluir" data-id="<?= (int) $p['id'] ?>">Excluir</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p id="tabela-vazia" class="text-muted text-center py-4 mb-0" style="<?= count($produtos) > 0 ? 'display:none' : '' ?>">Nenhum produto cadastrado.</p>
            </div>
        </div>
    </div>

    <div id="modal-produto" class="modal" tabindex="-1" role="dialog" aria-labelledby="modal-titulo" aria-hidden="true">
        <div class="modal-backdrop" id="modal-backdrop"></div>
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modal-titulo" class="modal-title h5 mb-0">Novo produto</h2>
                    <button type="button" class="btn-close" id="modal-close" aria-label="Fechar"></button>
                </div>
                <form id="form-produto">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="produto-id" value="">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" id="nome" name="nome" class="form-control" required maxlength="150" placeholder="Nome do produto">
                        </div>
                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição <span class="text-danger">*</span></label>
                            <textarea id="descricao" name="descricao" class="form-control" rows="3" required maxlength="2000" placeholder="Descrição do produto"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="codigo_interno" class="form-label">Código interno <span class="text-danger">*</span></label>
                            <input type="text" id="codigo_interno" name="codigo_interno" class="form-control" required maxlength="50" placeholder="Ex: PROD-001">
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select id="status" name="status" class="form-select" required>
                                <option value="A">Ativo</option>
                                <option value="I">Inativo</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="btn-cancelar">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btn-salvar">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modal-fornecedores-produto" class="modal" tabindex="-1" role="dialog" aria-labelledby="modal-vinculos-titulo" aria-hidden="true">
        <div class="modal-backdrop" id="modal-vinculos-backdrop"></div>
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modal-vinculos-titulo" class="modal-title h5 mb-0">Fornecedores do produto</h2>
                    <button type="button" class="btn-close" id="modal-vinculos-close" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="vinculos-produto-id" value="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h3 class="h6 mb-0">Fornecedores vinculados</h3>
                                        <button type="button" class="btn btn-sm btn-secondary" id="btn-remover-todos-vinculos" disabled>Remover todos</button>
                                    </div>
                                    <div id="vinculos-lista-loading" class="small text-muted" style="display:none;"><span class="spinner"></span> Carregando…</div>
                                    <ul id="vinculos-lista" class="list-group list-group-flush" style="max-height:200px;overflow-y:auto"></ul>
                                    <p id="vinculos-lista-vazia" class="small text-muted mb-0 mt-2">Nenhum fornecedor vinculado.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h3 class="h6 mb-2">Adicionar fornecedor</h3>
                                    <p class="small text-muted mb-2">Busca por nome ou e-mail (apenas ativos e ainda não vinculados).</p>
                                    <input type="text" id="vinculos-busca" class="form-control form-control-sm mb-2" placeholder="Nome ou e-mail..." autocomplete="off">
                                    <div id="vinculos-busca-loading" class="small text-muted" style="display:none;"><span class="spinner"></span> Buscando…</div>
                                    <ul id="vinculos-resultados" class="list-group list-group-flush" style="max-height:160px;overflow-y:auto"></ul>
                                    <p id="vinculos-resultados-vazia" class="small text-muted mb-0 mt-2" style="display:none;">Nenhum resultado ou já vinculados.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="vinculos-mensagem" class="alert mt-3 d-none" role="alert"></div>
                    <div class="card mt-3">
                        <div class="card-body">
                            <h3 class="h6 mb-2">Histórico de vínculos</h3>
                            <div id="vinculos-historico-loading" class="small text-muted" style="display:none;"><span class="spinner"></span> Carregando…</div>
                            <ul id="vinculos-historico-lista" class="list-group list-group-flush small" style="max-height:140px;overflow-y:auto"></ul>
                            <p id="vinculos-historico-vazia" class="small text-muted mb-0 mt-2">Nenhum registro no histórico. Vincule ou desvincule fornecedores para gerar registros (é necessário rodar a migration do banco: sql/migration_opcao_b.sql).</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= $baseUrl ? htmlspecialchars($baseUrl) . '/js/jquery-4.0.0.js' : 'js/jquery-4.0.0.js' ?>"></script>
    <script>
        window.CSRF_TOKEN = '<?= htmlspecialchars($csrf_token ?? '') ?>';
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
            desvincularTodosFornecedores: '<?= $apiBase ?>&action=desvincularTodosFornecedores',
            definirFornecedorPrincipal: '<?= $apiBase ?>&action=definirFornecedorPrincipal',
            listaHistoricoVinculos: '<?= $apiBase ?>&action=listaHistoricoVinculos'
        };
    </script>
    <script src="<?= $baseUrl ? htmlspecialchars($baseUrl) . '/js/produtos.js' : 'js/produtos.js' ?>"></script>
</body>
</html>
