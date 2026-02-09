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

1. Crie o banco e importe o schema:

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS teste_php_jquery CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p teste_php_jquery < sql/schema.sql
```

2. Ajuste usuário e senha em `config/database.php` (DB_USER, DB_PASS, DB_NAME).

## Como rodar

- **Opção A:** Document root do servidor (Apache/Nginx) apontando para a pasta `public/`.  
  Acesse: `http://localhost/?controller=fornecedor&action=index`

- **Opção B:** Servidor embutido PHP (na raiz do projeto):

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

## Documentação

- [Arquitetura e fluxo da requisição](docs/ARQUITETURA.md)
- [Modelagem do banco e exemplos de JOIN](docs/MODELAGEM-BANCO.md)
