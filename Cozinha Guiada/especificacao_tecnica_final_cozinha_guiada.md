# 🍳 Especificação Técnica e Funcional Final — Cozinha Guiada

> **Documento Consolidado de Requisitos, Arquitetura, Critérios de Aceite e Guia de Instalação**  
> **Disciplina / Projeto:** Programar com IA de Forma Responsável  
> **Autor / Aluno:** André Luiz Marques (`Caju12`)  
> **Professor Colaborador:** Rafael Pitwak (`rafaelpitwak`)  
> **Repositório:** [https://github.com/Caju12/Cozinha-Guiada](https://github.com/Caju12/Cozinha-Guiada)  
> **Versão:** 2.1 (Final Consolidada e Homologada) — Novembro de 2026  

---

## 📌 Sumário Executivo

Este documento consolida a especificação do sistema **Cozinha Guiada**, abrangendo seus objetivos, perfis de usuários (RBAC), requisitos funcionais (RF01 a RF53), requisitos não-funcionais (RNF01 a RNF19), critérios de aceite (CA01 a CA53), modelo relacional normalizado (19 tabelas), histórico detalhado de decisões e revisões humanas ao longo do desenvolvimento, e o guia completo de instalação passo a passo para execução local e em produção.

---

## 1. Objetivos do Sistema

* **O que é e para quem:** O *Cozinha Guiada* é uma plataforma web inclusiva de culinária projetada especialmente para auxiliar pessoas iniciantes na cozinha, idosos e pessoas com restrições alimentares severas (intolerâncias, alergias ou dietas éticas).
* **O que resolve:** O sistema elimina a insegurança do cozinheiro iniciante ao disponibilizar fichas técnicas completas com parâmetros práticos frequentemente omitidos por receitas tradicionais: temperatura exata do forno, intensidade da chama do fogão, utensílios necessários e ponto esperado do prato (textura, cor e consistência).
* **Benefício principal:** Conta com um **Modo Guiado** imersivo e acessível com controle de tela ativa (**Screen Wake Lock API**), temporizador culinário sonoro nativo (**Web Audio API**), pesquisa por ingredientes da despensa (redução de desperdício) e filtros estritos de dietas combinadas (relação lógica "E" / AND).

---

## 2. Perfis de Usuários (RBAC)

A plataforma adota Controle de Acesso Baseado em Papéis (*Role-Based Access Control*):

1. **Usuário Não Cadastrado (Visitante):**
   * Acesso público ao catálogo de receitas ativas.
   * Pesquisa textual livre, busca por despensa e filtros combinados de categorias e dietas inclusivas.
   * Visualização da ficha técnica completa, utensílios, alternativas de substituição e execução do Modo Guiado.
   * Não pode favoritar, avaliar, comentar ou publicar receitas.

2. **Usuário Cadastrado (Membro):**
   * Todas as permissões de visitante.
   * Gerenciamento de perfil (dados pessoais, biografia e preferências alimentares salvas).
   * Adicionar e remover receitas da lista de Favoritos.
   * Registro e limpeza de histórico de navegação.
   * Avaliação de receitas (notas de 1 a 5 estrelas).
   * Envio, edição e exclusão de comentários próprios.
   * Envio de denúncias contra conteúdos ou comentários inadequados.
   * Solicitação imediata para se tornar Produtor de Conteúdo.

3. **Produtor de Conteúdo (Chef):**
   * Todas as permissões de usuário cadastrado.
   * Acesso ao Painel do Produtor (`/produtor/painel`).
   * Construtor dinâmico de receitas com adição fluida de ingredientes, unidades, medidas, substituições inteligentes, utensílios e passos ordenados.
   * Upload de fotografias reais do prato (JPG, PNG, WEBP até 5 MB).
   * Controle de visibilidade da receita (Pública ou Privada/Rascunho).
   * Edição completa e exclusão definitiva de receitas de sua autoria.
   * Perfil público com biografia e portfólio de receitas publicadas.

4. **Administrador:**
   * Todas as permissões anteriores.
   * Painel Administrativo Geral (`/admin`) com métricas consolidadas em tempo real.
   * Gestão de usuários: ativação/bloqueio de contas e alteração de papéis RBAC com **prevenção contra auto-bloqueio (*lockout*)** do último administrador.
   * Fila de moderação de denúncias com remoção de conteúdo infrator ou descarte justificado.
   * Gestão completa (CRUD) de categorias culinárias oficiais.

---

## 3. Matriz de Casos de Uso Principais

| Perfil | Casos de Uso Abrangidos | Requisitos Funcionais |
| :--- | :--- | :---: |
| **Visitante** | Consultar catálogo; pesquisar por título; filtrar por categoria e dietas; buscar por despensa; visualizar ficha técnica, utensílios e substituições; executar Modo Guiado; cadastrar-se; autenticar-se. | RF01 a RF03, RF08 a RF19 |
| **Usuário Cadastrado** | Ações de visitante; editar perfil e bio; selecionar preferências alimentares; gerenciar favoritos; consultar e limpar histórico; avaliar receitas (1-5); comentar receitas (criar/editar/excluir); denunciar conteúdo; tornar-se produtor. | RF04 a RF07, RF20 a RF30 |
| **Produtor** | Ações de cadastrado; painel de publicações; criar, editar e excluir receitas; cadastrar ingredientes, unidades e substituições; ordenar etapas; informar parâmetros culinários (fogo, temperatura, ponto); upload de fotos; alternar visibilidade pública/privada. | RF31 a RF45, RF53 |
| **Administrador** | Ações gerais; dashboard de métricas; listar, bloquear e reativar usuários; alterar papéis RBAC; fila de moderação de denúncias; remoção de conteúdos infratores; CRUD de categorias culinárias. | RF46 a RF52 |

---

## 4. Requisitos Funcionais (RF01 a RF53)

### Acesso, Cadastro e Perfil
* **RF01:** Permitir acesso público e visualização de receitas públicas ativas.
* **RF02:** Permitir pesquisa de receitas por termos livres no título e descrição.
* **RF03:** Permitir aplicação de filtros de categoria culinária.
* **RF04:** Permitir criação de nova conta de usuário comum com perfil `cadastrado`.
* **RF05:** Permitir autenticação de usuários cadastrados mediante e-mail e senha.
* **RF06:** Permitir gerenciamento e atualização de dados de perfil e biografia.
* **RF07:** Permitir configuração de preferências alimentares (Vegetariana, Vegana, Sem lactose, Sem glúten).

### Pesquisa e Descoberta de Receitas
* **RF08:** Permitir pesquisa de receitas baseada nos ingredientes disponíveis na despensa.
* **RF09:** Permitir combinação simultânea de diferentes critérios de busca (texto, categoria e restrições).
* **RF10:** Permitir filtro por compatibilidades alimentares inclusivas.
* **RF11:** Apresentar receitas que cumpram **todos** os filtros alimentares selecionados (relação lógica "E" / AND estrita).
* **RF12:** Apresentar ficha detalhada da receita (ingredientes, quantidades, preparo e compatibilidades).
* **RF13:** Apresentar opções cadastradas de substituição de ingredientes com orientações e proporção.

### Modo de Preparo
* **RF14:** Apresentar ingredientes, quantidades e unidades de medida de forma clara e padronizada.
* **RF15:** Apresentar etapas de preparo ordenadas sequencialmente.
* **RF16:** Apresentar tempo estimado de preparo e lista de utensílios necessários.
* **RF17:** Apresentar informações adicionais de cocção: temperatura de forno, chama do fogão e ponto visual esperado.
* **RF18:** Disponibilizar Modo de Preparo Guiado passo a passo, imersivo e sem distrações.
* **RF19:** Manter a tela do dispositivo ativa durante todo o Modo Guiado via Screen Wake Lock API.

### Favoritos, Histórico e Interações Sociais
* **RF20:** Permitir adicionar receitas aos favoritos pessoais.
* **RF21:** Permitir remover receitas dos favoritos.
* **RF22:** Disponibilizar página dedicada para consulta de receitas favoritas salvas.
* **RF23:** Registrar histórico de receitas visualizadas por usuários cadastrados.
* **RF24:** Permitir consulta e limpeza completa do histórico de navegação.
* **RF25:** Permitir publicação de comentários em receitas ativas.
* **RF26:** Permitir edição de comentários pelo próprio autor.
* **RF27:** Permitir exclusão de comentários pelo próprio autor.
* **RF28:** Permitir atribuição de notas de 1 a 5 estrelas em receitas, calculando a média pública.
* **RF29:** Permitir denúncia fundamentada de receitas ou comentários que infrinjam diretrizes.

### Produção de Conteúdo
* **RF30:** Permitir que usuários cadastrados solicitem transição imediata para perfil de produtor.
* **RF31:** Permitir que produtores criem novas receitas através de construtor dinâmico.
* **RF32:** Permitir inserção dinâmica de ingredientes, quantidades e unidades de medida.
* **RF33:** Permitir inclusão e reordenação de passos sequenciais do modo de preparo.
* **RF34:** Permitir preenchimento de parâmetros culinários (tempo, utensílios, temperatura, chama e ponto).
* **RF35:** Permitir upload de imagens reais da receita (JPG, PNG, WEBP até 5 MB).
* **RF36:** Permitir personalização da apresentação da receita (título, descrição e imagem principal).
* **RF37:** Disponibilizar painel exclusivo de gestão das receitas do próprio produtor.
* **RF38:** Permitir edição de receitas de sua autoria.
* **RF39:** Permitir exclusão definitiva de receitas de sua autoria com integridade referencial.
* **RF40:** Permitir alternar a visibilidade da receita entre Pública e Privada (rascunho).
* **RF41:** Permitir seleção de compatibilidades alimentares atendidas pela receita.
* **RF42:** Permitir alteração das compatibilidades de receitas de sua autoria.
* **RF43:** Permitir cadastro de alternativas de substituição para ingredientes da receita.
* **RF44:** Permitir definição de proporção culinária e orientações nas substituições.
* **RF45:** Permitir remoção de alternativas de substituição cadastradas.

### Administração e Moderação
* **RF46:** Permitir visualização e alteração do status de usuários (ativo / bloqueado).
* **RF47:** Permitir gerenciamento dos papéis de acesso dos usuários (promoção/rebaixamento RBAC).
* **RF48:** Apresentar dashboard com métricas consolidadas em tempo real (usuários, receitas, avaliações e moderação).
* **RF49:** Permitir moderação de receitas e comentários reportados.
* **RF50:** Permitir remoção de conteúdos infratores por administradores.
* **RF51:** Disponibilizar CRUD completo de categorias de receitas.
* **RF52:** Disponibilizar fila de análise de denúncias (pendente, analisada, descartada, removida).
* **RF53:** Permitir que o público acesse o perfil público e portfólio de receitas de produtores.

---

## 5. Requisitos Não-Funcionais (RNF01 a RNF19)

* **RNF01:** Interface simples, limpa e de fácil compreensão visual.
* **RNF02:** Navegação intuitiva e consistente entre todas as páginas e rotas.
* **RNF03:** Responsividade completa para smartphones, tablets e desktops.
* **RNF04:** Tempo de resposta do catálogo e buscas inferior a 3 segundos.
* **RNF05:** Validação rigorosa de todos os dados de formulário no lado servidor antes da persistência.
* **RNF06:** Criptografia irreversível de senhas com algoritmo BCRYPT (`password_hash`), nunca em texto puro.
* **RNF07:** Sessões seguras com cookies marcados com `HttpOnly`, `SameSite=Lax` e destruição completa no logout.
* **RNF08:** Verificação estrita de autorização RBAC no servidor via `AuthMiddleware`.
* **RNF09:** Verificação estrita de propriedade de recursos (HTTP 403 para tentativa de edição de conteúdo alheio).
* **RNF10:** Encerramento seguro de sessão e expiração de dados temporários no logout.
* **RNF11:** Prevenção ativa contra SQL Injection (Prepared Statements PDO sem emulação) e XSS (`htmlspecialchars`).
* **RNF12:** Não exibição de detalhes técnicos, credenciais ou rastros de banco em ambiente de produção.
* **RNF13:** Proteção de dados cadastrais e garantia de unicidade de e-mail.
* **RNF14:** Integridade referencial garantida pelo SGBD com constraints e `ON DELETE CASCADE`.
* **RNF15:** Acessibilidade visual com alto contraste, suporte a foco por teclado (`:focus-visible`) e botões amplos.
* **RNF16:** Tratamento gracioso de erros com páginas HTTP 404 e 500 padronizadas.
* **RNF17:** Notificações visuais imediatas (*flash messages*) de sucesso, erro ou alerta após qualquer operação.
* **RNF18:** Arquitetura modular padrão MVC em PHP para alta manutenibilidade.
* **RNF19:** Extensibilidade de compatibilidades alimentares e categorias via banco sem alteração no código.

---

## 6. Mudanças e Decisões Técnicas Adotadas no Desenvolvimento

Durante o desenvolvimento interativo do projeto com apoio de IA, diversas decisões técnicas fundamentais foram tomadas e revisadas humanamente:

### 6.1. Resolução do Conflito de Stack Tecnológico
* **Proposta Inicial da IA:** Sugestão de migração para Next.js / TypeScript com Prisma ORM e SQLite/Vercel Serverless.
* **Revisão e Decisão Humana:** Rejeitada a proposta para manter total fidelidade ao escopo acadêmico: **PHP 8.2 puro em arquitetura MVC**, com PDO nativo, garantindo que o professor e colegas consigam rodar localmente no XAMPP ou servidor embutido do PHP sem dependência de ferramentas pesadas.

### 6.2. Algoritmo SQL Estrito para Compatibilidades Alimentares (RF11)
* **Proposta Inicial da IA:** Filtro com operador `IN` simples gerando comportamento lógico "OU" (retornando receitas que atendiam apenas a uma das restrições marcadas).
* **Revisão e Decisão Humana:** Implementada agregação SQL estrita com contagem distintiva (`HAVING COUNT(DISTINCT rc.compatibilidade_id) = :total_selecionado`), garantindo a relação lógica "E" (AND). Uma pessoa alérgica a glúten e intolerante a lactose só vê receitas que cumprem ambas as condições simultaneamente.

### 6.3. Prevenção Contra Auto-bloqueio (*Lockout*) do Administrador
* **Proposta Inicial da IA:** O endpoint administrativo de gestão de usuários permitia desativar ou rebaixar qualquer usuário da lista.
* **Revisão e Decisão Humana:** Adicionada regra de negócio rígida em `AdminController`: o sistema identifica se o usuário alvo é o último administrador ativo e rejeita a alteração caso ele tente se desativar ou rebaixar seu papel, impedindo a perda definitiva de acesso administrativo.

### 6.4. Screen Wake Lock com Reaquisição Automática e Web Audio API
* **Proposta Inicial da IA:** Utilização de arquivos `.mp3` hospedados externamente para o bipe do cronômetro culinário.
* **Revisão e Decisão Humana:** Substituída a dependência de arquivos externos pela **Web Audio API** (`AudioContext`), sintetizando beeps sonoros nativos em frequência de 880 Hz diretamente pelo navegador. Além disso, adicionado listener no evento `visibilitychange` para readquirir a trava de tela (*Wake Lock*) automaticamente caso o usuário alterne de aba e retorne.

### 6.5. Remoção de Metadados Técnicos da Interface Gráfica
* **Proposta Inicial da IA:** Inclusão de rótulos como "(RF33)", "(RF17)", "(RNF06)" nos títulos de seções das páginas públicas.
* **Revisão e Decisão Humana:** Removidas todas as referências internas aos requisitos na camada de visão ([`views/`](Cozinha%20Guiada/views/)), tornando a interface 100% amigável, limpa e profissional para os usuários finais.

### 6.6. Suporte Dual de Banco: MySQL Local (XAMPP) e PostgreSQL Nuvem (Vercel/Supabase)
* **Decisão Adotada:** A classe `Database` foi aprimorada para detectar dinamicamente se a conexão provém de uma `DATABASE_URL` do Supabase/PostgreSQL ou das variáveis locais do MySQL/MariaDB (`DB_HOST`, `DB_PORT=3306`).
* **Connection Pooler do Supabase (Porta 6543):** Identificado que conexões diretas do Supabase usam IPv6 e são bloqueadas em provedores serverless como a Vercel; a conexão foi configurada para utilizar o pooler do Supabase (IPv4 na porta 6543) com usuário no formato `postgres.[PROJECT-REF]`.
* **Endpoint de Diagnóstico `/health`:** Criada rota que retorna o status de conexão com o banco e o total de receitas cadastradas em JSON, permitindo verificar a integridade da publicação em segundos.

---

## 7. Instalação e Execução a Partir de um Clone Limpo

Esta seção atende integralmente ao roteiro exigido para que qualquer avaliador consiga clonar e rodar o projeto localmente ou em produção sem dúvidas.

### 7.1. Ambiente e Pré-requisitos
* **Linguagem:** PHP 8.2 (compatível com PHP 8.1+).
* **Extensões PHP obrigatórias:** `pdo_pgsql` (PostgreSQL), `pdo_mysql` (MySQL/MariaDB), `fileinfo` (validação segura de uploads de imagem) e `mbstring`.
* **Dependências de terceiros:** Nenhuma. O projeto utiliza **PHP Vanilla puro** (não requer `composer install`) e estilos utilitários via Tailwind CSS carregados por CDN (não requer `npm install`).
* **Sistemas operacionais testados e validados:** Windows 10/11 (ambiente de desenvolvimento e XAMPP) e Linux Alpine (container Docker FrankenPHP no Vercel).

---

### 7.2. Banco de Dados: Tabela das 19 Entidades

Abaixo está a relação completa de todas as 19 tabelas criadas pelo script `database/schema.sql`:

| Tabela | Campos principais | Chave primária | Relacionamentos |
| :--- | :--- | :---: | :--- |
| `usuarios` | `id`, `nome`, `email`, `senha`, `perfil`, `bio`, `ativo` | `id` | — |
| `compatibilidades_alimentares` | `id`, `nome`, `slug`, `descricao` | `id` | — |
| `usuario_preferencias` | `usuario_id`, `compatibilidade_id` | `(usuario_id, compatibilidade_id)` | `usuario_id` → `usuarios.id` (CASCADE)<br>`compatibilidade_id` → `compatibilidades_alimentares.id` (CASCADE) |
| `categorias` | `id`, `nome`, `slug`, `descricao` | `id` | — |
| `receitas` | `id`, `usuario_id`, `titulo`, `descricao`, `tempo_preparo_minutos`, `rendimento`, `temperatura`, `intensidade_fogo`, `ponto_esperado`, `visibilidade`, `status_moderacao` | `id` | `usuario_id` → `usuarios.id` (CASCADE) |
| `etapas_receita` | `id`, `receita_id`, `ordem`, `descricao` | `id` | `receita_id` → `receitas.id` (CASCADE) |
| `imagens_receita` | `id`, `receita_id`, `caminho`, `ordem` | `id` | `receita_id` → `receitas.id` (CASCADE) |
| `ingredientes` | `id`, `nome` | `id` | — |
| `receita_ingredientes` | `id`, `receita_id`, `ingrediente_id`, `quantidade`, `unidade`, `observacao` | `id` | `receita_id` → `receitas.id` (CASCADE)<br>`ingrediente_id` → `ingredientes.id` (CASCADE) |
| `substituicoes_ingrediente` | `id`, `receita_ingrediente_id`, `ingrediente_substituto_id`, `texto_substituto`, `proporcao`, `observacao` | `id` | `receita_ingrediente_id` → `receita_ingredientes.id` (CASCADE)<br>`ingrediente_substituto_id` → `ingredientes.id` (SET NULL) |
| `utensilios` | `id`, `nome` | `id` | — |
| `receita_utensilios` | `receita_id`, `utensilio_id` | `(receita_id, utensilio_id)` | `receita_id` → `receitas.id` (CASCADE)<br>`utensilio_id` → `utensilios.id` (CASCADE) |
| `receita_compatibilidades` | `receita_id`, `compatibilidade_id` | `(receita_id, compatibilidade_id)` | `receita_id` → `receitas.id` (CASCADE)<br>`compatibilidade_id` → `compatibilidades_alimentares.id` (CASCADE) |
| `receita_categorias` | `receita_id`, `categoria_id` | `(receita_id, categoria_id)` | `receita_id` → `receitas.id` (CASCADE)<br>`categoria_id` → `categorias.id` (CASCADE) |
| `favoritos` | `usuario_id`, `receita_id` | `(usuario_id, receita_id)` | `usuario_id` → `usuarios.id` (CASCADE)<br>`receita_id` → `receitas.id` (CASCADE) |
| `historico_receitas` | `id`, `usuario_id`, `receita_id`, `visualizado_em` | `id` | `usuario_id` → `usuarios.id` (CASCADE)<br>`receita_id` → `receitas.id` (CASCADE) |
| `comentarios` | `id`, `usuario_id`, `receita_id`, `texto`, `status` | `id` | `usuario_id` → `usuarios.id` (CASCADE)<br>`receita_id` → `receitas.id` (CASCADE) |
| `avaliacoes` | `usuario_id`, `receita_id`, `nota` | `(usuario_id, receita_id)` | `usuario_id` → `usuarios.id` (CASCADE)<br>`receita_id` → `receitas.id` (CASCADE) |
| `denuncias` | `id`, `usuario_id`, `tipo_alvo`, `alvo_id`, `motivo`, `status`, `resolvido_por`, `resolvido_em` | `id` | `usuario_id` → `usuarios.id` (CASCADE)<br>`resolvido_por` → `usuarios.id` (SET NULL) |

---

### 7.3. Variáveis de Ambiente (`.env.example`)

O repositório inclui o modelo de configuração no arquivo `.env.example`. Para executar, crie uma cópia com o nome `.env`.

| Variável | Descrição / Finalidade | Exemplo Local (XAMPP) | Exemplo Nuvem (Vercel / Supabase) |
| :--- | :--- | :--- | :--- |
| `DATABASE_URL` | URL de conexão PostgreSQL do Supabase Pooler | *(Opcional no MySQL)* | `postgresql://postgres.[REF]:[SENHA]@aws-0-[REGIAO].pooler.supabase.com:6543/postgres` |
| `DB_HOST` | Host do banco de dados | `127.0.0.1` ou `localhost` | `aws-0-[REGIAO].pooler.supabase.com` |
| `DB_PORT` | Porta de rede do banco | `3306` (MySQL) | `6543` (Pooler Supabase) ou `5432` |
| `DB_NAME` | Nome do banco de dados | `cozinha_guiada` | `postgres` |
| `DB_USER` | Usuário do banco de dados | `root` | `postgres.[PROJECT-REF]` |
| `DB_PASS` | Senha de acesso do banco | *(vazio no XAMPP)* | *SuaSenhaDoSupabase* |
| `DB_SSLMODE` | Modo de criptografia SSL | `disable` | `require` |
| `APP_DEBUG` | Exibe detalhes técnicos em caso de erro | `true` | `false` (ou `true` durante homologação) |
| `APP_ENV` | Identificador do ambiente | `development` | `production` |
| `UPLOAD_MAX_SIZE` | Limite máximo para upload de fotos (bytes) | `5242880` (5 MB) | `5242880` (5 MB) |
| `ALLOWED_IMAGE_TYPES` | Formatos MIME autorizados no upload | `image/jpeg,image/png,image/webp` | `image/jpeg,image/png,image/webp` |

---

### 7.4. Passo a Passo de Instalação e Execução Local

Siga a lista numerada abaixo a partir de um terminal:

1. **Clonar o repositório:**
   ```bash
   git clone https://github.com/Caju12/Cozinha-Guiada.git
   cd Cozinha-Guiada
   cd "Cozinha Guiada"
   ```

2. **Criar o arquivo de variáveis de ambiente:**
   ```bash
   cp .env.example .env
   # No Windows PowerShell: Copy-Item .env.example .env
   ```

3. **Criar o banco de dados e importar os dados iniciais:**
   * **Via XAMPP / MySQL:**
     1. Inicie o Apache e o MySQL no painel do XAMPP.
     2. Abra o terminal ou phpMyAdmin e execute:
        ```sql
        CREATE DATABASE cozinha_guiada CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        ```
     3. Importe o esquema e as sementes:
        ```bash
        mysql -u root -p cozinha_guiada < database/schema.sql
        mysql -u root -p cozinha_guiada < database/seed.sql
        ```
   * **Ou via script automatizado PHP:**
     ```bash
     php database/setup.php
     ```

4. **Iniciar o servidor de desenvolvimento:**
   Execute o servidor embutido do PHP apontando para o DocumentRoot `public/`:
   ```bash
   php -S 127.0.0.1:8000 -t public public/index.php
   ```

5. **Acessar a aplicação no navegador:**
   Abra: **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

### 7.5. Como Testar: Contas Padrão e Fluxo de Homologação

O arquivo `database/seed.sql` cadastra três usuários fictícios de teste. Todos utilizam a senha: **`Admin@2026`**

| Perfil | E-mail de Teste | O que testar com esse perfil |
| :--- | :--- | :--- |
| **Administrador** | `admin@cozinhaguiada.com` | Acesso ao Painel Admin (`/admin`), bloqueio/desbloqueio de usuários, fila de moderação de denúncias e CRUD de categorias. |
| **Produtor (Chef)** | `chef@cozinhaguiada.com` | Acesso ao Painel do Produtor (`/produtor/painel`), criação de receita com upload de foto, adição de ingredientes, etapas e substituições. |
| **Membro Cadastrado** | `leitor@cozinhaguiada.com` | Favoritar receitas, consultar histórico com limpeza, avaliar com 5 estrelas, postar e editar comentários, e enviar denúncias. |

#### Roteiro do Fluxo Principal (Demonstração Completa):
1. **Visitante:** Abra a home, filtre pela dieta *Vegetariana* **E** *Sem Glúten*, realize uma busca na despensa por "banana" e clique em "Modo Guiado" na receita de Panqueca de Banana para testar o temporizador sonoro.
2. **Cadastro e Login:** Clique em "Cadastre-se", crie uma conta com seu nome, entre no sistema e acerte suas preferências alimentares em "Meu Perfil".
3. **Interação Social:** Abra o Bolo de Cenoura, adicione aos Favoritos (ícone de coração), dê uma nota 5 e comente.
4. **Área do Produtor:** Clique em "Tornar-se Produtor" e acesse o painel para cadastrar um novo prato.
5. **Moderação Admin:** Saia da conta e faça login com `admin@cozinhaguiada.com` para conferir o painel com as métricas em tempo real e a fila de denúncias.

---

## 8. Checklist de Segurança da Apostila (Anexo B)

* [x] **Credenciais Fora do Repositório:** O arquivo `.env` está explicitamente ignorado pelo `.gitignore` na raiz e dentro da pasta do projeto. O repositório contém apenas `.env.example` com valores fictícios.
* [x] **Hash Seguro de Senhas:** Todas as senhas são cifradas via `password_hash($senha, PASSWORD_BCRYPT)`. Nunca são armazenadas ou trafegadas em texto plano (RNF06).
* [x] **Consultas Parametrizadas (Anti-SQLi):** 100% das consultas SQL utilizam `PDO::prepare` com marcadores nomeados ou posicionais e emulação desativada (`PDO::ATTR_EMULATE_PREPARES => false`).
* [x] **Proteção Anti-XSS:** Todas as saídas de dados dinâmicos do usuário são sanitizadas com `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
* [x] **Controle de Acesso RBAC no Servidor:** As rotas protegidas validam permissão no backend através do `AuthMiddleware`, impedindo que alterações de URL ou manipulações de requisições acessem áreas restritas.
* [x] **Validação Rígida de Uploads:** Arquivos enviados passam por validação de tamanho (máximo 5 MB), extensão autorizada e verificação de MIME real via `mime_content_type` (`finfo`).
* [x] **Cookies de Sessão Seguros:** Configurados com `HttpOnly`, `SameSite=Lax` e destruição completa no logout.

---

## 9. Situação de Colaboração e Publicação

* **Repositório GitHub:** [https://github.com/Caju12/Cozinha-Guiada](https://github.com/Caju12/Cozinha-Guiada)
* **Colaborador adicionado:** O professor `rafaelpitwak` foi convidado com permissão de escrita (*Write*) nas configurações do repositório (**Settings ➔ Collaborators**).
* **Publicação em Nuvem:** O sistema conta com conteinerização Docker baseada em FrankenPHP (`Dockerfile.vercel`), configuração de rotas e assets (`Caddyfile`), e suporte ao PostgreSQL do Supabase via Connection Pooler (porta 6543).

### 9.1. Estado atual da publicação na Vercel

* **Endereço do site publicado:** [https://cozinhaguiada.vercel.app/](https://cozinhaguiada.vercel.app/)
* **Situação:** O projeto foi publicado na plataforma Vercel e o endereço está acessível. Entretanto, foi identificado um problema durante a execução da aplicação: o layout inicial é carregado, mas as funcionalidades do sistema não são exibidas corretamente.
* **Erro apresentado:** No corpo da página, é exibida a mensagem genérica: **“Ops! Algo deu errado — Ocorreu um problema ao processar sua requisição. Por favor, tente novamente em instantes.”**
* **Estado da resolução:** A causa do erro ainda está em investigação. Portanto, embora a publicação esteja realizada, o funcionamento das funcionalidades do sistema ainda não foi validado.

