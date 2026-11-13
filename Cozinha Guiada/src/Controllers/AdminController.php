<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: src/Controllers/AdminController.php
// DESCRIÇÃO: Painel administrativo, métricas globais e gestão de usuários
// ATENDE AOS REQUISITOS: RF46, RF47, RF48, RNF08, RNF09, CA46, CA47, CA48
// ==============================================================================

require_once CONFIG_PATH . '/constants.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/Middleware/AuthMiddleware.php';
require_once SRC_PATH . '/Utils/Session.php';

class AdminController {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Dashboard com visão geral e estatísticas operacionais (RF48)
     */
    public function dashboard(): void {
        AuthMiddleware::requireAdmin();

        // 1. Estatísticas gerais da plataforma
        $stats = [
            'total_usuarios'     => (int)$this->db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn(),
            'total_produtores'   => (int)$this->db->query("SELECT COUNT(*) FROM usuarios WHERE perfil = 'produtor'")->fetchColumn(),
            'total_receitas'     => (int)$this->db->query("SELECT COUNT(*) FROM receitas WHERE status_moderacao = 'ativo'")->fetchColumn(),
            'receitas_privadas'  => (int)$this->db->query("SELECT COUNT(*) FROM receitas WHERE visibilidade = 'privada'")->fetchColumn(),
            'denuncias_pendentes'=> (int)$this->db->query("SELECT COUNT(*) FROM denuncias WHERE status = 'pendente'")->fetchColumn(),
            'total_comentarios'  => (int)$this->db->query("SELECT COUNT(*) FROM comentarios WHERE status = 'ativo'")->fetchColumn()
        ];

        // 2. Últimas denúncias registradas para atenção rápida
        $stmtRep = $this->db->query("
            SELECT d.*, u.nome AS denunciante_nome 
            FROM denuncias d
            JOIN usuarios u ON d.usuario_id = u.id
            WHERE d.status = 'pendente'
            ORDER BY d.criado_em DESC
            LIMIT 5
        ");
        $ultimasDenuncias = $stmtRep->fetchAll();

        // 3. Usuários cadastrados recentemente
        $stmtUsers = $this->db->query("
            SELECT id, nome, email, perfil, ativo, criado_em 
            FROM usuarios 
            ORDER BY criado_em DESC 
            LIMIT 6
        ");
        $ultimosUsuarios = $stmtUsers->fetchAll();

        require VIEWS_PATH . '/admin/dashboard.php';
    }

    /**
     * Listagem completa e gestão de usuários (RF46, RF47)
     */
    public function usersList(): void {
        AuthMiddleware::requireAdmin();

        $search = trim($_GET['q'] ?? '');
        $roleFilter = trim($_GET['perfil'] ?? '');

        $sql = "SELECT id, nome, email, perfil, bio, ativo, criado_em FROM usuarios WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (nome LIKE ? OR email LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if (!empty($roleFilter) && in_array($roleFilter, [ROLE_CADASTRADO, ROLE_PRODUTOR, ROLE_ADMIN], true)) {
            $sql .= " AND perfil = ?";
            $params[] = $roleFilter;
        }

        $sql .= " ORDER BY criado_em DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        require VIEWS_PATH . '/admin/users.php';
    }

    /**
     * Alterna o status do usuário entre Ativo e Bloqueado/Desativado (RF46)
     * Contém proteção contra auto-bloqueio do último administrador (Prevenção de Lockout).
     */
    public function toggleUserStatus(): void {
        AuthMiddleware::requireAdmin();

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token de segurança inválido.');
            header('Location: /admin/usuarios');
            exit;
        }

        $targetUserId = (int)($_POST['id'] ?? 0);
        $currentAdminId = (int)Session::get('user_id');

        $stmt = $this->db->prepare("SELECT id, perfil, ativo FROM usuarios WHERE id = ?");
        $stmt->execute([$targetUserId]);
        $targetUser = $stmt->fetch();

        if (!$targetUser) {
            Session::flash('error', 'Usuário não encontrado.');
            header('Location: /admin/usuarios');
            exit;
        }

        // Prevenção de Lockout: se estiver tentando desativar um admin, verifica se há outros admins ativos
        if ((int)$targetUser['ativo'] === 1 && $targetUser['perfil'] === ROLE_ADMIN) {
            $stmtOtherAdmins = $this->db->prepare("
                SELECT COUNT(*) FROM usuarios 
                WHERE perfil = 'administrador' AND ativo = 1 AND id != ?
            ");
            $stmtOtherAdmins->execute([$targetUserId]);
            $otherAdminsCount = (int)$stmtOtherAdmins->fetchColumn();

            if ($otherAdminsCount === 0) {
                Session::flash('error', 'Operação bloqueada: não é permitido desativar o único administrador ativo da plataforma.');
                header('Location: /admin/usuarios');
                exit;
            }
        }

        $newStatus = (int)$targetUser['ativo'] === 1 ? 0 : 1;
        $stmtUp = $this->db->prepare("UPDATE usuarios SET ativo = ? WHERE id = ?");
        $stmtUp->execute([$newStatus, $targetUserId]);

        $msg = $newStatus === 1 ? 'Conta de usuário ativada com sucesso.' : 'Conta de usuário bloqueada/desativada com sucesso.';
        Session::flash('success', $msg);
        header('Location: /admin/usuarios');
        exit;
    }

    /**
     * Altera o papel / permissão do usuário no sistema (RF47)
     * Contém proteção para impedir que o último administrador rebaixe a si mesmo.
     */
    public function changeUserRole(): void {
        AuthMiddleware::requireAdmin();

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token de segurança inválido.');
            header('Location: /admin/usuarios');
            exit;
        }

        $targetUserId = (int)($_POST['id'] ?? 0);
        $newRole = trim($_POST['perfil'] ?? '');

        if (!in_array($newRole, [ROLE_CADASTRADO, ROLE_PRODUTOR, ROLE_ADMIN], true)) {
            Session::flash('error', 'Perfil selecionado inválido.');
            header('Location: /admin/usuarios');
            exit;
        }

        $stmt = $this->db->prepare("SELECT id, perfil FROM usuarios WHERE id = ?");
        $stmt->execute([$targetUserId]);
        $targetUser = $stmt->fetch();

        if (!$targetUser) {
            Session::flash('error', 'Usuário não encontrado.');
            header('Location: /admin/usuarios');
            exit;
        }

        // Se estiver rebaixando um administrador, checa se ainda restará outro admin ativo
        if ($targetUser['perfil'] === ROLE_ADMIN && $newRole !== ROLE_ADMIN) {
            $stmtOtherAdmins = $this->db->prepare("
                SELECT COUNT(*) FROM usuarios 
                WHERE perfil = 'administrador' AND ativo = 1 AND id != ?
            ");
            $stmtOtherAdmins->execute([$targetUserId]);
            if ((int)$stmtOtherAdmins->fetchColumn() === 0) {
                Session::flash('error', 'Operação bloqueada: este é o único administrador ativo no sistema e não pode ser rebaixado.');
                header('Location: /admin/usuarios');
                exit;
            }
        }

        $stmtUp = $this->db->prepare("UPDATE usuarios SET perfil = ? WHERE id = ?");
        $stmtUp->execute([$newRole, $targetUserId]);

        // Se o usuário alterou seu próprio perfil, atualiza na sessão
        if ($targetUserId === (int)Session::get('user_id')) {
            Session::set('user_role', $newRole);
        }

        Session::flash('success', "Perfil atualizado para '{$newRole}' com sucesso.");
        header('Location: /admin/usuarios');
        exit;
    }
}
