# Cozinha Guiada

Aplicação web em PHP para cadastro/login de usuários e consulta/cadastro de receitas. O escopo implementado é uma versão mínima funcional; funcionalidades descritas em documentos antigos que não estejam presentes na interface não são consideradas entregues.

## Rodar localmente

Requisitos: PHP 8.2+ com `pdo_sqlite` e `mbstring` habilitados. Não há dependências Composer.

```bash
git clone https://github.com/Caju12/Cozinha-Guiada.git
cd Cozinha-Guiada
cp .env.example .env
php -S 127.0.0.1:8000 -t public
```

No PowerShell, use `Copy-Item .env.example .env`. Abra http://127.0.0.1:8000. Com SQLite o banco e as categorias de exemplo são criados automaticamente em `storage/cozinha_guiada.sqlite`.

Para usar MySQL/MariaDB, habilite `pdo_mysql`, ajuste `DB_DRIVER=mysql` e as variáveis `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` no `.env`, depois importe `database/schema.sql`. O banco deve estar criado e acessível antes de abrir o app.

## Publicação

Há um `Dockerfile` e `render.yaml` para preparar um deploy Docker no Render. Configure um disco persistente para `/var/www/html/storage` (necessário para conservar o SQLite) e `APP_ENV=production`. O Render cria o endereço público ao finalizar o deploy; esta configuração não publica automaticamente sem uma conta/projeto vinculado no Render. O plano/infraestrutura de disco pode ter custo. Para persistência em outros provedores, use MySQL gerenciado e configure as variáveis correspondentes.

## Arquivos relevantes

- `spec.md`: especificação e passos de instalação
- `database/schema.sql`: schema MySQL/MariaDB
- `.env.example`: nomes e valores de exemplo, sem credenciais reais
- `CHANGELOG.md`: decisões e alterações

Nunca publique o arquivo `.env` nem credenciais reais.
