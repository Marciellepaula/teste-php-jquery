## Como modelei o banco de dados

Usei **MySQL (InnoDB)** e normalizei até a **3ª forma normal** para evitar repetição de dados. As tabelas se ligam por **chaves estrangeiras** com `ON DELETE CASCADE` e `ON UPDATE CASCADE`, assim nada fica órfão quando algo é apagado.

São quatro tabelas:

---

### `fornecedores`

Cadastro dos fornecedores: nome, CNPJ, email, telefone, status (Ativo/Inativo). Índices em nome e status para as buscas. Campos `created_at` e `updated_at` para auditoria.

---

### `produtos`

Cadastro dos produtos: nome, descrição, código interno (único), status. Também `created_at` e `updated_at`.

---

### `fornecedor_produto`

Tabela do meio do relacionamento **N:N** entre produto e fornecedor. Chave primária composta (`fornecedor_id`, `produto_id`) para não ter vínculo duplicado. Tem o campo `principal` para marcar o fornecedor principal do produto.

---

### `vinculo_historico`

Guarda quando um fornecedor foi vinculado ou desvinculado de um produto, para ter um histórico simples.

---

## Por que escolhi essa estrutura

Separa bem cada entidade, resolve o N:N de forma direta e evita redundância. É um desenho que eu uso bastante: simples de manter e o próprio banco garante consistência.

---

## Por que escolhi a Opção B (e um pouco da A e C)

Escolhi a **Opção B** (regra de negócio) porque deixa o sistema mais confiável: bloquear vínculo com fornecedor inativo evita incoerência; fornecedor principal e histórico ajudam o comercial a decidir com base em quem realmente fornece e no que já aconteceu. Também coloquei filtro e busca na listagem (Opção A) e organizei em MVC com um trait de validação reutilizável (Opção C).

---

## O que melhoraria com mais tempo

Testes automatizados para as regras de negócio, camadas de Service e Repository, e autenticação/controle de acesso.
