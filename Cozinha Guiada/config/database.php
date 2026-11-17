<?php
// Conexão PDO com PostgreSQL (Supabase).
require_once __DIR__ . DIRECTORY_SEPARATOR . 'constants.php';

class Database {
    private static ?PDO $instance = null;
    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $host = env('DB_HOST', '127.0.0.1');
            $port = env('DB_PORT', '5432');
            $name = env('DB_NAME', 'postgres');
            $user = env('DB_USER', 'postgres');
            $pass = env('DB_PASS', '');

            $dsn = "pgsql:host={$host};port={$port};dbname={$name};sslmode=require";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                error_log("Erro de conexão PostgreSQL [Cozinha Guiada]: " . $e->getMessage());
                throw new Exception("Não foi possível conectar ao banco de dados. Verifique as configurações do Supabase.");
            }
        }
        return self::$instance;
    }
}
