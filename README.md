# Cozinha Guiada

Aplicação web simples para cadastro de usuários, autenticação e listagem de receitas culinárias, com banco de dados configurável para SQLite ou MySQL.

## Requisitos

- PHP 8.2+
- Extensão PDO
- SQLite para rodar sem instalar banco extra, ou MySQL/MariaDB para ambiente profissional

## Instalação rápida

```bash
git clone https://github.com/Caju12/Cozinha-Guiada.git
cd Cozinha-Guiada
cp .env.example .env
php -S localhost:8000 -t public
```

Abra: http://localhost:8000

## Configuração do banco

### SQLite (padrão)

O projeto pode rodar com SQLite sem instalar um servidor de banco. O arquivo do banco será criado automaticamente em `storage/cozinha_guiada.sqlite`.

### MySQL/MariaDB

1. Crie o banco `cozinha_guiada`.
2. Ajuste as variáveis em `.env`.
3. Importe o script:

```bash
mysql -u root -p < database/schema.sql
```

## Usuário de teste

Crie uma conta na interface inicial ou use o cadastro da aplicação. A autenticação usa `password_hash` e consultas com prepared statements.

## Estrutura

- `public/` — interface web
- `src/` — bootstrap e conexão com banco
- `database/` — schema SQL
- `spec.md` — especificação final do projeto
- `CHANGELOG.md` — histórico de mudanças

## Segurança

- Senhas armazenadas com `password_hash`
- Consultas parametrizadas com PDO
- Variáveis de ambiente em `.env` e `.env.example`
