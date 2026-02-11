<?php
$fornecedores = $fornecedores ?? [];
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
$apiBase = ($baseUrl ? $baseUrl . '/' : '') . 'index.php?controller=fornecedor';
$indexUrl = ($baseUrl ? $baseUrl . '/' : '') . 'index.php';
$cssUrl = $baseUrl ? $baseUrl . '/css/style.css' : 'css/style.css';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fornecedores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars($cssUrl) ?>">
</head>
<body class="bg-light">
    <div class="container py-4">
        <ul class="nav nav-pills mb-4">
            <li class="nav-item"><a class="nav-link active" href="<?= htmlspecialchars($indexUrl) ?>?controller=fornecedor&action=index">Fornecedores</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($indexUrl) ?>?controller=produto&action=index">Produtos</a></li>
        </ul>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">Fornecedores</h1>
            <button type="button" class="btn btn-primary" id="btn-novo-fornecedor">Novo fornecedor</button>
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
                        <label for="busca-fornecedor" class="form-label mb-0 small">Busca</label>
                        <input type="text" id="busca-fornecedor" class="form-control form-control-sm" placeholder="Nome, e-mail, CNPJ ou telefone..." autocomplete="off">
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0" id="tabela-fornecedores">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>CNPJ</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fornecedores as $f): ?>
                        <tr data-id="<?= (int) $f['id'] ?>">
                            <td><?= (int) $f['id'] ?></td>
                            <td><?= htmlspecialchars($f['nome']) ?></td>
                            <td><?= htmlspecialchars($f['cnpj'] ?? '') ?></td>
                            <td><?= htmlspecialchars($f['email'] ?? '') ?></td>
                            <td><?= htmlspecialchars($f['telefone'] ?? '') ?></td>
                            <td><span class="badge bg-<?= $f['status'] === 'A' ? 'success' : 'danger' ?>"><?= $f['status'] === 'A' ? 'Ativo' : 'Inativo' ?></span></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-success btn-editar" data-id="<?= (int) $f['id'] ?>">Editar</button>
                                <button type="button" class="btn btn-sm btn-danger btn-excluir" data-id="<?= (int) $f['id'] ?>">Excluir</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p id="tabela-vazia" class="text-muted text-center py-4 mb-0" style="<?= count($fornecedores) > 0 ? 'display:none' : '' ?>">Nenhum fornecedor cadastrado.</p>
            </div>
        </div>
    </div>

    <div id="modal-fornecedor" class="modal" tabindex="-1" role="dialog" aria-labelledby="modal-titulo" aria-hidden="true">
        <div class="modal-backdrop" id="modal-backdrop"></div>
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modal-titulo" class="modal-title h5 mb-0">Novo fornecedor</h2>
                    <button type="button" class="btn-close" id="modal-close" aria-label="Fechar"></button>
                </div>
                <form id="form-fornecedor">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="fornecedor-id" value="">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" id="nome" name="nome" class="form-control" required maxlength="150" placeholder="Razão social">
                        </div>
                        <div class="mb-3">
                            <label for="cnpj" class="form-label">CNPJ <span class="text-danger">*</span></label>
                            <input type="text" id="cnpj" name="cnpj" class="form-control" required maxlength="18" placeholder="00.000.000/0000-00">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" class="form-control" required maxlength="100" placeholder="contato@exemplo.com">
                        </div>
                        <div class="mb-3">
                            <label for="telefone" class="form-label">Telefone <span class="text-danger">*</span></label>
                            <input type="text" id="telefone" name="telefone" class="form-control" required maxlength="20" placeholder="(00) 00000-0000">
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-select">
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

    <script src="<?= $baseUrl ? htmlspecialchars($baseUrl) . '/js/jquery-4.0.0.js' : 'js/jquery-4.0.0.js' ?>"></script>
    <script>
        window.API_FORNECEDOR = {
            lista: '<?= $apiBase ?>&action=lista',
            buscar: '<?= $apiBase ?>&action=buscar',
            salvar: '<?= $apiBase ?>&action=salvar',
            atualizar: '<?= $apiBase ?>&action=atualizar',
            excluir: '<?= $apiBase ?>&action=excluir'
        };
    </script>
    <script src="<?= $baseUrl ? htmlspecialchars($baseUrl) . '/js/fornecedores.js' : 'js/fornecedores.js' ?>"></script>
</body>
</html>
