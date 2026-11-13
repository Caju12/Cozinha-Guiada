<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: src/Middleware/AuthMiddleware.php
// DESCRIÇÃO: Controle de acesso baseado em papéis (RBAC - RNF08, RNF09)
// ==============================================================================

require_once CONFIG_PATH . '/constants.php';
require_once SRC_PATH . '/Utils/Session.php';

class AuthMiddleware {
    /**
     * Verifica se o usuário atual está autenticado
     */
    public static function isLoggedIn(): bool {
        return Session::has('user_id');
    }

    /**
     * Retorna a role/perfil do usuário atual ou 'visitante'
     */
    public static function getRole(): string {
        return Session::get('user_role', ROLE_VISITANTE);
    }

    /**
     * Exige que o usuário seja visitante (redireciona para o perfil se já estiver logado)
     */
    public static function requireGuest(string $redirectTo = '/perfil'): void {
        if (self::isLoggedIn()) {
            header("Location: {$redirectTo}");
            exit;
        }
    }

    /**
     * Exige que o usuário esteja autenticado (RF05, RNF08)
     */
    public static function requireAuth(string $redirectTo = '/login'): void {
        if (!self::isLoggedIn()) {
            Session::flash('error', 'Você precisa estar conectado para acessar esta área.');
            header("Location: {$redirectTo}");
            exit;
        }
    }

    /**
     * Exige um ou mais perfis específicos (RBAC - RNF08, RNF09)
     *
     * @param string|array $roles Perfis aceitos (ex: ROLE_PRODUTOR, ROLE_ADMIN)
     */
    public static function requireRole(string|array $roles, string $redirectFallback = '/'): void {
        self::requireAuth();

        $allowedRoles = is_array($roles) ? $roles : [$roles];
        $currentRole = self::getRole();

        // Se for admin, concede acesso a quase tudo
        if ($currentRole === ROLE_ADMIN) {
            return;
        }

        if (!in_array($currentRole, $allowedRoles, true)) {
            Session::flash('error', 'Você não tem permissão para acessar esta área.');
            http_response_code(403);
            header("Location: {$redirectFallback}");
            exit;
        }
    }

    /**
     * Exige perfil de Produtor de Conteúdo ou Administrador (RF31 a RF45)
     */
    public static function requireProducer(): void {
        self::requireRole([ROLE_PRODUTOR, ROLE_ADMIN], '/perfil');
    }

    /**
     * Exige perfil de Administrador estrito (RF46 a RF52)
     */
    public static function requireAdmin(): void {
        self::requireRole(ROLE_ADMIN, '/');
    }
}
