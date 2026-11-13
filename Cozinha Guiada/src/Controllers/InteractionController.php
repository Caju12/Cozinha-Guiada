<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: src/Controllers/InteractionController.php
// DESCRIÇÃO: Controlador de Favoritos, Histórico e Denúncias
// ATENDE AOS REQUISITOS: RF20, RF21, RF22, RF23, RF24, RF28, RF29, RNF09, RNF11
// ==============================================================================

require_once CONFIG_PATH . '/constants.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/Middleware/AuthMiddleware.php';
require_once SRC_PATH . '/Utils/Session.php';
require_once SRC_PATH . '/Models/Recipe.php';

class InteractionController {
    private PDO $db;
    private Recipe $recipeModel;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->recipeModel = new Recipe();
    }

    // --------------------------------------------------------------------------
    // 1. FAVORITOS (RF20, RF21, RF22 / CA20, CA21, CA22)
    // --------------------------------------------------------------------------

    /**
     * Alterna o estado de favorito de uma receita para o usuário logado
     */
    public function toggleFavorite(): void {
        AuthMiddleware::requireAuth();

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirectBack();
            return;
        }

        $userId = (int)Session::get('user_id');
        $recipeId = (int)($_POST['receita_id'] ?? 0);

        if ($recipeId <= 0) {
            $this->redirectBack();
            return;
        }

        // Verifica se já está favoritado
        $stmtCheck = $this->db->prepare("SELECT 1 FROM favoritos WHERE usuario_id = ? AND receita_id = ?");
        $stmtCheck->execute([$userId, $recipeId]);
        $isFavorited = (bool)$stmtCheck->fetch();

        if ($isFavorited) {
            // Remove dos favoritos (RF21)
            $stmtDel = $this->db->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND receita_id = ?");
            $stmtDel->execute([$userId, $recipeId]);
            Session::flash('success', 'Receita removida dos seus favoritos.');
        } else {
            // Adiciona aos favoritos (RF20)
            $stmtIns = $this->db->prepare("INSERT INTO favoritos (usuario_id, receita_id) VALUES (?, ?)");
            $stmtIns->execute([$userId, $recipeId]);
            Session::flash('success', 'Receita salva nos seus favoritos com sucesso! ⭐');
        }

        $this->redirectBack();
    }

    /**
     * Exibe a lista de receitas favoritas do usuário (RF22)
     */
    public function listFavorites(): void {
        AuthMiddleware::requireAuth();
        $userId = (int)Session::get('user_id');

        $stmt = $this->db->prepare("
            SELECT 
                r.id, 
                r.titulo, 
                r.descricao, 
                r.tempo_preparo_minutos, 
                r.rendimento, 
                u.id AS autor_id, 
                u.nome AS autor_nome,
                f.criado_em AS favoritado_em,
                COALESCE(AVG(a.nota), 0) AS media_avaliacao
            FROM favoritos f
            JOIN receitas r ON f.receita_id = r.id
            JOIN usuarios u ON r.usuario_id = u.id
            LEFT JOIN avaliacoes a ON a.receita_id = r.id
            WHERE f.usuario_id = ? AND r.status_moderacao = 'ativo'
            GROUP BY r.id, f.criado_em, u.id
            ORDER BY f.criado_em DESC
        ");
        $stmt->execute([$userId]);
        $favorites = $stmt->fetchAll();

        foreach ($favorites as &$fav) {
            $fav['compatibilidades'] = $this->recipeModel->getRecipeCompatibilities($fav['id']);
        }

        require_once VIEWS_PATH . '/interactions/favorites.php';
    }

    // --------------------------------------------------------------------------
    // 2. HISTÓRICO DE VISUALIZAÇÕES (RF23, RF24 / CA23, CA24)
    // --------------------------------------------------------------------------

    /**
     * Exibe o histórico de receitas acessadas pelo usuário autenticado (RF23, RF24)
     */
    public function listHistory(): void {
        AuthMiddleware::requireAuth();
        $userId = (int)Session::get('user_id');

        // Retorna receitas visualizadas de forma agrupada pelo acesso mais recente
        $stmt = $this->db->prepare("
            SELECT 
                r.id, 
                r.titulo, 
                r.descricao, 
                r.tempo_preparo_minutos, 
                r.rendimento,
                u.id AS autor_id, 
                u.nome AS autor_nome,
                MAX(h.visualizado_em) AS ultimo_acesso,
                COUNT(h.id) AS total_acessos,
                COALESCE(AVG(a.nota), 0) AS media_avaliacao
            FROM historico_receitas h
            JOIN receitas r ON h.receita_id = r.id
            JOIN usuarios u ON r.usuario_id = u.id
            LEFT JOIN avaliacoes a ON a.receita_id = r.id
            WHERE h.usuario_id = ? AND r.status_moderacao = 'ativo'
            GROUP BY r.id
            ORDER BY ultimo_acesso DESC
        ");
        $stmt->execute([$userId]);
        $history = $stmt->fetchAll();

        foreach ($history as &$item) {
            $item['compatibilidades'] = $this->recipeModel->getRecipeCompatibilities($item['id']);
        }

        require_once VIEWS_PATH . '/interactions/history.php';
    }

    /**
     * Limpa todo o histórico de visualizações do usuário (RF24, CA24)
     */
    public function clearHistory(): void {
        AuthMiddleware::requireAuth();

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token de segurança inválido.');
            header('Location: /historico');
            exit;
        }

        $userId = (int)Session::get('user_id');
        $stmt = $this->db->prepare("DELETE FROM historico_receitas WHERE usuario_id = ?");
        $stmt->execute([$userId]);

        Session::flash('success', 'Seu histórico de receitas visualizadas foi limpo com sucesso.');
        header('Location: /historico');
        exit;
    }

    // --------------------------------------------------------------------------
    // 3. AVALIAÇÕES (NOTAS DE 1 A 5 - RF28 / CA28)
    // --------------------------------------------------------------------------

    /**
     * Registra ou atualiza a nota atribuída pelo usuário a uma receita (RF28, CA28)
     */
    public function rateRecipe(): void {
        AuthMiddleware::requireAuth();

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token de segurança expirado.');
            $this->redirectBack();
            return;
        }

        $userId = (int)Session::get('user_id');
        $recipeId = (int)($_POST['receita_id'] ?? 0);
        $nota = (int)($_POST['nota'] ?? 0);

        // Validação estrita do intervalo de 1 a 5 (CA28)
        if ($nota < 1 || $nota > 5) {
            Session::flash('error', 'A nota de avaliação deve ser um valor entre 1 e 5 estrelas.');
            $this->redirectBack();
            return;
        }

        if ($recipeId <= 0) {
            $this->redirectBack();
            return;
        }

        // Insere ou atualiza avaliação
        $stmt = $this->db->prepare("
            INSERT INTO avaliacoes (usuario_id, receita_id, nota) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE nota = VALUES(nota), atualizado_em = NOW()
        ");
        $stmt->execute([$userId, $recipeId, $nota]);

        Session::flash('success', "Obrigado! Sua avaliação ({$nota} estrelas) foi registrada com sucesso.");
        $this->redirectBack();
    }

    // --------------------------------------------------------------------------
    // 4. DENÚNCIAS (RF29 / CA29)
    // --------------------------------------------------------------------------

    /**
     * Registra uma denúncia de receita ou comentário inadequado (RF29, CA29)
     */
    public function submitReport(): void {
        AuthMiddleware::requireAuth();

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirectBack();
            return;
        }

        $userId = (int)Session::get('user_id');
        $tipoAlvo = $_POST['tipo_alvo'] ?? '';
        $alvoId = (int)($_POST['alvo_id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');

        if (!in_array($tipoAlvo, ['receita', 'comentario'], true)) {
            Session::flash('error', 'Alvo de denúncia inválido.');
            $this->redirectBack();
            return;
        }

        if ($alvoId <= 0 || empty($motivo)) {
            Session::flash('error', 'Por favor, detalhe o motivo da denúncia para que a moderação possa analisar.');
            $this->redirectBack();
            return;
        }

        $stmt = $this->db->prepare("
            INSERT INTO denuncias (usuario_id, tipo_alvo, alvo_id, motivo, status) 
            VALUES (?, ?, ?, ?, 'pendente')
        ");
        $stmt->execute([$userId, $tipoAlvo, $alvoId, $motivo]);

        Session::flash('success', 'Denúncia enviada com sucesso. Nossa equipe de moderação irá avaliar o conteúdo.');
        $this->redirectBack();
    }

    // --------------------------------------------------------------------------
    // MÉTODOS AUXILIARES
    // --------------------------------------------------------------------------

    private function redirectBack(): void {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/receitas';
        if (!headers_sent()) {
            header("Location: {$referer}");
        }
    }
}
