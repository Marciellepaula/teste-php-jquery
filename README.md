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

