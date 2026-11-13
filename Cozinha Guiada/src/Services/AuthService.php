<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: src/Services/AuthService.php
// DESCRIÇÃO: Lógica central de autenticação, RBAC e gestão de perfil
// ATENDE AOS REQUISITOS: RF04, RF05, RF06, RF07, RF30, RNF06, RNF07, RNF08, RNF09, RNF13
// ==============================================================================

require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/constants.php';
require_once SRC_PATH . '/Utils/Session.php';

class AuthService {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Cadastra um novo usuário no sistema (RF04 / CA04).
     * Garante que o perfil atribuído seja SEMPRE 'cadastrado', protegendo a conta de admin (RNF06).
     */
    public function register(string $nome, string $email, string $senha, array $preferencias = []): array {
        $nome = trim($nome);
        $email = trim(filter_var($email, FILTER_SANITIZE_EMAIL));

        if (empty($nome)) {
            return ['success' => false, 'error' => 'O nome completo é obrigatório.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Por favor, informe um endereço de e-mail válido.'];
        }

        if (mb_strlen($senha) < 6) {
            return ['success' => false, 'error' => 'A senha deve conter no mínimo 6 caracteres.'];
        }

        // Verifica duplicidade de e-mail
        $stmtCheck = $this->db->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
        $stmtCheck->execute([$email]);
        if ($stmtCheck->fetch()) {
            return ['success' => false, 'error' => 'Este e-mail já está cadastrado em nossa base.'];
        }

        // Criptografia segura da senha (RNF06)
        $senhaHash = password_hash($senha, PASSWORD_BCRYPT);

        try {
            $this->db->beginTransaction();

            // Perfil forçado como 'cadastrado' para impedir autoatribuição de 'administrador'
            $stmt = $this->db->prepare("
                INSERT INTO usuarios (nome, email, senha, perfil, ativo)
                VALUES (?, ?, ?, ?, 1)
            ");
            $stmt->execute([$nome, $email, $senhaHash, ROLE_CADASTRADO]);
            $userId = (int) $this->db->lastInsertId();

            // Salva preferências alimentares selecionadas (RF07 / CA07)
            if (!empty($preferencias)) {
                $this->saveUserPreferences($userId, $preferencias);
            }

            $this->db->commit();
            return ['success' => true, 'user_id' => $userId];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao registrar usuário: " . $e->getMessage());
            return ['success' => false, 'error' => 'Ocorreu um erro ao processar o cadastro. Tente novamente.'];
        }
    }

    /**
     * Autentica um usuário existente (RF05 / CA05 / RNF07).
     */
    public function login(string $email, string $senha): array {
        $email = trim(filter_var($email, FILTER_SANITIZE_EMAIL));

        if (empty($email) || empty($senha)) {
            return ['success' => false, 'error' => 'Informe o e-mail e a senha para entrar.'];
        }

        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'error' => 'E-mail ou senha incorretos.'];
        }

        // Verifica se a conta está ativa (RF46)
        if ((int)$user['ativo'] !== 1) {
            return ['success' => false, 'error' => 'Sua conta está desativada ou suspensa. Contate o suporte.'];
        }

        // Validação da senha criptografada (RNF06)
        if (!password_verify($senha, $user['senha'])) {
            return ['success' => false, 'error' => 'E-mail ou senha incorretos.'];
        }

        // Regenera o ID de sessão para prevenir sequestro de sessão (Session Fixation)
        Session::start();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        // Armazena na sessão
        Session::set('user_id', (int)$user['id']);
        Session::set('user_name', $user['nome']);
        Session::set('user_email', $user['email']);
        Session::set('user_role', $user['perfil']);

        return ['success' => true, 'user' => $user];
    }

    /**
     * Encerra a sessão do usuário (Logout / RF05).
     */
    public function logout(): void {
        Session::destroy();
    }

    /**
     * Retorna os dados completos do usuário logado atualmente (ou null se visitante).
     */
    public function getCurrentUser(): ?array {
        $userId = Session::get('user_id');
        if (!$userId) {
            return null;
        }

        $stmt = $this->db->prepare("SELECT id, nome, email, perfil, bio, ativo, criado_em FROM usuarios WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user || (int)$user['ativo'] !== 1) {
            Session::destroy();
            return null;
        }

        // Carrega as preferências alimentares
        $stmtPref = $this->db->prepare("
            SELECT compatibilidade_id 
            FROM usuario_preferencias 
            WHERE usuario_id = ?
        ");
        $stmtPref->execute([$userId]);
        $user['preferencias'] = $stmtPref->fetchAll(PDO::FETCH_COLUMN);

        return $user;
    }

    /**
     * Atualiza dados de perfil e preferências do usuário (RF06, RF07 / CA06, CA07).
     */
    public function updateProfile(int $userId, string $nome, ?string $bio, array $preferencias = []): array {
        $nome = trim($nome);
        $bio = $bio !== null ? trim($bio) : null;

        if (empty($nome)) {
            return ['success' => false, 'error' => 'O nome não pode ficar em branco.'];
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("UPDATE usuarios SET nome = ?, bio = ? WHERE id = ?");
            $stmt->execute([$nome, $bio, $userId]);

            $this->saveUserPreferences($userId, $preferencias);

            $this->db->commit();

            // Atualiza nome na sessão
            Session::set('user_name', $nome);

            return ['success' => true];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao atualizar perfil: " . $e->getMessage());
            return ['success' => false, 'error' => 'Não foi possível atualizar o perfil. Tente novamente.'];
        }
    }

    /**
     * Permite que um usuário comum solicite/torne-se Produtor de Conteúdo (RF30 / CA30).
     */
    public function becomeProducer(int $userId): array {
        $user = $this->getCurrentUser();
        if (!$user || (int)$user['id'] !== $userId) {
            return ['success' => false, 'error' => 'Ação não permitida.'];
        }

        if ($user['perfil'] === ROLE_ADMIN) {
            return ['success' => false, 'error' => 'Você já possui perfil de Administrador.'];
        }

        if ($user['perfil'] === ROLE_PRODUTOR) {
            return ['success' => false, 'error' => 'Você já é um Produtor de Conteúdo!'];
        }

        $stmt = $this->db->prepare("UPDATE usuarios SET perfil = ? WHERE id = ?");
        $stmt->execute([ROLE_PRODUTOR, $userId]);

        Session::set('user_role', ROLE_PRODUTOR);

        return ['success' => true];
    }

    /**
     * Retorna todas as opções de compatibilidade alimentar cadastradas no sistema (RF07, RNF19).
     */
    public function getAllCompatibilidades(): array {
        $stmt = $this->db->query("SELECT id, nome, slug, descricao FROM compatibilidades_alimentares ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    /**
     * Salva as preferências alimentares do usuário
     */
    private function saveUserPreferences(int $userId, array $preferencias): void {
        // Remove preferências antigas
        $stmtDel = $this->db->prepare("DELETE FROM usuario_preferencias WHERE usuario_id = ?");
        $stmtDel->execute([$userId]);

        if (empty($preferencias)) {
            return;
        }

        $stmtIns = $this->db->prepare("INSERT INTO usuario_preferencias (usuario_id, compatibilidade_id) VALUES (?, ?)");
        foreach ($preferencias as $prefId) {
            $prefId = (int)$prefId;
            if ($prefId > 0) {
                $stmtIns->execute([$userId, $prefId]);
            }
        }
    }
}
