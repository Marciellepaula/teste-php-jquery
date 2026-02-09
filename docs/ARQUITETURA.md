# Arquitetura do Sistema

Sistema web em PHP 7.4 sem frameworks, com MySQL, HTML, CSS e jQuery.

## 1. Estrutura de Pastas

```
teste-php-jQuery/
├── config/           # Configurações (banco, constantes)
│   └── database.php
├── controllers/      # Regra de negócio e orquestração
│   └── FornecedorController.php
├── models/           # Acesso a dados (queries, PDO)
│   └── FornecedorModel.php
├── views/            # Interface (HTML + placeholders)
│   └── fornecedores/
├── public/           # Ponto de entrada e assets
│   ├── index.php     # Front controller
│   ├── css/
│   └── js/
├── sql/              # Scripts de banco de dados
│   └── schema.sql
└── docs/             # Documentação
```

## 2. Descrição das Camadas

| Camada      | Pasta        | Responsabilidade |
|------------|--------------|-------------------|
| **Config** | `config/`    | Conexão PDO, constantes, ambiente. |
| **Controller** | `controllers/` | Recebe a requisição (GET/POST), valida entrada, chama o Model, decide qual View usar e devolve resposta (HTML ou JSON). **Regra de negócio** (validações, fluxos) fica aqui. |
| **Model**  | `models/`    | **Acesso a dados**: prepared statements, CRUD no MySQL. Não contém regra de negócio complexa, apenas leitura/gravação. |
| **View**   | `views/`     | HTML com dados injetados. Sem lógica de negócio; apenas exibição. |

## 3. Fluxo de Requisição (AJAX → PHP → MySQL → Resposta)

```
[Navegador]
    │
    │  jQuery $.ajax() (GET/POST)
    ▼
[public/index.php]  ← Front controller (roteia por ?controller=X&action=Y)
    │
    │  require Controller
    ▼
[Controller]
    │  Valida parâmetros
    │  Chama Model (ex: $model->listar())
    ▼
[Model]
    │  PDO + Prepared Statements
    ▼
[MySQL]
    │  Resultado (array/boolean)
    ▼
[Controller]
    │  Formata resposta (JSON para AJAX ou HTML para página)
    ▼
[Navegador]
    │  Recebe JSON → atualiza DOM / mostra mensagem
```

- **Requisição normal (página):** `index.php?controller=fornecedor&action=index` → Controller carrega View e retorna HTML.
- **Requisição AJAX (API):** `index.php?controller=fornecedor&action=lista` (ou `salvar`, `excluir`) → Controller retorna `Content-Type: application/json` com dados ou mensagem de erro.

## 4. Segurança

- **SQL Injection:** uso exclusivo de **Prepared Statements** no Model; nenhuma concatenação de SQL com input do usuário.
- **Separação:** dados (Model) x regras e validação (Controller) x apresentação (View).

## 5. Resposta AJAX (padrão JSON)

Exemplo de retorno para o jQuery:

```json
{
  "success": true,
  "message": "Fornecedor cadastrado com sucesso.",
  "data": { "id": 1, "nome": "..." }
}
```

Em erro:

```json
{
  "success": false,
  "message": "Nome é obrigatório.",
  "errors": ["nome"]
}
```
