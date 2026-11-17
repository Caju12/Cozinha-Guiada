<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: src/Controllers/CommentController.php
// DESCRIÇÃO: Gestão de comentários com proteção de posse e sanitização XSS
// ATENDE AOS REQUISITOS: RF25, RF26, RF27, RNF09, RNF11, CA25, CA26, CA27
// ==============================================================================

require_once CONFIG_PATH . '/constants.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/Middleware/AuthMiddleware.php';
require_once SRC_PATH . '/Utils/Session.php';

class CommentController {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Publica um novo comentário em uma receita (RF25, CA25)
     */
    public function create(): void {
        AuthMiddleware::requireAuth();

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token de segurança expirado. Tente novamente.');
            $this->redirectBack();
            return;
        }

        $userId = (int)Session::get('user_id');
        $recipeId = (int)($_POST['receita_id'] ?? 0);
        
        // Sanitização estrita contra ataques Cross-Site Scripting (XSS - RNF11)
        $texto = trim(strip_tags($_POST['texto'] ?? ''));

        if ($recipeId <= 0 || empty($texto)) {
            Session::flash('error', 'O comentário não pode ficar vazio.');
            $this->redirectBack();
            return;
        }

        if (mb_strlen($texto) > 1000) {
            Session::flash('error', 'O comentário não pode ultrapassar 1000 caracteres.');
            $this->redirectBack();
            return;
        }

        $stmt = $this->db->prepare("
            INSERT INTO comentarios (usuario_id, receita_id, texto, status) 
            VALUES (?, ?, ?, 'ativo') RETURNING id
        ");
        $stmt->execute([$userId, $recipeId, $texto]);
        $commentId = (int)$stmt->fetchColumn();

        Session::flash('success', 'Seu comentário foi publicado com sucesso!');
        $this->redirectBack("#comentario-" . $commentId);
    }

    /**
     * Edita o próprio comentário com verificação estrita de posse (RF26, RNF09 / CA26)
     */
    public function update(): void {
        AuthMiddleware::requireAuth();

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirectBack();
            return;
        }

        $userId = (int)Session::get('user_id');
        $commentId = (int)($_POST['comentario_id'] ?? 0);
        $texto = trim(strip_tags($_POST['texto'] ?? ''));

        if ($commentId <= 0 || empty($texto)) {
            Session::flash('error', 'O texto do comentário não pode ficar vazio.');
            $this->redirectBack();
            return;
        }

        // Busca o comentário existente
        $stmt = $this->db->prepare("SELECT id, usuario_id FROM comentarios WHERE id = ?");
        $stmt->execute([$commentId]);
        $comment = $stmt->fetch();

        if (!$comment) {
            Session::flash('error', 'Comentário não encontrado.');
            $this->redirectBack();
            return;
        }

        // Verificação estrita de posse do recurso: apenas o autor pode editar (RNF09)
        if ((int)$comment['usuario_id'] !== $userId) {
            if (!headers_sent()) {
                http_response_code(403);
            }
            Session::flash('error', 'Você não tem permissão para editar um comentário que não é seu.');
            $this->redirectBack();
            return;
        }

        $stmtUp = $this->db->prepare("UPDATE comentarios SET texto = ? WHERE id = ?");
        $stmtUp->execute([$texto, $commentId]);

        Session::flash('success', 'Comentário atualizado com sucesso!');
        $this->redirectBack("#comentario-{$commentId}");
    }

    /**
     * Exclui o próprio comentário ou moderação por admin (RF27, RNF09 / CA27)
     */
    public function delete(): void {
        AuthMiddleware::requireAuth();

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirectBack();
            return;
        }

        $userId = (int)Session::get('user_id');
        $userRole = Session::get('user_role');
        $commentId = (int)($_POST['comentario_id'] ?? 0);

        $stmt = $this->db->prepare("SELECT id, usuario_id FROM comentarios WHERE id = ?");
        $stmt->execute([$commentId]);
        $comment = $stmt->fetch();

        if (!$comment) {
            Session::flash('error', 'Comentário não encontrado.');
            $this->redirectBack();
            return;
        }

        // Apenas o autor ou um administrador pode excluir (RNF09)
        if ((int)$comment['usuario_id'] !== $userId && $userRole !== ROLE_ADMIN) {
            if (!headers_sent()) {
                http_response_code(403);
            }
            Session::flash('error', 'Você não tem permissão para excluir este comentário.');
            $this->redirectBack();
            return;
        }

        $stmtDel = $this->db->prepare("DELETE FROM comentarios WHERE id = ?");
        $stmtDel->execute([$commentId]);

        Session::flash('success', 'Comentário excluído com sucesso.');
        $this->redirectBack("#comentarios");
    }

    // --------------------------------------------------------------------------
    // AUXILIARES
    // --------------------------------------------------------------------------

    private function redirectBack(string $hash = ''): void {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/receitas';
        if ($hash && !str_contains($referer, '#')) {
            $referer .= $hash;
        }
        if (!headers_sent()) {
            header("Location: {$referer}");
        }
    }
}
