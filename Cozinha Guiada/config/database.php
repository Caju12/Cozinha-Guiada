<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: config/database.php
// DESCRIÇÃO: Conexão segura e inteligente via PDO com suporte a PostgreSQL 
//            (Supabase / Vercel) e MySQL/MariaDB (XAMPP local).
// ==============================================================================

require_once __DIR__ . DIRECTORY_SEPARATOR . 'constants.php';

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            // 1. Prioriza DATABASE_URL ou POSTGRES_URL (padrão de integração Vercel / Supabase / Neon / Render)
            $dbUrl = env('DATABASE_URL') ?: env('POSTGRES_URL') ?: env('SUPABASE_DB_URL');

            if (!empty($dbUrl)) {
                $parsed = parse_url($dbUrl);
                $scheme = strtolower((string)($parsed['scheme'] ?? ''));
                $driver = ($scheme === 'mysql' || $scheme === 'mariadb') ? 'mysql' : 'pgsql';
                $host   = $parsed['host'] ?? '127.0.0.1';
                $port   = isset($parsed['port']) ? (int)$parsed['port'] : ($driver === 'pgsql' ? 5432 : 3306);
                $name   = isset($parsed['path']) ? ltrim($parsed['path'], '/') : ($driver === 'pgsql' ? 'postgres' : 'cozinha_guiada');
                $user   = isset($parsed['user']) ? urldecode($parsed['user']) : ($driver === 'pgsql' ? 'postgres' : 'root');
                $pass   = isset($parsed['pass']) ? urldecode($parsed['pass']) : '';
            } else {
                // 2. Fallback para variáveis individuais (DB_HOST, DB_PORT, etc.)
                $host = env('DB_HOST', '127.0.0.1');
                $port = (int)env('DB_PORT', 5432);
                $name = env('DB_NAME', 'postgres');
                $user = env('DB_USER', 'postgres');
                $pass = env('DB_PASS', '');

                $driverEnv = strtolower((string)env('DB_DRIVER', ''));
                if ($driverEnv === 'mysql' || $port === 3306) {
                    $driver = 'mysql';
                } else {
                    $driver = 'pgsql';
                }
            }

            // 3. Monta o DSN e opções conforme o driver
            if ($driver === 'pgsql') {
                $sslMode = env('DB_SSLMODE');
                if (!$sslMode && !empty($parsed['query'])) {
                    parse_str($parsed['query'], $queryParams);
                    if (!empty($queryParams['sslmode'])) {
                        $sslMode = $queryParams['sslmode'];
                    }
                }
                if (!$sslMode) {
                    $sslMode = ($host === '127.0.0.1' || $host === 'localhost') ? 'disable' : 'require';
                }

                $dsn = "pgsql:host={$host};port={$port};dbname={$name};sslmode={$sslMode}";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_TIMEOUT            => 10,
                ];
            } else {
                $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ];
            }

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                error_log("Erro de conexão com o banco de dados [Cozinha Guiada]: " . $e->getMessage());

                $isDev = (env('APP_DEBUG') === 'true' || env('APP_DEBUG') === true || env('APP_ENV') === 'development');
                $msg = "Não foi possível conectar ao banco de dados.";
                if ($isDev) {
                    $msg .= " Detalhes: " . $e->getMessage() . " (Host: {$host}:{$port}, Banco: {$name}, Usuário: {$user}, Driver: {$driver})";
                } else {
                    $msg .= " Verifique se as variáveis de conexão (DATABASE_URL ou DB_HOST/DB_USER/DB_PASS) estão configuradas no Vercel e se o Supabase Pooler está ativo.";
                }

                throw new Exception($msg, (int)$e->getCode(), $e);
            }
        }
        return self::$instance;
    }
}
