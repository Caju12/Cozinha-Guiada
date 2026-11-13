<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: src/Controllers/HomeController.php
// DESCRIÇÃO: Página inicial e visão geral da plataforma
// ==============================================================================

require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/constants.php';
require_once SRC_PATH . '/Utils/Session.php';

class HomeController {
    public function index(): void {
        $db = Database::getConnection();

        // Busca receitas públicas em destaque
        $stmt = $db->query("
            SELECT r.id, r.titulo, r.descricao, r.tempo_preparo_minutos, r.rendimento, u.nome AS autor_nome
            FROM receitas r
            JOIN usuarios u ON r.usuario_id = u.id
            WHERE r.visibilidade = 'publica' AND r.status_moderacao = 'ativo'
            ORDER BY r.criado_em DESC
            LIMIT 6
        ");
        $destaques = $stmt->fetchAll();

        // Categorias para atalho rápido
        $stmtCat = $db->query("SELECT id, nome, slug FROM categorias ORDER BY nome ASC");
        $categorias = $stmtCat->fetchAll();

        require_once VIEWS_PATH . '/home/index.php';
    }
}
