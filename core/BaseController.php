<?php

declare(strict_types=1);

/**
 * Controller base para respostas JSON e renderização de views.
 * Centraliza comportamento comum e mantém os controllers enxutos e reutilizáveis.
 */
abstract class BaseController
{
    /**
     * Envia resposta JSON e encerra o fluxo de saída.
     */
    protected function json(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Renderiza uma view PHP com variáveis extraídas no escopo da view.
     *
     * @param string $viewPath Caminho absoluto ou relativo ao diretório raiz do projeto
     * @param array  $vars     Variáveis disponíveis na view (ex.: ['produtos' => $lista])
     */
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

    /**
     * Retorna um inteiro seguro a partir de GET ou POST.
     */
    protected function getInt(string $key, string $source = 'GET'): int
    {
        $source = strtoupper($source);
        $data   = $source === 'POST' ? $_POST : $_GET;
        return (int) ($data[$key] ?? 0);
    }
}
