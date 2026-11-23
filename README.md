# Beijo da Onça — e-commerce PHP e MySQL

MVP completo de loja de bebidas artesanais, cachaças, licores e kits. Projeto acadêmico demonstrativo inspirado na identidade solicitada, sem afiliação oficial. Os oito itens e seus preços reproduzem as capturas fornecidas. As fotos da vitrine são cópias locais das imagens de produto de https://beijodaonca.com/. Os estoques são dados de teste. Pagamentos são simulados e confirmados imediatamente; não há cobrança, envio de cartão, cálculo de frete ou entrega real.

## Stack e requisitos

- PHP **8.0 ou superior** com `pdo_mysql`, `session` e `password_hash`.
- MySQL 8+ ou MariaDB 10.4+, com tabelas InnoDB e utf8mb4.
- Apache/XAMPP com `AllowOverride All` se utilizar as proteções `.htaccess`.
- HTML5, CSS3, JavaScript Vanilla e Bootstrap 5.3.3 por CDN, com SRI e internet.
- Nenhum Composer, framework, ORM ou ferramenta de build.

## Estrutura

```text
beijo-da-onca/
├── .env.example
├── .gitignore
├── .htaccess
├── config/
│   ├── .htaccess
│   ├── env.php
│   └── database.php
├── includes/
│   ├── .htaccess
│   ├── bootstrap.php
│   ├── header.php
│   └── footer.php
├── database/
│   ├── .htaccess
│   └── schema.sql
├── public/
│   ├── index.php
│   ├── produto.php
│   ├── carrinho.php
│   ├── cadastro.php
│   ├── login.php
│   ├── logout.php
│   ├── checkout.php
│   ├── pedido.php
│   ├── meus-pedidos.php
│   ├── assets/
│   │   ├── style.css
│   │   ├── app.js
│   │   ├── catalogo/ (oito fotos dos produtos)
│   │   └── garrafa.svg
│   └── admin/
│       ├── index.php
│       ├── produtos.php
│       └── pedidos.php
└── README.md
```

## Instalação no XAMPP

1. Inicie Apache e MySQL no painel do XAMPP.
2. Copie `beijo-da-onca` para `C:\xampp\htdocs\beijo-da-onca` (ou a pasta htdocs do seu sistema).
3. Em `http://localhost/phpmyadmin`, importe **uma única vez** `database/schema.sql`. Ele cria `beijo_da_onca_db`, cinco tabelas com relacionamentos, dois usuários, quatro categorias e oito produtos. Não apaga dados existentes; uma reimportação encontrará tabelas existentes. Use um banco limpo para avaliação.
4. Crie um usuário MySQL exclusivo para a aplicação. Conceda somente `SELECT`, `INSERT`, `UPDATE`, `DELETE` sobre `beijo_da_onca_db.*` após a importação. Credenciais administrativas do MySQL ficam fora da aplicação.
5. Copie `.env.example` para `.env` na raiz do projeto. No Windows, habilite a visualização de extensões para não criar `.env.txt`.
6. Preencha `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`. Informe a senha real entre aspas se necessário. O parser aceita linhas `KEY=value`, aspas externas e comentários em linhas próprias; não faz expansão e não remove comentários ao final do valor. Variáveis do processo têm prioridade. Não faça commit do `.env`.
7. Mantenha `APP_BASE=/beijo-da-onca/public` nesse caminho do XAMPP e `SESSION_SECURE=false` apenas para desenvolvimento local HTTP.
8. Acesse `http://localhost/beijo-da-onca/public/index.php`.
9. Para testar com o servidor embutido: defina `APP_BASE=` e execute, a partir da raiz, `php -S 127.0.0.1:8081 -t public`. Acesse `http://127.0.0.1:8081`. O MySQL precisa continuar ativo. O servidor embutido é apenas para desenvolvimento.

### DocumentRoot recomendado

Em hospedagem Apache, configure o DocumentRoot para a pasta **public** e `APP_BASE=` (ou o prefixo real de subpasta). Dessa forma, `.env`, SQL e configurações ficam fora da raiz pública. Os `.htaccess` incluídos são defesa adicional para o cenário XAMPP em htdocs. Não use o projeto inteiro como raiz em servidores que ignoram `.htaccess`. Configure HTTPS e `SESSION_SECURE=true` em produção, e mantenha erros de PHP apenas no log protegido.

## Credenciais iniciais

| Perfil        | E-mail                | Senha       |
| ------------- | --------------------- | ----------- |
| Administrador | admin@beijodaonca.com | Admin@123   |
| Cliente       | cliente@example.com   | Cliente@123 |

As duas senhas estão no seed como hashes Bcrypt válidos, gerados com custo 12. Troque/remova essas contas antes de expor a aplicação publicamente; o seed é exclusivo para avaliação. Novos cadastros sempre recebem perfil `cliente`, decidido no servidor. O menu Administração só aparece para administradores; todas as rotas em `public/admin` também verificam o perfil no servidor.

## Fluxos e segurança

- Vitrine com produtos ativos, filtro de categoria, busca por nome/descrição e detalhes com disponibilidade. A Tábua começa sem estoque para testar o bloqueio.
- Carrinho em `$_SESSION`: adição cumulativa, atualização validada contra estoque, remoção, limpeza e totais calculados no servidor. Itens inativos ou com estoque alterado são indicados e impedidos no checkout.
- Cadastro e login usam Bcrypt e `password_verify`. Regeneração do ID da sessão ao entrar/sair, cookie HttpOnly/SameSite e proteção CSRF em todas as ações POST. Logout exclusivamente por POST. Login tem limite básico de tentativas por sessão; um ambiente público deve usar limitação por conta/IP na infraestrutura.
- RBAC consulta o perfil atual no banco e exige usuário autenticado para checkout e pedidos pessoais. O cliente só consulta pedidos próprios.
- Todas as consultas da aplicação usam `prepare` + `execute`, com parâmetros vinculados, inclusive leituras sem filtros. A criação de tabelas e o seed são executados separadamente na instalação.
- Checkout usa `beginTransaction`, bloqueia produtos por `SELECT ... FOR UPDATE` em ordem de ID, valida status/estoque/preço, cria pedido e itens, decrementa estoque e confirma com `commit`. Qualquer falha desfaz o conjunto com `rollBack`. O carrinho é limpo somente após o commit.
- Token único de checkout e bloqueio da sessão PHP evitam repetição de pedido no reenvio comum. Não é integração com um gateway nem simulação de liquidação bancária.
- Preços são `DECIMAL(10,2)` no banco e centavos inteiros nos cálculos PHP. Preço e nome ficam congelados nos itens do pedido. Produtos ligados a pedidos são desativados em vez de apagados para preservar FKs/histórico.
- Escape HTML em saída, URLs de imagem validadas (HTTPS ou assets/), CSP e cabeçalhos básicos. Sem upload de arquivos. Aviso +18 permanente, declaração no cadastro e no checkout.

## Roteiro de avaliação

1. Explore a vitrine em celular e desktop. Busque “amburana”, combine uma categoria e busque texto inexistente.
2. Abra um produto disponível; adicione duas unidades e atualize no carrinho. Tente enviar quantidade maior que o estoque e confirme a recusa no servidor.
3. Remova um item e limpe a sacola. Abra o produto com estoque zero e confira ausência do botão de compra.
4. Sem login, tente abrir checkout e `/admin/index.php`; espere redirecionamento ao login.
5. Cadastre uma conta, teste e-mail repetido e login inválido. Entre com cliente e confirme HTTP 403 no painel administrativo.
6. Com produtos na sacola e login de cliente, confirme uma compra com PIX. Confira pedido, itens, preço histórico, baixa de estoque e sacola vazia.
7. Repita com Boleto e Cartão de Crédito: todos são simulações sem dados bancários.
8. Atualize a confirmação ou repita o POST: não deve gerar uma segunda compra.
9. Entre como admin. Cadastre produto, altere preço/estoque/categoria/status e confira a vitrine. Exclua produto sem histórico; tente excluir produto comprado e confira desativação.
10. Consulte todos os pedidos em Administração → Pedidos e abra os detalhes.
11. Em dois navegadores/sessões diferentes, dispute o último item de um produto; apenas um checkout deve confirmar.
12. Simule uma alteração de estoque entre a montagem da sacola e a confirmação: o pedido deve falhar integralmente e preservar o carrinho.

## Modelo de dados

`usuarios 1:N pedidos`; `categorias 1:N produtos`; `pedidos N:N produtos` resolvido por `itens_pedido`. A tabela associativa guarda quantidade, nome e preço unitário da compra. Os índices atendem categorias, status/nome, cliente/data e itens por produto. As restrições de banco complementam a validação PHP.

## Roteiro sugerido de commits

1. `chore: estrutura PHP e ambiente sem credenciais`
2. `feat: schema normalizado e seed demonstrativo`
3. `feat: vitrine busca e detalhes de produtos`
4. `feat: carrinho em sessão com validação de estoque`
5. `feat: cadastro login e controle de perfis`
6. `feat: checkout transacional e histórico de pedidos`
7. `feat: administração de produtos e pedidos`
8. `style: identidade responsiva Beijo da Onça`
9. `docs: instalação XAMPP e roteiro de avaliação`
10. `test: registrar validações de segurança e compra`

Antes de cada commit, revise `git status` e confirme que `.env` está ignorado. Para verificar sintaxe: `php -l caminho/do/arquivo.php`. Faça backup do banco antes de migrações. A passagem para operação comercial exige gateway real, política de entrega, recuperação de conta, controles de acesso operacionais e revisão jurídica da venda de bebidas; esses serviços não fazem parte do MVP solicitado.

## Entrega da atividade

Submeta o link do repositório GitHub ou GitLab contendo o histórico de desenvolvimento. O ZIP é uma cópia pessoal. A atividade não o aceita como entrega oficial do e-commerce.
