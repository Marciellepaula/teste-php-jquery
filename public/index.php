<?php
/**
 * Front Controller: roteia requisições por controller e action.
 * Ex.: index.php?controller=fornecedor&action=index
 *      index.php?controller=fornecedor&action=lista (AJAX)
 */

declare(strict_types=1);

$controllerName = $_GET['controller'] ?? 'fornecedor';
$actionName     = $_GET['action'] ?? 'index';

// Normaliza: "fornecedor" -> "FornecedorController"
$className = ucfirst(strtolower($controllerName)) . 'Controller';
$action    = strtolower($actionName);

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
if (!method_exists($controller, $action)) {
    http_response_code(404);
    echo 'Action não encontrada.';
    exit;
}

$controller->$action();
