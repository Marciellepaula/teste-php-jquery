<?php
$fornecedores = $fornecedores ?? [];
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
$apiBase = ($baseUrl ? $baseUrl . '/' : '') . 'index.php?controller=fornecedor';
$indexUrl = ($baseUrl ? $baseUrl . '/' : '') . 'index.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fornecedores</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ? htmlspecialchars($baseUrl) . '/css/style.css' : 'css/style.css' ?>">
</head>
<body>
    <div class="container">
        <nav class="page-nav">
            <a href="<?= htmlspecialchars($indexUrl) ?>?controller=fornecedor&action=index" class="active">Fornecedores</a>
            <a href="<?= htmlspecialchars($indexUrl) ?>?controller=produto&action=index">Produtos</a>
        </nav>
        <header class="page-header">
            <h1>Fornecedores</h1>
            <button type="button" class="btn btn-primary" id="btn-novo-fornecedor">Novo fornecedor</button>
        </header>

        <div id="mensagem" class="mensagem" role="alert" aria-live="polite"></div>

        <div class="card">
            <table class="tabela" id="tabela-fornecedores">
                <thead>
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
                        <td><span class="badge badge-<?= $f['status'] === 'A' ? 'ativo' : 'inativo' ?>"><?= $f['status'] === 'A' ? 'Ativo' : 'Inativo' ?></span></td>
                        <td>
                            <button type="button" class="btn btn-small btn-editar" data-id="<?= (int) $f['id'] ?>">Editar</button>
                            <button type="button" class="btn btn-small btn-excluir" data-id="<?= (int) $f['id'] ?>">Excluir</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p id="tabela-vazia" class="tabela-vazia" style="<?= count($fornecedores) > 0 ? 'display:none' : '' ?>">Nenhum fornecedor cadastrado.</p>
        </div>
    </div>

    <div id="modal-fornecedor" class="modal" role="dialog" aria-labelledby="modal-titulo" aria-hidden="true">
        <div class="modal-backdrop" id="modal-backdrop"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-titulo">Novo fornecedor</h2>
                <button type="button" class="modal-close" id="modal-close" aria-label="Fechar">&times;</button>
            </div>
            <form id="form-fornecedor" class="modal-body">
                <input type="hidden" name="id" id="fornecedor-id" value="">
                <div class="form-group">
                    <label for="nome">Nome <span class="obrigatorio">*</span></label>
                    <input type="text" id="nome" name="nome" required maxlength="150" placeholder="Razão social">
                </div>
                <div class="form-group">
                    <label for="cnpj">CNPJ</label>
                    <input type="text" id="cnpj" name="cnpj" maxlength="18" placeholder="00.000.000/0000-00">
                </div>
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" maxlength="100" placeholder="contato@exemplo.com">
                </div>
                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="text" id="telefone" name="telefone" maxlength="20" placeholder="(00) 00000-0000">
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
                <button type="submit" form="form-fornecedor" class="btn btn-primary" id="btn-salvar">Salvar</button>
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
