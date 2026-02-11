# Teste PHP + jQuery

Sistema simples de cadastro de **fornecedores** e **produtos**, com vínculo entre eles (um produto pode ter vários fornecedores e vice-versa). Inclui histórico de vínculos e regras como: não vincular fornecedor inativo e marcar um fornecedor como principal por produto.

---

## O que tem aqui

- **Backend:** PHP 7.4 (com `strict types`), PDO e MySQL
- **Frontend:** jQuery 4.0, Bootstrap 5.3 e CSS em `public/css/style.css`
- **Banco:** MySQL 8.0 (InnoDB, utf8mb4)
- **Ambiente:** Docker — servidor PHP na porta 8080 e MySQL na 3306

---

## Estrutura do projeto

Cada pasta tem um papel claro:

| Onde | O que faz |
|------|-----------|
| `config/database.php` | Configuração do banco (variáveis de ambiente ou valores padrão) e função `getConnection()` em PDO |
| `controllers/` | **FornecedorController** e **ProdutoController** — CRUD e respostas em JSON para o front |
| `core/BaseController.php` | Métodos comuns: `json()`, `renderView()`, `getInt()` |
| `core/ValidationTrait.php` | Validação reutilizável (campos obrigatórios, e-mail, etc.) |
| `models/` | **FornecedorModel**, **ProdutoModel**, **FornecedorProdutoModel** — acesso ao banco |
| `views/fornecedores/`, `views/produtos/` | Páginas HTML, uma por módulo |
| `public/index.php` | Ponto único de entrada: `?controller=X&action=Y` chama o controller e a action |
| `public/js/` | `fornecedores.js` e `produtos.js` — chamadas AJAX e interação com modais; jQuery 4.0.0 |
| `sql/install.sql` | Criação do banco e das tabelas (fornecedores, produtos, fornecedor_produto, vinculo_historico) |
| `docker-compose.yml` e `Dockerfile` | App em PHP 7.4 + MySQL 8; ao subir, o script de instalação do banco é executado |

---

## Banco de dados

São quatro tabelas:

- **fornecedores** — nome, CNPJ, e-mail, telefone, status (Ativo/Inativo)
- **produtos** — nome, descrição, código interno (único), preço, status
- **fornecedor_produto** — liga produto e fornecedor (N:N), com campo para marcar o fornecedor principal
- **vinculo_historico** — registro de quando um fornecedor foi vinculado ou desvinculado de um produto

Para detalhes do desenho do banco e das escolhas, veja o [DECISOES.md](DECISOES.md). O script completo está em [sql/install.sql](sql/install.sql).

---

## Como rodar

### Com Docker 

Na pasta do projeto:

```bash
docker compose up -d
```

Depois acesse no navegador:

- **App:** http://localhost:8080  
- **Fornecedores:** http://localhost:8080/?controller=fornecedor&action=index  
- **Produtos:** http://localhost:8080/?controller=produto&action=index  

Para parar:

```bash
docker compose down
```

No Docker, o banco usa usuário `app`, senha `app`, nome do banco `teste_php_jquery` (MySQL root/root conforme o compose).

---

## Rotas

Tudo passa pelo [public/index.php](public/index.php):

- Formato: `index.php?controller=fornecedor|produto&action=nome_da_action`
- Páginas: `action=index` (HTML)
- API (JSON): `lista`, `salvar`, `excluir`, `buscar`; em produto também `vinculos`, `vincular`, `desvincular`, `definirPrincipal`

---

## O que o sistema faz

- **Fornecedores:** listar (com filtro por status e busca), criar, editar e excluir.
- **Produtos:** listar (filtro por status e busca), criar, editar e excluir; código interno único.
- **Vínculos (por produto):** ver fornecedores vinculados, vincular (bloqueado se o fornecedor estiver inativo), desvincular e marcar o fornecedor principal; o histórico fica na tabela `vinculo_historico`.

---

