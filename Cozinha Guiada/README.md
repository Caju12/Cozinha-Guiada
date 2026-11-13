# 🍳 Cozinha Guiada — Plataforma Web de Culinária Inclusiva

> **Cozinha Guiada** é uma plataforma web completa desenvolvida para iniciantes na cozinha e pessoas com restrições alimentares. Oferece um **Modo Guiado** passo a passo com tela sempre ativa (Screen Wake Lock), substituições inteligentes de ingredientes e filtros combinados estritos para dietas especiais.

---

## 📌 Sumário
- [Destaques e Recursos Principais](#-destaques-e-recursos-principais)
- [Arquitetura e Tecnologias](#-arquitetura-e-tecnologias)
- [Estrutura do Projeto](#-estrutura-do-projeto)
- [Instalação e Configuração](#-instalação-e-configuração)
- [Contas de Acesso Padrão (Seed)](#-contas-de-acesso-padrão-seed)
- [Execução dos Testes Automatizados](#-execução-dos-testes-automatizados)
- [Matriz de Conformidade e Aceite](#-matriz-de-conformidade-e-aceite)

---

## 🌟 Destaques e Recursos Principais

1. **Modo de Preparo Guiado Passo a Passo (RF18, RF19):**
   - Interface imersiva e focada, sem distrações.
   - Integração com a **Screen Wake Lock API** para impedir que a tela do celular ou tablet apague enquanto o usuário cozinha com as mãos sujas.
   - Temporizador culinário integrado com avisos sonoros gerados nativamente via **Web Audio API**.
   - Navegação acessível por teclado (Setas Esquerda/Direita para avançar/voltar e Barra de Espaço para temporizador).

2. **Substituições Inteligentes e Inclusivas (RF13, RF43, RF44):**
   - Cada ingrediente pode possuir sugestões alternativas com indicação de proporção e dicas culinárias (ex: substitutos sem glúten, sem lactose ou versões veganas).

3. **Filtros Combinados Estritos (Relação "E" / AND - RF11):**
   - Permite filtrar simultaneamente por múltiplas compatibilidades (ex: *Vegetariana* **E** *Sem Lactose* **E** *Sem Glúten*), retornando apenas receitas que atendem **todas** as restrições selecionadas via agregação SQL estrita.

4. **Busca por Despensa (RF08):**
   - Encontre receitas com base nos ingredientes que você já possui em casa.

5. **Área do Produtor de Conteúdo (RF31 a RF45):**
   - Construtor dinâmico de receitas com adição fluida de ingredientes, substituições expansíveis, utensílios e passos sequenciais.
   - Upload validado de imagens (JPG, PNG, WEBP até 5MB) com renomeação segura.
   - Controle de visibilidade da receita (Pública ou Privada).

6. **Painel Administrativo & Moderação (RF46 a RF52):**
   - Dashboard com métricas globais de receitas, usuários, avaliações e denúncias.
   - Gestão de status de usuários (ativo/bloqueado) e perfis RBAC com **prevenção contra auto-bloqueio (*lockout*)** do último administrador.
   - Moderação de fila de denúncias com remoção de conteúdo infrator ou descarte justificado.
   - CRUD completo de categorias culinárias.

---

## 🛠️ Arquitetura e Tecnologias

- **Linguagem Backend:** PHP 8.2 (Vanilla MVC sem frameworks pesados).
- **Banco de Dados:** MySQL / MariaDB (compatível com XAMPP) com 19 tabelas normalizadas e integridade referencial (`ON DELETE CASCADE`).
- **Conexão:** PDO com Prepared Statements estritos contra SQL Injection (`PDO::ATTR_EMULATE_PREPARES => false`).
- **Frontend & Estilo:** HTML5 semântico, Tailwind CSS (via CDN) e CSS customizado com estilos de acessibilidade (`:focus-visible`) e folha de estilo para impressão (`@media print`).
- **Scripts:** JavaScript ES6+ Vanilla (Screen Wake Lock API, Web Audio API, Recipe Builder dinâmico).
- **Segurança:** RBAC com 4 níveis (`visitante`, `cadastrado`, `produtor`, `administrador`), senhas criptografadas com `BCRYPT`, proteção anti-CSRF, cookies `HttpOnly`, e prevenção contra XSS.

---

## 📁 Estrutura do Projeto

```text
Cozinha Guiada/
├── config/
│   ├── constants.php          # Constantes globais, caminhos e perfis RBAC
│   └── database.php           # Singleton PDO com tratamento seguro de erros
├── database/
│   ├── schema.sql             # Esquema relacional com as 19 tabelas e constraints
│   └── seed.sql               # Categorias, dietas, utensílios, usuários e receitas demo
├── public/
│   ├── css/
│   │   └── style.css          # Estilos complementares, foco e @media print
│   ├── js/
│   │   ├── guided-mode.js     # Lógica do Modo Guiado, Wake Lock e AudioContext
│   │   └── recipe-builder.js  # Gerenciador dinâmico do formulário do produtor
│   ├── uploads/               # Diretório para armazenamento de fotos de receitas
│   ├── .htaccess              # Regras de reescrita Apache para o Front Controller
│   └── index.php              # Front Controller e Roteador da aplicação
├── src/
│   ├── Controllers/           # Controladores das rotas e regras de negócio
│   ├── Middleware/            # Autenticação e autorização RBAC (AuthMiddleware)
│   ├── Models/                # Modelos de dados e queries SQL otimizadas
│   ├── Services/              # UploadService, AuthService
│   └── Utils/                 # Session, Validator
├── tests/                     # Bateria de testes automatizados e checklist de aceite
│   ├── acceptance_checklist.md# Matriz detalhada dos 53 RFs e 19 RNFs
│   ├── run_all_tests.php      # Runner unificado de todos os testes
│   ├── test_etapa2.php a test_etapa8_final.php
│   └── test_routes.php        # Validador de renderização de todas as rotas web
├── views/                     # Templates e visões organizadas por domínio
├── .env                       # Variáveis de ambiente locais
├── .env.example               # Exemplo de configuração de ambiente
├── .htaccess                  # Redirecionamento da raiz para /public
└── README.md                  # Documentação oficial do projeto
```

---

## 🚀 Instalação e Configuração

### 1. Pré-requisitos
- **XAMPP** (ou similar) com Apache e MySQL ativos, com suporte a **PHP 8.1+**.

### 2. Configuração do Banco de Dados
1. No painel do XAMPP, inicie os serviços **Apache** e **MySQL**.
2. Abra o terminal ou acesse o `phpMyAdmin` (`http://localhost/phpmyadmin`).
3. Crie o banco de dados `cozinha_guiada` (charset `utf8mb4_unicode_ci`):
   ```sql
   CREATE DATABASE cozinha_guiada CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
4. Importe o esquema de tabelas:
   ```bash
   mysql -u root -p cozinha_guiada < database/schema.sql
   ```
5. Importe os dados iniciais de semente:
   ```bash
   mysql -u root -p cozinha_guiada < database/seed.sql
   ```

### 3. Configuração do `.env`
Verifique ou crie o arquivo `.env` na raiz do projeto:
```ini
DB_HOST=localhost
DB_PORT=3306
DB_NAME=cozinha_guiada
DB_USER=root
DB_PASS=
# A aplicação usa os padrões definidos no código quando a variável não é informada.
```

### 4. Inicialização do Servidor Local
Você pode rodar o servidor embutido do PHP apontando para a pasta pública:
```powershell
& "C:\xampp\php\php.exe" -S 127.0.0.1:8000 -t public
```
Em seguida, acesse no navegador: **[http://127.0.0.1:8000](http://127.0.0.1:8000)**.

---

## 🔑 Contas de Acesso Padrão (Seed)

Todas as contas iniciais de teste utilizam a senha: **`Admin@2026`**

| Perfil | E-mail de Acesso | Permissões |
| :--- | :--- | :--- |
| **Administrador** | `admin@cozinhaguiada.com` | Acesso total ao sistema, moderação, gestão de usuários e categorias |
| **Chef / Produtor** | `chef@cozinhaguiada.com` | Criação, edição, exclusão e gestão de receitas próprias |
| **Membro Cadastrado** | `leitor@cozinhaguiada.com` | Favoritos, histórico, avaliações, comentários e denúncias |

---

## 🧪 Execução dos Testes Automatizados

O sistema conta com suítes de testes automatizados que cobrem desde a camada de banco de dados, regras de negócio e controle de acesso até a renderização visual das rotas.

Para executar todas as suítes unificadas:
```powershell
& "C:\xampp\php\php.exe" tests\run_all_tests.php
```

### Resultado Esperado:
```text
====================================================================
                    RESUMO GERAL DE HOMOLOGAÇÃO                     
====================================================================
  Etapa 2 - Autenticação & RBAC          ✅ APROVADO (15/15)
  Etapa 3 - Catálogo & Filtros E          ✅ APROVADO (19/19)
  Etapa 4 - Modo Guiado & Wake Lock        ✅ APROVADO (19/19)
  Etapa 5 - Interações Sociais           ✅ APROVADO (12/12)
  Etapa 6 - Área do Produtor              ✅ APROVADO (15/15)
  Etapa 7 - Painel Admin & Moderação     ✅ APROVADO (15/15)
  Etapa 8 - Aceite Final & RNFs            ✅ APROVADO (14/14)
  Rotas - Renderização Front-End         ✅ APROVADO (19/19)
====================================================================
🎉 SUCESSO TOTAL! Todas as suítes de teste foram concluídas com êxito.
```

---

## 📋 Matriz de Conformidade e Especificação Técnica

* 📄 **Especificação Técnica e Funcional Final Consolidada:** [especificacao_tecnica_final_cozinha_guiada.md](especificacao_tecnica_final_cozinha_guiada.md)
* 🧪 **Matriz de Rastreabilidade e Critérios de Aceite (CA01 a CA53):** [tests/acceptance_checklist.md](tests/acceptance_checklist.md)
