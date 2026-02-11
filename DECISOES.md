

##  Como modelei o banco de dados

O banco de dados foi modelado utilizando **MySQL com engine InnoDB**, seguindo princípios de **normalização até a 3ª forma normal (3FN)**, com o objetivo de evitar redundância de dados e garantir consistência das informações.

A **integridade referencial** é por meio do uso de **chaves estrangeiras (FOREIGN KEY)** com regras de `ON DELETE CASCADE` e `ON UPDATE CASCADE`, garantindo que não existam registros órfãos e que os vínculos entre produtos e fornecedores permaneçam consistentes.

A estrutura foi dividida em quatro tabelas principais:

---

### 🔹 `fornecedores`

Armazena os dados cadastrais dos fornecedores.

- Controle de status (Ativo/Inativo)
- Índices para otimização de consultas por nome e status
- Campos de auditoria (`created_at`, `updated_at`)

---

### 🔹 `produtos`

Responsável pelos dados dos produtos.

- Campo `codigo_interno` definido como **único**, evitando duplicidade
- Controle de status
- Campos de auditoria (`created_at`, `updated_at`)

---

### 🔹 `fornecedor_produto`

Tabela intermediária responsável pelo relacionamento **N:N (muitos para muitos)** entre produtos e fornecedores.

- Chave primária composta (`fornecedor_id`, `produto_id`)
- Impede vínculos duplicados
- Campo `principal` para definir o fornecedor principal do produto
- Chaves estrangeiras garantindo integridade referencial

---

### 🔹 `vinculo_historico`

Tabela criada para registrar ações realizadas nos vínculos (ex: criação e remoção), permitindo rastreabilidade simples das operações realizadas no sistema.

---

##  Por que escolhi essa estrutura

- Separa claramente as responsabilidades entre as entidades.
- Resolve corretamente o relacionamento muitos-para-muitos.
- Evita redundância de dados.
- Mantém consistência através de regras no próprio banco.
- Estrutura simples, organizada e escalável.
- Padrão que utilizo com frequência por sua robustez e clareza.

---

##  O que melhoraria se tivesse mais tempo

- Implementaria **testes automatizados** para validação das regras de negócio.
- Melhoraria a separação de responsabilidades com camadas como **Service** e **Repository**.
- Criaria sistema de **autenticação e controle de acesso**.

