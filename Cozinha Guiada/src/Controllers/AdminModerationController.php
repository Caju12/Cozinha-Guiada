<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: src/Controllers/AdminModerationController.php
// DESCRIÇÃO: Moderação de receitas e comentários sob denúncia (RF49, RF50, RF52)
// ==============================================================================

require_once CONFIG_PATH . '/constants.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/Middleware/AuthMiddleware.php';
require_once SRC_PATH . '/Utils/Session.php';

class AdminModerationController {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Fila de moderação de denúncias (RF52)
     */
    public function reportsList(): void {
        AuthMiddleware::requireAdmin();

        $statusFilter = trim($_GET['status'] ?? 'pendente');

        $sql = "
            SELECT 
                d.*, 
                u.nome AS autor_denuncia_nome,
                u.email AS autor_denuncia_email,
                adm.nome AS admin_resolvido_nome
            FROM denuncias d
            JOIN usuarios u ON d.usuario_id = u.id
            LEFT JOIN usuarios adm ON d.resolvido_por = adm.id
        ";

        if (!empty($statusFilter) && in_array($statusFilter, [REPORT_PENDING, REPORT_DISCARDED, REPORT_ACTION_TAKEN], true)) {
            $sql .= " WHERE d.status = ?";
            $params = [$statusFilter];
        } else {
            $params = [];
        }

        $sql .= " ORDER BY d.criado_em DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $reports = $stmt->fetchAll();

        // Carrega os detalhes do alvo denunciado (título da receita ou texto do comentário)
        foreach ($reports as &$rep) {
            if ($rep['tipo_alvo'] === 'receita') {
                $stmtRec = $this->db->prepare("SELECT id, titulo, status_moderacao FROM receitas WHERE id = ?");
                $stmtRec->execute([$rep['alvo_id']]);
                $rep['alvo_detalhes'] = $stmtRec->fetch();
            } else {
                $stmtCom = $this->db->prepare("SELECT id, texto, status FROM comentarios WHERE id = ?");
                $stmtCom->execute([$rep['alvo_id']]);
                $rep['alvo_detalhes'] = $stmtCom->fetch();
            }
        }

        require VIEWS_PATH . '/admin/reports.php';
    }

    /**
     * Resolve a denúncia aplicando remoção de conteúdo ou descarte (RF49, RF50, RF52)
     */
    public function resolveReport(): void {
        AuthMiddleware::requireAdmin();

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token de segurança inválido.');
            header('Location: /admin/denuncias');
            exit;
        }

        $denunciaId = (int)($_POST['denuncia_id'] ?? 0);
        $acao = trim($_POST['acao'] ?? '');
        $adminId = (int)Session::get('user_id');

        $stmt = $this->db->prepare("SELECT * FROM denuncias WHERE id = ?");
        $stmt->execute([$denunciaId]);
        $report = $stmt->fetch();

        if (!$report) {
            Session::flash('error', 'Denúncia não encontrada.');
            header('Location: /admin/denuncias');
            exit;
        }

        try {
            $this->db->beginTransaction();

            if ($acao === 'remover_conteudo') {
                // Remove o conteúdo infrator (Soft delete de moderação - RF49, RF50)
                if ($report['tipo_alvo'] === 'receita') {
                    $this->db->prepare("UPDATE receitas SET status_moderacao = 'removido' WHERE id = ?")
                             ->execute([$report['alvo_id']]);
                } elseif ($report['tipo_alvo'] === 'comentario') {
                    $this->db->prepare("UPDATE comentarios SET status = 'removido' WHERE id = ?")
                             ->execute([$report['alvo_id']]);
                }

                $stmtUp = $this->db->prepare("
                    UPDATE denuncias 
                    SET status = 'conteudo_removido', resolvido_por = ?, resolvido_em = NOW() 
                    WHERE id = ?
                ");
                $stmtUp->execute([$adminId, $denunciaId]);

                Session::flash('success', 'Conteúdo removido com sucesso e denúncia marcada como resolvida.');
            } elseif ($acao === 'descartar') {
                $stmtUp = $this->db->prepare("
                    UPDATE denuncias 
                    SET status = 'descartada', resolvido_por = ?, resolvido_em = NOW() 
                    WHERE id = ?
                ");
                $stmtUp->execute([$adminId, $denunciaId]);

                Session::flash('success', 'Denúncia descartada como improcedente.');
            } else {
                throw new Exception("Ação de moderação inválida.");
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erro na moderação: " . $e->getMessage());
            Session::flash('error', 'Falha ao processar a ação de moderação.');
        }

        header('Location: /admin/denuncias');
        exit;
    }
}
