# Changelog — Cozinha Guiada

Este documento registra o histórico de versões, as principais decisões arquiteturais e, fundamentalmente, o **registro de revisão humana** sobre o código e as propostas geradas pelos assistentes de Inteligência Artificial durante o desenvolvimento da plataforma Cozinha Guiada.

---

## [2.1.0] — 2026-11-18 (Homologação Final e Correção de Nuvem)

### Adicionado
- Endpoint de diagnóstico de infraestrutura em `/health` e `/api/health` retornando status de conexão, driver PDO e contagem de registros em JSON.
- Caixa técnica de diagnóstico no Front Controller (`public/index.php`) ativada sob `APP_DEBUG=true`.
- Suporte ao **Supabase Connection Pooler** (IPv4, porta 6543) com tratamento seguro de senhas codificadas via `urldecode()`.
- Criação de `.gitignore` abrangente na raiz do repositório para evitar vazamento de credenciais locais.

### Corrigido
- Falha na conexão de banco no Vercel: a classe `Database` não lia as variáveis de ambiente de produção (`DATABASE_URL` / `POSTGRES_URL`), tentando conectar localmente em `127.0.0.1:5432`.
- Resolução de SSL dinâmico para conexões remotas (`sslmode=require`) versus conexões de desenvolvimento local (`sslmode=disable`).

---

## [2.0.0] — 2026-11-13 (Consolidação da Documentação e Etapas 1 a 8)

### Adicionado
- Especificação técnica consolidada em `spec.md` e `especificacao_tecnica_final_cozinha_guiada.md`.
- Suíte completa com 8 baterias de testes automatizados (`tests/run_all_tests.php`).
- Configuração de container Docker FrankenPHP para publicação na nuvem (`Dockerfile.vercel` e `Caddyfile`).
- Modo de Preparo Guiado com Screen Wake Lock API e Web Audio API nativo.
- Sistema de substituições inteligentes de ingredientes e construtor dinâmico de receitas para produtores.

---

## 📋 Registro Formal de Revisão Humana de Propostas de IA

Em cumprimento aos critérios de avaliação da disciplina ("Programar com IA de forma responsável"), a tabela abaixo documenta momentos críticos em que as propostas geradas pela IA foram avaliadas criticamente pela equipe humana, resultando em aceitação com ajustes ou rejeição fundamentada:

| Data | Proposta / Trecho Proposto pela IA | Decisão Humana | Justificativa Técnica / Pedagógica | Responsável |
| :--- | :--- | :---: | :--- | :--- |
| **10/10/2026** | **Stack em Next.js / TypeScript com Prisma e SQLite:** O agente sugeriu refatorar o projeto para uma stack moderna baseada em Node.js/React. | **Rejeitada** | O escopo do projeto acadêmico exige estritamente **PHP 8.2 e arquitetura MVC relacional**, garantindo que o professor consiga clonar e avaliar o sistema no XAMPP padrão sem dependências complexas de build. | André Luiz Marques |
| **16/10/2026** | **Filtro de Dietas com SQL `IN` simples (Lógica "OU"):** O agente propôs filtrar receitas com `WHERE rc.compatibilidade_id IN (...)`. | **Alterada** | Um usuário com múltiplas restrições (ex: Celíaco **E** Intolerante a Lactose) não pode receber pratos com glúten. A lógica foi alterada para agregação estrita: `HAVING COUNT(DISTINCT rc.compatibilidade_id) = :total_filtros` (relação lógica "E" / AND). | André Luiz Marques |
| **24/10/2026** | **Efeitos Sonoros com Arquivos de Áudio Externos (`.mp3`):** O agente propôs carregar arquivos MP3 de CDN externa para o bipe do temporizador. | **Alterada** | Se a conexão falhar ou houver bloqueio de CORS/MIME, o alarme de cozinha falha silenciosamente. A solução foi migrada para a **Web Audio API** nativa (`AudioContext`), gerando ondas senoidais diretamente no hardware do navegador. | André Luiz Marques |
| **28/10/2026** | **Gestão de Usuários sem Prevenção de Lockout:** O endpoint de administração permitia alterar status de qualquer usuário para inativo ou trocar perfil. | **Alterada** | Havia o risco crítico de o último administrador desativar a própria conta ou rebaixar seu perfil, travando o sistema. Foi adicionada validação no servidor que impede a auto-desativação/rebaixamento do último admin ativo. | André Luiz Marques |
| **05/11/2026** | **Inclusão de Tags Técnicas na Interface do Usuário:** O agente inseriu identificadores como "(RF33)", "(RF17)", "(RNF06)" nos títulos de páginas e formulários. | **Alterada** | Requisitos funcionais são metadados de especificação técnica e não devem ser exibidos aos usuários reais do sistema. Todos os rótulos foram limpos para proporcionar uma experiência profissional e amigável. | André Luiz Marques |
| **17/11/2026** | **Conexão Hardcoded na Migração PostgreSQL/Supabase:** Na conteinerização para o Vercel, o agente fixou `127.0.0.1:5432` no `Database.php`. | **Alterada** | O ambiente serverless do Vercel não possui PostgreSQL local e exige conexão remota via **Connection Pooler do Supabase** (porta 6543, IPv4). A classe foi reescrita com suporte a `DATABASE_URL` e criação de rota de diagnóstico `/health`. | André Luiz Marques |

---

## 🔒 Checklist de Segurança e Boas Práticas

- [x] Nenhuma senha real ou chave secreta exposta em repositório público ou privado.
- [x] O arquivo `.env` é estritamente ignorado; apenas `.env.example` é rastreado no git.
- [x] Senhas armazenadas com hash criptográfico irreversível `BCRYPT` (`password_hash`).
- [x] Todas as entradas em queries SQL passam por Prepared Statements no PDO.
- [x] Saídas HTML tratadas com `htmlspecialchars()` contra Cross-Site Scripting (XSS).
- [x] Controle de acesso baseado em papéis (RBAC) validado em todas as rotas restritas.
