# teste-php-jquery

Sistema web em PHP 7.4 (sem frameworks) com MySQL, HTML, CSS e jQuery.  
Gerencia **Fornecedores**, **Produtos** e o vínculo **N:N** entre eles.

## Estrutura

- **config/** – Conexão PDO (MySQL)
- **controllers/** – Regra de negócio e orquestração
- **models/** – Acesso a dados (Prepared Statements)
- **views/** – HTML (fornecedores, etc.)
- **public/** – Front controller (`index.php`), CSS e JS
- **sql/** – Script de criação do banco
- **docs/** – [ARQUITETURA.md](docs/ARQUITETURA.md) e [MODELAGEM-BANCO.md](docs/MODELAGEM-BANCO.md)

## Banco de dados

**Opção 1 – Um único arquivo (recomendado no Windows)**  
O arquivo `sql/install.sql` cria o banco e todas as tabelas. No terminal (cmd, na pasta do projeto):

```cmd
mysql -u root -p < sql\install.sql
```

**Opção 2 – Dois comandos (Linux/WSL)**

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS teste_php_jquery CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p teste_php_jquery < sql/schema.sql
```

**Opção 3 – MySQL Workbench (Windows)**  
Abra o MySQL Workbench → conecte ao servidor → File → Open SQL Script → escolha `sql/install.sql` → execute (raio).

**Opção 4 – phpMyAdmin (XAMPP/Laragon/etc.)**  
Acesse phpMyAdmin → Aba SQL → copie e cole o conteúdo de `sql/install.sql` → Executar.

Ajuste usuário e senha em `config/database.php` (DB_USER, DB_PASS, DB_NAME).

## Como rodar

**Opção 1 – Docker Compose (MySQL + PHP 7.4)**

Na pasta do projeto:

```bash
docker compose up -d
```

Acesse: **http://localhost:8080/?controller=fornecedor&action=index**

O MySQL sobe na porta 3306 e executa `sql/install.sql` na primeira inicialização. A aplicação usa usuário `app` / senha `app` (definidos no `docker-compose.yml`). Para parar: `docker compose down`.

---

**Opção 2 – Sem Docker:** document root do servidor (Apache/Nginx) apontando para a pasta `public/`.  
Acesse: `http://localhost/?controller=fornecedor&action=index`

**Opção 3 – Servidor embutido PHP (na raiz do projeto):**

```bash
cd public && php -S localhost:8080
```

Acesse: `http://localhost:8080/?controller=fornecedor&action=index`

## CRUD de Fornecedores

- **Listar:** página inicial + API JSON `action=lista`
- **Cadastrar:** botão "Novo fornecedor" → modal → POST em `action=salvar`
- **Editar:** botão "Editar" na linha → carrega dados via `action=buscar` → POST em `action=atualizar`
- **Excluir:** botão "Excluir" → confirma → POST em `action=excluir`

Respostas AJAX em JSON; validação básica (nome obrigatório, e-mail válido); feedback visual de sucesso/erro.

## CRUD de Produtos

- **Campos:** Nome, Descrição, Código interno, Status (Ativo/Inativo).
- **URL:** `?controller=produto&action=index`
- **Listar / Cadastrar / Editar / Excluir** via AJAX (jQuery), com validação (nome obrigatório, código interno único) e feedback visual.

## Vínculo Produto × Fornecedor (N:N)

Na listagem de **Produtos**, cada linha tem o botão **"Fornecedores"**, que abre uma **modal** onde é possível:

- **Listar** fornecedores vinculados ao produto
- **Adicionar** vínculo: busca dinâmica por nome/e-mail (AJAX, debounce), depois clicar em "Adicionar"
- **Remover** vínculo individual (botão "Remover" na linha)
- **Remover todos** os vínculos do produto em massa

Interface em modal para manter o contexto na tela de produtos; busca dinâmica para boa UX com muitos fornecedores. Detalhes em [docs/VINCULOS-PRODUTO-FORNECEDOR.md](docs/VINCULOS-PRODUTO-FORNECEDOR.md).

## Documentação

- [Arquitetura e fluxo da requisição](docs/ARQUITETURA.md)
- [Modelagem do banco e exemplos de JOIN](docs/MODELAGEM-BANCO.md)
- [Vínculo produto × fornecedor e UX](docs/VINCULOS-PRODUTO-FORNECEDOR.md)
