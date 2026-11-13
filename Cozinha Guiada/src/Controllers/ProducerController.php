<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: src/Controllers/ProducerController.php
// DESCRIÇÃO: Gestão de receitas do produtor (Dashboard, Criação, Edição, Exclusão)
// ATENDE AOS REQUISITOS: RF31 a RF45, RNF05, RNF09, RNF14, CA31 a CA45
// ==============================================================================

require_once CONFIG_PATH . '/constants.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/Middleware/AuthMiddleware.php';
require_once SRC_PATH . '/Utils/Session.php';
require_once SRC_PATH . '/Models/Recipe.php';
require_once SRC_PATH . '/Services/UploadService.php';

class ProducerController {
    private PDO $db;
    private Recipe $recipeModel;
    private UploadService $uploadService;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->recipeModel = new Recipe();
        $this->uploadService = new UploadService();
    }

    /**
     * Painel de controle do Produtor com suas receitas (RF31)
     */
    public function dashboard(): void {
        AuthMiddleware::requireProducer();
        $userId = (int)Session::get('user_id');

        $stmt = $this->db->prepare("
            SELECT 
                r.*,
                COALESCE(AVG(a.nota), 0) AS media_avaliacao,
                COUNT(DISTINCT a.usuario_id) AS total_avaliacoes,
                COUNT(DISTINCT f.usuario_id) AS total_favoritos
            FROM receitas r
            LEFT JOIN avaliacoes a ON a.receita_id = r.id
            LEFT JOIN favoritos f ON f.receita_id = r.id
            WHERE r.usuario_id = ?
            GROUP BY r.id
            ORDER BY r.criado_em DESC
        ");
        $stmt->execute([$userId]);
        $recipes = $stmt->fetchAll();

        foreach ($recipes as &$rec) {
            $rec['categorias'] = $this->recipeModel->getRecipeCategories($rec['id']);
            $rec['compatibilidades'] = $this->recipeModel->getRecipeCompatibilities($rec['id']);
        }

        require VIEWS_PATH . '/producer/dashboard.php';
    }

    /**
     * Exibe o formulário para criação de nova receita (RF31 a RF45)
     */
    public function createForm(): void {
        AuthMiddleware::requireProducer();

        $mode = 'create';
        $categorias = $this->recipeModel->getAllCategories();
        $compatibilidades = $this->recipeModel->getAllCompatibilities();
        $utensilios = $this->db->query("SELECT id, nome FROM utensilios ORDER BY nome ASC")->fetchAll();
        $catalogoIngredientes = $this->recipeModel->getAllIngredients();

        $recipe = [
            'id' => 0,
            'titulo' => '',
            'descricao' => '',
            'tempo_preparo_minutos' => 30,
            'rendimento' => '',
            'temperatura' => '',
            'intensidade_fogo' => '',
            'ponto_esperado' => '',
            'visibilidade' => VISIBILITY_PUBLIC,
            'categorias' => [],
            'compatibilidades' => [],
            'utensilios' => [],
            'ingredientes' => [],
            'etapas' => []
        ];

        require VIEWS_PATH . '/producer/recipe_form.php';
    }

    /**
     * Processa a criação e persistência de uma nova receita completa (RF31 a RF45)
     */
    public function store(): void {
        AuthMiddleware::requireProducer();

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token de segurança inválido.');
            header('Location: /produtor/receita/nova');
            exit;
        }

        $userId = (int)Session::get('user_id');
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $tempoPreparo = max(1, (int)($_POST['tempo_preparo_minutos'] ?? 0));
        $rendimento = trim($_POST['rendimento'] ?? '');
        $temperatura = trim($_POST['temperatura'] ?? '');
        $fogo = trim($_POST['intensidade_fogo'] ?? '');
        $pontoEsperado = trim($_POST['ponto_esperado'] ?? '');
        $visibilidade = in_array($_POST['visibilidade'] ?? '', [VISIBILITY_PUBLIC, VISIBILITY_PRIVATE], true)
            ? $_POST['visibilidade']
            : VISIBILITY_PUBLIC;

        if (empty($titulo) || empty($descricao)) {
            Session::flash('error', 'O título e a descrição da receita são obrigatórios.');
            header('Location: /produtor/receita/nova');
            exit;
        }

        try {
            $this->db->beginTransaction();

            // 1. Insere dados principais da receita (RF31, RF40)
            $stmt = $this->db->prepare("
                INSERT INTO receitas (
                    usuario_id, titulo, descricao, tempo_preparo_minutos, rendimento, 
                    temperatura, intensidade_fogo, ponto_esperado, visibilidade, status_moderacao
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'ativo')
            ");
            $stmt->execute([
                $userId, $titulo, $descricao, $tempoPreparo, $rendimento,
                $temperatura, $fogo, $pontoEsperado, $visibilidade
            ]);
            $recipeId = (int)$this->db->lastInsertId();

            // 2. Upload de imagem se fornecida (RF35, RF36)
            if (!empty($_FILES['imagem']) && $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadRes = $this->uploadService->uploadRecipeImage($_FILES['imagem']);
                if ($uploadRes['success']) {
                    $stmtImg = $this->db->prepare("INSERT INTO imagens_receita (receita_id, caminho, ordem) VALUES (?, ?, 1)");
                    $stmtImg->execute([$recipeId, $uploadRes['path']]);
                } else {
                    Session::flash('error', 'Aviso sobre a imagem: ' . $uploadRes['error']);
                }
            }

            // 3. Associa Categorias (RF03, RF51)
            if (!empty($_POST['categorias']) && is_array($_POST['categorias'])) {
                $stmtCat = $this->db->prepare("INSERT INTO receita_categorias (receita_id, categoria_id) VALUES (?, ?)");
                foreach ($_POST['categorias'] as $catId) {
                    $stmtCat->execute([$recipeId, (int)$catId]);
                }
            }

            // 4. Associa Compatibilidades Alimentares (RF41, RF42)
            if (!empty($_POST['compatibilidades']) && is_array($_POST['compatibilidades'])) {
                $stmtComp = $this->db->prepare("INSERT INTO receita_compatibilidades (receita_id, compatibilidade_id) VALUES (?, ?)");
                foreach ($_POST['compatibilidades'] as $compId) {
                    $stmtComp->execute([$recipeId, (int)$compId]);
                }
            }

            // 5. Associa Utensílios Culinários (RF34)
            if (!empty($_POST['utensilios']) && is_array($_POST['utensilios'])) {
                $stmtUt = $this->db->prepare("INSERT INTO receita_utensilios (receita_id, utensilio_id) VALUES (?, ?)");
                foreach ($_POST['utensilios'] as $utId) {
                    $stmtUt->execute([$recipeId, (int)$utId]);
                }
            }

            // 6. Insere Ingredientes e Substituições (RF32, RF43, RF44)
            if (!empty($_POST['ingredientes']) && is_array($_POST['ingredientes'])) {
                $this->saveIngredientsAndSubstitutions($recipeId, $_POST['ingredientes']);
            }

            // 7. Insere Etapas do Modo de Preparo (RF33)
            if (!empty($_POST['etapas']) && is_array($_POST['etapas'])) {
                $this->saveSteps($recipeId, $_POST['etapas']);
            }

            $this->db->commit();
            Session::flash('success', 'Receita publicada com sucesso!');
            header('Location: /produtor/painel');
            exit;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao salvar receita: " . $e->getMessage());
            Session::flash('error', 'Ocorreu um erro ao salvar a receita. Tente novamente.');
            header('Location: /produtor/receita/nova');
            exit;
        }
    }

    /**
     * Exibe o formulário de edição de receita existente (RF38)
     */
    public function editForm(): void {
        AuthMiddleware::requireProducer();
        $id = (int)($_GET['id'] ?? 0);
        $userId = (int)Session::get('user_id');
        $userRole = Session::get('user_role');

        $recipe = $this->recipeModel->findById($id);

        if (!$recipe) {
            Session::flash('error', 'Receita não encontrada.');
            header('Location: /produtor/painel');
            exit;
        }

        // Verificação estrita de posse (RNF09)
        if ((int)$recipe['usuario_id'] !== $userId && $userRole !== ROLE_ADMIN) {
            if (!headers_sent()) {
                http_response_code(403);
            }
            Session::flash('error', 'Você não tem permissão para editar receitas de outros produtores.');
            header('Location: /produtor/painel');
            exit;
        }

        $mode = 'edit';
        $categorias = $this->recipeModel->getAllCategories();
        $compatibilidades = $this->recipeModel->getAllCompatibilities();
        $utensilios = $this->db->query("SELECT id, nome FROM utensilios ORDER BY nome ASC")->fetchAll();
        $catalogoIngredientes = $this->recipeModel->getAllIngredients();

        require VIEWS_PATH . '/producer/recipe_form.php';
    }

    /**
     * Atualiza uma receita existente e seus relacionamentos (RF38, RNF09)
     */
    public function update(): void {
        AuthMiddleware::requireProducer();

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token de segurança inválido.');
            header('Location: /produtor/painel');
            exit;
        }

        $recipeId = (int)($_POST['id'] ?? 0);
        $userId = (int)Session::get('user_id');
        $userRole = Session::get('user_role');

        // Confirma posse
        $stmtOwner = $this->db->prepare("SELECT usuario_id FROM receitas WHERE id = ?");
        $stmtOwner->execute([$recipeId]);
        $ownerId = $stmtOwner->fetchColumn();

        if (!$ownerId || ((int)$ownerId !== $userId && $userRole !== ROLE_ADMIN)) {
            if (!headers_sent()) {
                http_response_code(403);
            }
            Session::flash('error', 'Permissão negada para editar esta receita.');
            header('Location: /produtor/painel');
            exit;
        }

        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $tempoPreparo = max(1, (int)($_POST['tempo_preparo_minutos'] ?? 0));
        $rendimento = trim($_POST['rendimento'] ?? '');
        $temperatura = trim($_POST['temperatura'] ?? '');
        $fogo = trim($_POST['intensidade_fogo'] ?? '');
        $pontoEsperado = trim($_POST['ponto_esperado'] ?? '');
        $visibilidade = in_array($_POST['visibilidade'] ?? '', [VISIBILITY_PUBLIC, VISIBILITY_PRIVATE], true)
            ? $_POST['visibilidade']
            : VISIBILITY_PUBLIC;

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                UPDATE receitas SET
                    titulo = ?, descricao = ?, tempo_preparo_minutos = ?, rendimento = ?,
                    temperatura = ?, intensidade_fogo = ?, ponto_esperado = ?, visibilidade = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $titulo, $descricao, $tempoPreparo, $rendimento,
                $temperatura, $fogo, $pontoEsperado, $visibilidade, $recipeId
            ]);

            // Upload de nova imagem
            if (!empty($_FILES['imagem']) && $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadRes = $this->uploadService->uploadRecipeImage($_FILES['imagem']);
                if ($uploadRes['success']) {
                    $this->db->prepare("DELETE FROM imagens_receita WHERE receita_id = ?")->execute([$recipeId]);
                    $this->db->prepare("INSERT INTO imagens_receita (receita_id, caminho, ordem) VALUES (?, ?, 1)")
                             ->execute([$recipeId, $uploadRes['path']]);
                }
            }

            // Atualiza Categorias
            $this->db->prepare("DELETE FROM receita_categorias WHERE receita_id = ?")->execute([$recipeId]);
            if (!empty($_POST['categorias']) && is_array($_POST['categorias'])) {
                $stmtCat = $this->db->prepare("INSERT INTO receita_categorias (receita_id, categoria_id) VALUES (?, ?)");
                foreach ($_POST['categorias'] as $catId) {
                    $stmtCat->execute([$recipeId, (int)$catId]);
                }
            }

            // Atualiza Compatibilidades
            $this->db->prepare("DELETE FROM receita_compatibilidades WHERE receita_id = ?")->execute([$recipeId]);
            if (!empty($_POST['compatibilidades']) && is_array($_POST['compatibilidades'])) {
                $stmtComp = $this->db->prepare("INSERT INTO receita_compatibilidades (receita_id, compatibilidade_id) VALUES (?, ?)");
                foreach ($_POST['compatibilidades'] as $compId) {
                    $stmtComp->execute([$recipeId, (int)$compId]);
                }
            }

            // Atualiza Utensílios
            $this->db->prepare("DELETE FROM receita_utensilios WHERE receita_id = ?")->execute([$recipeId]);
            if (!empty($_POST['utensilios']) && is_array($_POST['utensilios'])) {
                $stmtUt = $this->db->prepare("INSERT INTO receita_utensilios (receita_id, utensilio_id) VALUES (?, ?)");
                foreach ($_POST['utensilios'] as $utId) {
                    $stmtUt->execute([$recipeId, (int)$utId]);
                }
            }

            // Atualiza Ingredientes
            $this->db->prepare("DELETE FROM receita_ingredientes WHERE receita_id = ?")->execute([$recipeId]);
            if (!empty($_POST['ingredientes']) && is_array($_POST['ingredientes'])) {
                $this->saveIngredientsAndSubstitutions($recipeId, $_POST['ingredientes']);
            }

            // Atualiza Etapas
            $this->db->prepare("DELETE FROM etapas_receita WHERE receita_id = ?")->execute([$recipeId]);
            if (!empty($_POST['etapas']) && is_array($_POST['etapas'])) {
                $this->saveSteps($recipeId, $_POST['etapas']);
            }

            $this->db->commit();
            Session::flash('success', 'Receita atualizada com sucesso!');
            header('Location: /produtor/painel');
            exit;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao atualizar receita: " . $e->getMessage());
            Session::flash('error', 'Ocorreu um erro ao atualizar a receita.');
            header("Location: /produtor/receita/editar?id={$recipeId}");
            exit;
        }
    }

    /**
     * Alterna a visibilidade entre Pública e Privada (RF40)
     */
    public function toggleVisibility(): void {
        AuthMiddleware::requireProducer();

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token inválido.');
            header('Location: /produtor/painel');
            exit;
        }

        $recipeId = (int)($_POST['id'] ?? 0);
        $userId = (int)Session::get('user_id');
        $userRole = Session::get('user_role');

        $stmt = $this->db->prepare("SELECT usuario_id, visibilidade FROM receitas WHERE id = ?");
        $stmt->execute([$recipeId]);
        $recipe = $stmt->fetch();

        if (!$recipe || ((int)$recipe['usuario_id'] !== $userId && $userRole !== ROLE_ADMIN)) {
            if (!headers_sent()) {
                http_response_code(403);
            }
            Session::flash('error', 'Permissão negada.');
            header('Location: /produtor/painel');
            exit;
        }

        $newVis = $recipe['visibilidade'] === VISIBILITY_PUBLIC ? VISIBILITY_PRIVATE : VISIBILITY_PUBLIC;
        $this->db->prepare("UPDATE receitas SET visibilidade = ? WHERE id = ?")->execute([$newVis, $recipeId]);

        Session::flash('success', "A receita agora está {$newVis}.");
        header('Location: /produtor/painel');
        exit;
    }

    /**
     * Exclui receita própria de forma segura preservando integridade (RF39, CA39)
     */
    public function delete(): void {
        AuthMiddleware::requireProducer();

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token inválido.');
            header('Location: /produtor/painel');
            exit;
        }

        $recipeId = (int)($_POST['id'] ?? 0);
        $userId = (int)Session::get('user_id');
        $userRole = Session::get('user_role');

        $stmt = $this->db->prepare("SELECT usuario_id FROM receitas WHERE id = ?");
        $stmt->execute([$recipeId]);
        $ownerId = $stmt->fetchColumn();

        if (!$ownerId || ((int)$ownerId !== $userId && $userRole !== ROLE_ADMIN)) {
            if (!headers_sent()) {
                http_response_code(403);
            }
            Session::flash('error', 'Permissão negada para excluir esta receita.');
            header('Location: /produtor/painel');
            exit;
        }

        // Exclusão (Foreign keys em ON DELETE CASCADE tratam os dependentes)
        $this->db->prepare("DELETE FROM receitas WHERE id = ?")->execute([$recipeId]);

        Session::flash('success', 'Receita excluída permanentemente.');
        header('Location: /produtor/painel');
        exit;
    }

    // --------------------------------------------------------------------------
    // MÉTODOS AUXILIARES
    // --------------------------------------------------------------------------

    private function saveIngredientsAndSubstitutions(int $recipeId, array $rawIngredients): void {
        $stmtIng = $this->db->prepare("
            INSERT INTO receita_ingredientes (receita_id, ingrediente_id, quantidade, unidade, observacao)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmtSubst = $this->db->prepare("
            INSERT INTO substituicoes_ingrediente (receita_ingrediente_id, texto_substituto, proporcao, observacao)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($rawIngredients as $item) {
            $nome = trim($item['nome'] ?? '');
            if (empty($nome)) continue;

            $ingId = $this->findOrCreateIngredient($nome);
            $qtd = !empty($item['quantidade']) ? (float)$item['quantidade'] : null;
            $unidade = trim($item['unidade'] ?? '');
            $obs = trim($item['observacao'] ?? '');

            $stmtIng->execute([$recipeId, $ingId, $qtd, $unidade, $obs]);
            $recIngId = (int)$this->db->lastInsertId();

            // Salva substituição se informada (RF13, RF43, RF44)
            $substTexto = trim($item['substituto_texto'] ?? '');
            if (!empty($substTexto)) {
                $substProp = trim($item['substituto_proporcao'] ?? '');
                $substObs = trim($item['substituto_observacao'] ?? '');
                $stmtSubst->execute([$recIngId, $substTexto, $substProp, $substObs]);
            }
        }
    }

    private function saveSteps(int $recipeId, array $rawSteps): void {
        $stmtStep = $this->db->prepare("
            INSERT INTO etapas_receita (receita_id, ordem, descricao)
            VALUES (?, ?, ?)
        ");

        $order = 1;
        foreach ($rawSteps as $stepText) {
            $desc = trim(is_array($stepText) ? ($stepText['descricao'] ?? '') : $stepText);
            if (!empty($desc)) {
                $stmtStep->execute([$recipeId, $order++, $desc]);
            }
        }
    }

    private function findOrCreateIngredient(string $nome): int {
        $stmtFind = $this->db->prepare("SELECT id FROM ingredientes WHERE LOWER(nome) = LOWER(?) LIMIT 1");
        $stmtFind->execute([$nome]);
        $id = $stmtFind->fetchColumn();

        if ($id) {
            return (int)$id;
        }

        $stmtIns = $this->db->prepare("INSERT INTO ingredientes (nome) VALUES (?)");
        $stmtIns->execute([$nome]);
        return (int)$this->db->lastInsertId();
    }
}
