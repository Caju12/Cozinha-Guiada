<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: src/Controllers/ProfileController.php
// DESCRIÇÃO: Gestão de perfil, preferências alimentares e transição para produtor
// ATENDE AOS REQUISITOS: RF06, RF07, RF30, CA06, CA07, CA30
// ==============================================================================

require_once CONFIG_PATH . '/constants.php';
require_once SRC_PATH . '/Services/AuthService.php';
require_once SRC_PATH . '/Middleware/AuthMiddleware.php';
require_once SRC_PATH . '/Utils/Session.php';

class ProfileController {
    private AuthService $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    /**
     * Exibe a tela de perfil do usuário logado (RF06, RF07)
     */
    public function showProfile(): void {
        AuthMiddleware::requireAuth();

        $user = $this->authService->getCurrentUser();
        $compatibilidades = $this->authService->getAllCompatibilidades();

        require VIEWS_PATH . '/profile/index.php';
    }

    /**
     * Salva as alterações de perfil e preferências (RF06, RF07 / CA06, CA07)
     */
    public function updateProfile(): void {
        AuthMiddleware::requireAuth();

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token de segurança expirado. Tente novamente.');
            header('Location: /perfil');
            exit;
        }

        $user = $this->authService->getCurrentUser();
        $nome = $_POST['nome'] ?? '';
        $bio = isset($_POST['bio']) ? $_POST['bio'] : null;
        $preferencias = isset($_POST['preferencias']) && is_array($_POST['preferencias'])
            ? $_POST['preferencias']
            : [];

        $result = $this->authService->updateProfile((int)$user['id'], $nome, $bio, $preferencias);

        if ($result['success']) {
            Session::flash('success', 'Perfil e preferências atualizados com sucesso!');
        } else {
            Session::flash('error', $result['error']);
        }

        header('Location: /perfil');
        exit;
    }

    /**
     * Processa a solicitação de tornar-se Produtor de Conteúdo (RF30 / CA30)
     */
    public function becomeProducer(): void {
        AuthMiddleware::requireAuth();

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token de segurança expirado. Tente novamente.');
            header('Location: /perfil');
            exit;
        }

        $user = $this->authService->getCurrentUser();
        $result = $this->authService->becomeProducer((int)$user['id']);

        if ($result['success']) {
            Session::flash('success', '🎉 Parabéns! Sua conta agora é de Produtor de Conteúdo. Você já pode publicar receitas!');
        } else {
            Session::flash('error', $result['error']);
        }

        header('Location: /perfil');
        exit;
    }
}
