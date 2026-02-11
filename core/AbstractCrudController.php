<?php

declare(strict_types=1);

abstract class AbstractCrudController extends BaseController
{
    abstract protected function getModel(): CrudModelInterface;

    abstract protected function getEntityName(): string;

    public function lista(): void
    {
        $status = $this->getStatusFromGet();
        $busca  = $this->getBuscaFromGet();
        $data   = $this->getModel()->listar($status, $busca);
        $this->json(['success' => true, 'data' => $data]);
    }

    public function buscar(): void
    {
        $id = $this->getInt('id', 'GET');
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID inválido.']);
            return;
        }
        $item = $this->getModel()->buscarPorId($id);
        if (!$item) {
            $entity = $this->getEntityName();
            $this->json(['success' => false, 'message' => $entity . ' não encontrado.']);
            return;
        }
        $this->json(['success' => true, 'data' => $item]);
    }

    public function excluir(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido.']);
            return;
        }
        $this->requireCsrf();
        $id = $this->getInt('id', 'POST');
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID inválido.']);
            return;
        }
        try {
            $ok = $this->getModel()->excluir($id);
            if (!$ok) {
                $entity = $this->getEntityName();
                $this->json(['success' => false, 'message' => $entity . ' não encontrado.']);
                return;
            }
            $this->json(['success' => true, 'message' => $this->getEntityName() . ' excluído com sucesso.']);
        } catch (\Throwable $e) {
            $this->handleException($e, 'Erro ao excluir.');
        }
    }
}
