<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: src/Utils/Session.php
// DESCRIÇÃO: Gerenciamento seguro de sessões e mensagens flash (RNF07, RNF10)
// ==============================================================================

class Session {
    /**
     * Inicia a sessão com configurações seguras de cookies se ainda não estiver ativa
     */
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            // Apenas define parâmetros de cookie se os cabeçalhos HTTP ainda não tiverem sido enviados
            if (!headers_sent()) {
                $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

                session_set_cookie_params([
                    'lifetime' => 0, // Sessão válida até fechar o navegador
                    'path'     => '/',
                    'domain'   => '',
                    'secure'   => $isSecure,
                    'httponly' => true,      // Previne acesso via JavaScript (anti-XSS)
                    'samesite' => 'Lax'      // Mitigação contra CSRF
                ]);
            }

            if (!headers_sent() || php_sapi_name() === 'cli') {
                @session_start();
            }
        }
    }

    /**
     * Define uma variável na sessão
     */
    public static function set(string $key, mixed $value): void {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Obtém uma variável da sessão
     */
    public static function get(string $key, mixed $default = null): mixed {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Verifica se uma chave existe na sessão
     */
    public static function has(string $key): bool {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove uma chave da sessão
     */
    public static function remove(string $key): void {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Define uma mensagem flash (exibida apenas na próxima requisição)
     */
    public static function flash(string $type, string $message): void {
        self::start();
        $_SESSION['flash_messages'][$type][] = $message;
    }

    /**
     * Recupera e consome todas as mensagens flash pendentes
     */
    public static function getFlashes(): array {
        self::start();
        $flashes = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $flashes;
    }

    /**
     * Gera ou recupera o token CSRF da sessão atual (RNF10)
     */
    public static function csrfToken(): string {
        self::start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Valida um token CSRF recebido na requisição POST
     */
    public static function validateCsrf(?string $token): bool {
        self::start();
        $stored = $_SESSION['csrf_token'] ?? '';
        return !empty($token) && hash_equals($stored, $token);
    }

    /**
     * Encerra e destrói completamente a sessão do usuário
     */
    public static function destroy(): void {
        self::start();
        $_SESSION = [];

        if (!headers_sent() && ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}
