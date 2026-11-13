# Especificação Final — Cozinha Guiada

## Objetivo e escopo entregue

Aplicação web em PHP para pessoas iniciantes na cozinha. A versão presente neste repositório permite criar conta, entrar/sair, consultar receitas cadastradas e, autenticado, cadastrar receitas com categoria, tempo e dificuldade. Não afirmar como implementadas funcionalidades não listadas aqui; a especificação extensa anterior descreve propostas, não o escopo que foi possível confirmar no código atual.

## Usuários e casos de uso

- Visitante: cadastro e consulta do catálogo de receitas.
- Usuário cadastrado: login/logout e cadastro de receitas.

Fluxo principal: criar conta → entrar → cadastrar receita → conferir a receita na listagem → sair.

## Requisitos funcionais

1. Cadastro com validação básica e senha armazenada com `password_hash`.
2. Login usando `password_verify` e sessão.
3. Logout com remoção da sessão.
4. Listagem pública das receitas.
5. Cadastro de receita para usuário autenticado.
6. Categorias iniciais exibidas no formulário.

## Requisitos não funcionais e segurança

- PHP 8.2+, PDO, prepared statements e escaping HTML.
- Token CSRF nos formulários POST.
- Cookie de sessão `HttpOnly`, `SameSite=Lax`, `Secure` quando servido em HTTPS; ID de sessão regenerado após login.
- `.env` é local e ignorado pelo Git; `.env.example` contém apenas exemplos.
- Esta revisão de código não substitui uma auditoria de segurança completa nem validação dinâmica.

## Stack e ambiente

- PHP 8.2 ou superior; extensões necessárias: `pdo_sqlite` e `mbstring` no modo padrão, ou `pdo_mysql` no modo MySQL.
- Servidor local: servidor embutido do PHP.
- Sem dependências Composer/npm.
- O ambiente operacional não foi executado por esta ferramenta. Os comandos abaixo destinam-se a Linux/macOS; para Windows, há alternativa para copiar o `.env`.

## Banco e tabelas

Padrão local: SQLite 3, arquivo `storage/cozinha_guiada.sqlite`, criado automaticamente na primeira requisição. As tabelas SQLite são criadas pela aplicação. Banco alternativo: MySQL 8/MariaDB 10.6+, criado/importado com `database/schema.sql`.

| Tabela | Campos principais | Chave primária | Relacionamentos |
|---|---|---|---|
| `users` | id, nome, email, senha_hash, perfil, created_at | id | — |
| `categories` | id, nome | id | — |
| `recipes` | id, user_id, titulo, descricao, categoria_id, tempo_preparo, dificuldade, created_at | id | user_id → users.id; categoria_id → categories.id (opcional) |

O script SQL e o bootstrap SQLite inserem as categorias Café da Manhã, Almoço, Sobremesas, Saladas e Massas. Não há usuário ou senha de teste predefinidos: cadastre um usuário pela tela inicial.

## Variáveis de ambiente

Copie `.env.example` para `.env` e ajuste conforme necessário:

| Variável | Uso |
|---|---|
| `APP_ENV` | ambiente; use `local` localmente e `production` na hospedagem |
| `DB_DRIVER` | `sqlite` (padrão) ou `mysql` |
| `DB_NAME` | caminho do arquivo SQLite relativo à raiz do projeto, ou nome do banco MySQL |
| `DB_HOST`, `DB_PORT` | host e porta MySQL; ignorados em SQLite |
| `DB_USER`, `DB_PASS` | usuário e senha do banco MySQL; nunca use credenciais reais em commits |
| `APP_URL` | URL base informativa do app |
| `SESSION_NAME` | nome do cookie de sessão |

## Instalação e execução

1. Instale PHP 8.2+ e habilite `pdo_sqlite` e `mbstring` (`pdo_mysql` também se for escolher MySQL). Confirme com `php -v` e `php -m`.
2. Clone: `git clone https://github.com/Caju12/Cozinha-Guiada.git`
3. Entre na pasta: `cd Cozinha-Guiada`.
4. Copie a configuração: Linux/macOS `cp .env.example .env`; PowerShell `Copy-Item .env.example .env`.
5. Para SQLite, mantenha `DB_DRIVER=sqlite` e `DB_NAME=storage/cozinha_guiada.sqlite`; não é necessário importar SQL.
6. Para MySQL/MariaDB, crie o banco `cozinha_guiada`, configure `DB_DRIVER=mysql` e os valores `DB_*` no `.env`, então execute `mysql -u root -p < database/schema.sql` (ou importe pelo cliente equivalente).
7. Inicie o servidor na raiz do projeto: `php -S 127.0.0.1:8000 -t public`.
8. Abra http://127.0.0.1:8000.

## Como testar

1. Na página inicial, cadastre uma conta fictícia com senha de teste (mínimo 8 caracteres).
2. Entre com a conta criada.
3. Cadastre uma receita com título, descrição, categoria, tempo e dificuldade.
4. Confirme que a receita aparece no catálogo após o envio.
5. Saia e entre novamente para verificar a sessão e a persistência.

## Limitações conhecidas

- A aplicação ainda não implementa busca, ingredientes estruturados, favoritos, moderação ou painel administrativo.
- Para MySQL, importar o schema é obrigatório; a aplicação não executa migrações MySQL.
- SQLite em hospedagem precisa de armazenamento persistente; sem isso os dados podem desaparecer após reinício/redeploy.
- O código foi revisado estaticamente, mas não foi possível executar testes PHP ou confirmar um deploy por esta sessão.
- A URL pública ainda depende de criar/vincular um serviço no provedor de hospedagem.
