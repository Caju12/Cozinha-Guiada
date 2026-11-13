<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: src/Controllers/RecipeController.php
// DESCRIÇÃO: Controlador do catálogo público, busca, filtros e detalhes da receita
// ATENDE AOS REQUISITOS: RF01, RF02, RF03, RF08, RF09, RF10, RF11, RF12, RF13,
//                        RF14, RF15, RF16, RF17, RF23, RF53, RNF04
// ==============================================================================

require_once CONFIG_PATH . '/constants.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/Models/Recipe.php';
require_once SRC_PATH . '/Utils/Session.php';

class RecipeController {
    private Recipe $recipeModel;

    public function __construct() {
        $this->recipeModel = new Recipe();
    }

    /**
     * Catálogo público com pesquisa e filtros combinados (RF01, RF02, RF03, RF08, RF10, RF11)
     */
    public function index(): void {
        $searchQuery = $_GET['q'] ?? '';
        $categoriaId = !empty($_GET['categoria']) ? (int)$_GET['categoria'] : null;
        
        $compatibilidades = isset($_GET['compatibilidades']) && is_array($_GET['compatibilidades'])
            ? $_GET['compatibilidades']
            : [];

        $ingredientes = isset($_GET['ingredientes']) && is_array($_GET['ingredientes'])
            ? $_GET['ingredientes']
            : [];

        $filters = [
            'q'                => $searchQuery,
            'categoria_id'     => $categoriaId,
            'compatibilidades' => $compatibilidades,
            'ingredientes'     => $ingredientes
        ];

        // Executa busca com tempo de resposta otimizado (RNF04)
        $recipes = $this->recipeModel->search($filters);
        
        $categorias = $this->recipeModel->getAllCategories();
        $todasCompatibilidades = $this->recipeModel->getAllCompatibilities();
        $catalogoIngredientes = $this->recipeModel->getAllIngredients();

        require_once VIEWS_PATH . '/recipes/index.php';
    }

    /**
     * Exibição detalhada de uma receita (RF12 a RF17, RF23)
     */
    public function show(): void {
        $id = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            if (!headers_sent()) {
                header('Location: /receitas');
            }
            return;
        }

        $recipe = $this->recipeModel->findById($id);

        if (!$recipe || $recipe['status_moderacao'] === STATUS_REMOVED) {
            if (!headers_sent()) {
                http_response_code(404);
            }
            require_once VIEWS_PATH . '/layouts/header.php';
            echo "
                <div class='max-w-xl mx-auto my-16 bg-white p-8 rounded-2xl shadow-sm text-center border border-stone-200'>
                    <div class='text-6xl mb-4'>🍲❌</div>
                    <h1 class='text-2xl font-bold text-stone-800 mb-2'>Receita não encontrada</h1>
                    <p class='text-stone-600 mb-6'>A receita solicitada não existe ou foi removida pela moderação.</p>
                    <a href='/receitas' class='inline-block bg-amber-600 hover:bg-amber-700 text-white font-medium px-6 py-2.5 rounded-xl transition-all shadow'>Explorar Outras Receitas</a>
                </div>
            ";
            require_once VIEWS_PATH . '/layouts/footer.php';
            return;
        }

        // Se a receita for privada (RF40), apenas o autor ou admin pode visualizá-la
        if ($recipe['visibilidade'] === VISIBILITY_PRIVATE) {
            $currentUserId = Session::get('user_id');
            $currentUserRole = Session::get('user_role');

            if ($currentUserId !== (int)$recipe['usuario_id'] && $currentUserRole !== ROLE_ADMIN) {
                http_response_code(403);
                Session::flash('error', 'Esta receita é privada e pode ser visualizada apenas pelo autor.');
                header('Location: /receitas');
                exit;
            }
        }

        // Registra histórico de visualização se o usuário estiver autenticado (RF23, CA23)
        $userId = (int)Session::get('user_id', 0);
        if ($userId > 0) {
            $this->recordHistory($userId, $id);
        }

        // Carrega interações do usuário (Etapa 5)
        $comments = $this->recipeModel->getRecipeComments($id);
        $isFavorited = $this->recipeModel->isFavoritedByUser($id, $userId);
        $userRating = $this->recipeModel->getUserRating($id, $userId);

        require_once VIEWS_PATH . '/recipes/show.php';
    }

    /**
     * Modo de preparo passo a passo guiado e imersivo (RF18, RF19)
     */
    public function guidedMode(): void {
        $id = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            if (!headers_sent()) {
                header('Location: /receitas');
            }
            return;
        }

        $recipe = $this->recipeModel->findById($id);

        if (!$recipe || $recipe['status_moderacao'] === STATUS_REMOVED) {
            if (!headers_sent()) {
                http_response_code(404);
            }
            Session::flash('error', 'Receita não encontrada.');
            if (!headers_sent()) {
                header('Location: /receitas');
            }
            return;
        }

        // Se a receita for privada (RF40), apenas o autor ou admin pode acessar
        if ($recipe['visibilidade'] === VISIBILITY_PRIVATE) {
            $currentUserId = Session::get('user_id');
            $currentUserRole = Session::get('user_role');

            if ($currentUserId !== (int)$recipe['usuario_id'] && $currentUserRole !== ROLE_ADMIN) {
                if (!headers_sent()) {
                    http_response_code(403);
                }
                Session::flash('error', 'Esta receita é privada.');
                if (!headers_sent()) {
                    header('Location: /receitas');
                }
                return;
            }
        }

        if (empty($recipe['etapas'])) {
            Session::flash('error', 'Esta receita ainda não possui etapas de preparo cadastradas.');
            if (!headers_sent()) {
                header("Location: /receita?id={$id}");
            }
            return;
        }

        require_once VIEWS_PATH . '/recipes/guided_mode.php';
    }

    /**
     * Perfil público do Produtor de Conteúdo (RF53, CA53)
     */
    public function producerProfile(): void {
        $id = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            if (!headers_sent()) {
                header('Location: /receitas');
            }
            return;
        }

        $producer = $this->recipeModel->getProducerProfile($id);
        if (!$producer) {
            if (!headers_sent()) {
                http_response_code(404);
            }
            Session::flash('error', 'Produtor não encontrado.');
            if (!headers_sent()) {
                header('Location: /receitas');
            }
            return;
        }

        $recipes = $this->recipeModel->findByProducer($id);

        require_once VIEWS_PATH . '/users/producer_profile.php';
    }

    /**
     * Registra no histórico do usuário autenticado (RF23)
     */
    private function recordHistory(int $userId, int $recipeId): void {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO historico_receitas (usuario_id, receita_id, visualizado_em) 
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$userId, $recipeId]);
        } catch (Exception $e) {
            // Falha no histórico não deve interromper a experiência do usuário
            error_log("Erro ao salvar histórico: " . $e->getMessage());
        }
    }
}
