# teste-php-jquery

PHP 7.4 + MySQL + jQuery. Fornecedores, Produtos e vínculo N:N.

## Como rodar

### 1. Com Docker (recomendado)

Na pasta do projeto:

```bash
docker compose up -d
```

Acesse: http://localhost:8080/?controller=fornecedor&action=index  
Produtos: http://localhost:8080/?controller=produto&action=index  
Parar: `docker compose down`

### 2. Sem Docker (local)

**Requisitos:** PHP 7.4 (com PDO MySQL) e MySQL.

1. Crie o banco e as tabelas (no terminal, na pasta do projeto):
   - Windows (cmd): `mysql -u root -p < sql\install.sql`
   - Linux/WSL: `mysql -u root -p < sql/install.sql`
2. Ajuste `config/database.php` se precisar: `DB_USER`, `DB_PASS`, `DB_NAME`.
3. Suba o servidor PHP: `cd public && php -S localhost:8080`
4. Acesse: http://localhost:8080/?controller=fornecedor&action=index

Documento de decisões técnicas e modelagem: [DECISOES.md](DECISOES.md).
