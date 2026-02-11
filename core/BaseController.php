<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';

abstract class BaseController
{
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
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
        $vars['csrf_token'] = $vars['csrf_token'] ?? getCsrfToken();
        extract($vars, EXTR_SKIP);
        require $path;
    }

    protected function getInt(string $key, string $source = 'GET'): int
    {
        $source = strtoupper($source);
        $data   = $source === 'POST' ? $_POST : $_GET;
        return (int) ($data[$key] ?? 0);
    }

    protected function validateCsrf(): bool
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        return $token !== '' && hash_equals($sessionToken, $token);
    }

    protected function requireCsrf(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Token de segurança inválido. Atualize a página.'], 403);
            exit;
        }
    }

    protected function handleException(\Throwable $e, string $genericMessage): void
    {
        if (getenv('APP_ENV') === 'production') {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            $this->json(['success' => false, 'message' => $genericMessage]);
        } else {
            $this->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
        }
    }
}
