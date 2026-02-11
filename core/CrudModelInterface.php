<?php

declare(strict_types=1);

interface CrudModelInterface
{
    public function listar(?string $status = null, ?string $busca = null): array;

    public function buscarPorId(int $id): ?array;

    public function excluir(int $id): bool;
}
