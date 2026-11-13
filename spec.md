# Especificação Final — Cozinha Guiada

## 1. Objetivo

O sistema Cozinha Guiada é uma aplicação web para auxiliar pessoas com pouca experiência culinária a encontrar receitas acessíveis, com instruções claras, dicas de preparo e fluxo simples de cadastro.

## 2. Usuários

- Visitante: pode consultar receitas públicas.
- Usuário cadastrado: registra-se e faz login.
- Administrador: pode gerenciar categorias e visualizar conteúdo no painel administrativo.

## 3. Casos de uso

- Cadastro de usuário
- Login e logout
- Consulta de receitas
- Visualização de detalhes
- Criação de nova receita por usuário autenticado
- Gerenciamento de categorias

## 4. Requisitos funcionais

- RF01: cadastrar usuário
- RF02: autenticar usuário
- RF03: listar receitas
- RF04: ver detalhes da receita
- RF05: criar receita
- RF06: listar categorias
- RF07: logout

## 5. Decisão de stack

- Linguagem: PHP 8.2
- Frontend: HTML, CSS, PHP
- Banco: SQLite por padrão para execução local simples; MySQL/MariaDB para ambiente de produção
- Servidor local: `php -S localhost:8000 -t public`

## 6. Modelo de dados

### Tabela: users

| Campo | Tipo | Observação |
|---|---|---|
| id | INT PK | chave primária |
| nome | VARCHAR(120) | nome do usuário |
| email | VARCHAR(180) | único |
| senha_hash | VARCHAR(255) | hash bcrypt |
| perfil | ENUM | `usuario` ou `admin` |
| created_at | TIMESTAMP | data do cadastro |

### Tabela: categories

| Campo | Tipo | Observação |
|---|---|---|
| id | INT PK | chave primária |
| nome | VARCHAR(80) | nome da categoria |

### Tabela: recipes

| Campo | Tipo | Observação |
|---|---|---|
| id | INT PK | chave primária |
| user_id | INT FK | referência para users |
| titulo | VARCHAR(150) | título da receita |
| descricao | TEXT | resumo |
| categoria_id | INT FK | referência para categories |
| tempo_preparo | INT | em minutos |
| dificuldade | VARCHAR(20) | facil/medio/dificil |
| imagem_url | VARCHAR(255) | opcional |
| created_at | TIMESTAMP | data da criação |

### Tabela: ingredientes

| Campo | Tipo | Observação |
|---|---|---|
| id | INT PK | chave primária |
| recipe_id | INT FK | referência para recipes |
| nome | VARCHAR(120) | nome do ingrediente |
| quantidade | VARCHAR(50) | quantidade ou unidade |

### Tabela: favoritos

| Campo | Tipo | Observação |
|---|---|---|
| id | INT PK | chave primária |
| user_id | INT FK | referência para users |
| recipe_id | INT FK | referência para recipes |

## 7. Requisitos não funcionais

- Interface simples e responsiva
- Validação dos campos no backend
- Hash de senha seguro
- Consultas com prepared statements
- Uso de variáveis de ambiente para configuração

## 8. Instalação e execução

### Ambiente

- PHP 8.2
- SQLite 3 (padrão) ou MySQL 8 / MariaDB 10.6+
- Sistema operacional testado: Linux / Windows / macOS

### Dependências

Não há dependências externas para execução local básica. O projeto usa apenas a extensão PDO do PHP.

### Banco de dados

- SGBD: SQLite (padrão) ou MySQL/MariaDB
- Banco: `cozinha_guiada`
- Script SQL: `database/schema.sql`

Importação MySQL:

```bash
mysql -u root -p < database/schema.sql
```

### Variáveis de ambiente

O repositório contém o arquivo `.env.example` com exemplo de configuração. Copie para `.env` e ajuste os valores.

```env
APP_ENV=local
DB_DRIVER=sqlite
DB_HOST=localhost
DB_PORT=3306
DB_NAME=cozinha_guiada
DB_USER=root
DB_PASS=
APP_URL=http://localhost:8000
```

### Passo a passo de instalação

1. Clone o repositório: `git clone https://github.com/Caju12/Cozinha-Guiada.git`
2. Acesse o diretório: `cd Cozinha-Guiada`
3. Copie o exemplo de ambiente: `cp .env.example .env`
4. Ajuste as variáveis de ambiente conforme o banco local
5. Se for usar MySQL, importe `database/schema.sql`
6. Inicie o servidor: `php -S localhost:8000 -t public`
7. Abra `http://localhost:8000`

## 9. Como testar

1. Cadastre um usuário novo.
2. Faça login com o novo usuário.
3. Veja a lista de receitas na home.
4. Cadastre uma nova receita no painel autenticado.
5. Confirme que os dados ficam persistidos no banco.

## 10. Problemas conhecidos

- A implantação pública em produção depende de um serviço externo (Render, Railway, Vercel ou ambiente web) e não foi configurada neste repositório.
- O professor deve adicionar o usuário `rafaelpitwak` como colaborador no GitHub manualmente.

## 11. Critérios de aceite

- A aplicação roda localmente seguindo a documentação acima
- O cadastro e login funcionam
- O banco salva receitas e usuários
- Não há credenciais em código-fonte
- O sistema usa variáveis de ambiente para configuração
