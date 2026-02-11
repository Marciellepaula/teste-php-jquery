- Como você modelou o banco de dados (Inclua a modelagem de Banco de Dados)
O banco foi modelado seguindo princípios de normalização até a 3ª forma normal, evitando redundância de dados. A integridade referencial é garantida através do uso de chaves estrangeiras com regras de exclusão em cascata assegurando consistência entre produtos, fornecedores e seus vínculos.
O banco foi modelado utilizando MySQL com engine InnoDB, seguindo princípios de normalização até a 3ª forma normal (3FN), com o objetivo de evitar redundância de dados e garantir consistência nas informações.

A estrutura foi dividida em quatro tabelas principais:
* fornecedores
Armazena os dados cadastrais dos fornecedores.
Possui controle de status (Ativo/Inativo) e índices para melhorar performance em consultas por nome e status.

*  produtos
Responsável pelos dados dos produtos.
O campo codigo_interno é único para evitar duplicidade.
Inclui status e campos de auditoria (created_at, updated_at).

* fornecedor_produto
Tabela intermediária responsável pelo relacionamento N:N (muitos para muitos) entre produtos e fornecedores.
Utiliza chave primária composta (fornecedor_id, produto_id) para impedir vínculos duplicados.
Possui campo principal para definir fornecedor principal do produto

* vinculo_historico
Tabela criada para registrar ações realizadas nos vínculos (ex: criação e remoção), permitindo rastreabilidade simples.
- Por que escolheu essa estrutura
Separa claramente responsabilidades entre entidades. É uma estrutura que uso bastante nos meus projetos 
- O que melhoraria se tivesse mais tempo
Testes automatizados para regras de negócio. Camadas mais bem definidas (Service e Repository). Autenticação e controle de acesso.

