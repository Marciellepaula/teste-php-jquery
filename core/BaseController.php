<?php

declare(strict_types=1);


abstract class BaseController
{

    protected function json(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    protected function renderView(string $viewPath, array $vars = []): void
    {
        $base = dirname(__DIR__);
        $path = strpos($viewPath, $base) === 0 ? $viewPath : $base . '/' . ltrim($viewPath, '/');
        if (!is_file($path)) {
            echo '<p>View não encontrada.</p>';
            return;
        }
        extract($vars, EXTR_SKIP);
        require $path;
    }

    protected function getInt(string $key, string $source = 'GET'): int
    {
        $source = strtoupper($source);
        $data   = $source === 'POST' ? $_POST : $_GET;
        return (int) ($data[$key] ?? 0);
    }
}
