# Modelagem do Banco de Dados

## Diagrama Lógico (em texto)

- **fornecedores**  
  - Entidade que representa cada fornecedor.  
  - Campos: `id` (PK), `nome`, `cnpj`, `email`, `telefone`, `status` (ativo/inativo), `created_at`, `updated_at`.

- **produtos**  
  - Entidade que representa cada produto.  
  - Campos: `id` (PK), `nome`, `descricao`, `preco`, `status` (ativo/inativo), `created_at`, `updated_at`.

- **fornecedor_produto** (tabela de associação N:N)  
  - Um **produto** pode ter vários **fornecedores** e um **fornecedor** pode fornecer vários **produtos**.  
  - Campos: `fornecedor_id` (FK → fornecedores.id), `produto_id` (FK → produtos.id).  
  - Chave primária composta: `(fornecedor_id, produto_id)`.  
  - Índices/foreign keys garantem integridade e buscas rápidas.

**Status:** em `fornecedores` e `produtos` o campo `status` usa `'A'` = Ativo e `'I'` = Inativo (char(1)), permitindo filtros por ativo/inativo.

**Resumo das relações:**
- `fornecedores` 1 —— N `fornecedor_produto` N —— 1 `produtos`
