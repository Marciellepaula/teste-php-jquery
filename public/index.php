<?php

declare(strict_types=1);

$controllerName = $_GET['controller'] ?? 'fornecedor';
$actionName     = $_GET['action'] ?? 'index';

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
