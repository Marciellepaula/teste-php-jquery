<?php

declare(strict_types=1);

trait ValidationTrait
{
    protected function campoObrigatorioEstaVazio(array $dados, string $campo): bool
    {
        $valor = $dados[$campo] ?? '';
        $valor = is_string($valor) ? trim($valor) : $valor;
        return $valor === '' || $valor === null;
    }

    protected function validarEmail(?string $email): bool
    {
        if ($email === null || trim($email) === '') {
            return true;
        }
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
    }
}
