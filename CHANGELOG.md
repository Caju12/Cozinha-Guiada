# Changelog

## 2026-11-13 — Correção após inspeção

- Corrigida a organização dos arquivos PHP: bootstrap, conexão de banco e front controller estão em seus caminhos correspondentes.
- Implementada inicialização SQLite com criação automática das tabelas e categorias; schema MySQL/MariaDB documentado em `database/schema.sql`.
- Ajustados cadastro/login/logout, validação básica, prepared statements, escaping, token CSRF e propriedades de cookie de sessão.
- Atualizada a especificação para refletir o escopo efetivamente presente, sem declarar como concluídas as funcionalidades que não estão no código.
- Adicionada preparação Docker/Render para facilitar publicação; o deploy e teste em execução ainda dependem de conta/projeto no provedor.

### Revisão humana e ressalvas

A leitura do commit anterior encontrou arquivos trocados de caminho (o front controller tinha sido salvo como `src/bootstrap.php`, e o bootstrap como `src/Database.php`), além de inexistência do diretório `public/`. A instalação documentada também mencionava SQLite, mas o código não criava as tabelas SQLite. Esta revisão corrige esses problemas. A validação foi estática; não afirmar que os testes ou deploy passaram sem executá-los.
