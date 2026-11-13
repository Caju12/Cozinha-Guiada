<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: src/Controllers/AuthController.php
// DESCRIÇÃO: Controlador de login, cadastro e logout (RF04, RF05)
// ==============================================================================

require_once CONFIG_PATH . '/constants.php';
require_once SRC_PATH . '/Services/AuthService.php';
require_once SRC_PATH . '/Middleware/AuthMiddleware.php';
require_once SRC_PATH . '/Utils/Session.php';

class AuthController {
    private AuthService $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    /**
     * Exibe o formulário de login (RF05)
     */
    public function showLogin(): void {
        AuthMiddleware::requireGuest();
        require_once VIEWS_PATH . '/auth/login.php';
    }

    /**
     * Processa a autenticação do usuário (RF05, RNF07)
     */
    public function login(): void {
        AuthMiddleware::requireGuest();

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token de segurança expirado. Tente novamente.');
            header('Location: /login');
            exit;
        }

        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        $result = $this->authService->login($email, $senha);

        if ($result['success']) {
            Session::flash('success', 'Bem-vindo(a) de volta, ' . $result['user']['nome'] . '!');
            header('Location: /perfil');
            exit;
        }

        Session::flash('error', $result['error']);
        header('Location: /login');
        exit;
    }

    /**
     * Exibe o formulário de cadastro (RF04, RF07)
     */
    public function showRegister(): void {
        AuthMiddleware::requireGuest();
        $compatibilidades = $this->authService->getAllCompatibilidades();
        require_once VIEWS_PATH . '/auth/register.php';
    }

    /**
     * Processa o cadastro de novos usuários (RF04, CA04)
     */
    public function register(): void {
        AuthMiddleware::requireGuest();

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token de segurança expirado. Tente novamente.');
            header('Location: /cadastro');
            exit;
        }

        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $senhaConfirm = $_POST['senha_confirm'] ?? '';
        $preferencias = isset($_POST['preferencias']) && is_array($_POST['preferencias'])
            ? $_POST['preferencias']
            : [];

        if ($senha !== $senhaConfirm) {
            Session::flash('error', 'A confirmação de senha não confere.');
            header('Location: /cadastro');
            exit;
        }

        $result = $this->authService->register($nome, $email, $senha, $preferencias);

        if ($result['success']) {
            Session::flash('success', 'Cadastro realizado com sucesso! Entre com seu e-mail e senha.');
            header('Location: /login');
            exit;
        }

        Session::flash('error', $result['error']);
        header('Location: /cadastro');
        exit;
    }

    /**
     * Processa o encerramento da sessão (Logout / RF05)
     */
    public function logout(): void {
        AuthMiddleware::requireAuth();
        $this->authService->logout();
        Session::flash('success', 'Você saiu com segurança.');
        header('Location: /login');
        exit;
    }
}
