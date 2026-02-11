<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/../core/csrf.php';

$allowedRoutes = [
    'fornecedor' => ['index', 'lista', 'buscar', 'salvar', 'atualizar', 'excluir'],
    'produto'    => [
        'index', 'lista', 'buscar', 'salvar', 'atualizar', 'excluir',
        'listaFornecedores', 'buscaFornecedoresParaVincular', 'vincularFornecedor',
        'desvincularFornecedor', 'desvincularTodosFornecedores', 'definirFornecedorPrincipal',
        'listaHistoricoVinculos',
    ],
];

$controllerName = $_GET['controller'] ?? 'fornecedor';
$actionName     = $_GET['action'] ?? 'index';
$controllerName = strtolower(trim((string) $controllerName));
$actionName     = trim((string) $actionName);

if (!isset($allowedRoutes[$controllerName]) || !in_array($actionName, $allowedRoutes[$controllerName], true)) {
    http_response_code(404);
    echo 'Rota não encontrada.';
    exit;
}

$className      = ucfirst($controllerName) . 'Controller';
$controllerFile = __DIR__ . '/../controllers/' . $className . '.php';

if (!is_file($controllerFile)) {
    http_response_code(404);
    echo 'Controller não encontrado.';
    exit;
}

require_once $controllerFile;
if (!class_exists($className)) {
    http_response_code(500);
    echo 'Classe do controller não encontrada.';
    exit;
}

$controller = new $className();
$controller->$actionName();
