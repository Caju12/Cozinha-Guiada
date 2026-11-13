<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: config/database.php
// DESCRIÇÃO: Conexão segura com banco de dados via PDO (Padrão Singleton)
// ATENDE AOS REQUISITOS: RNF05, RNF11, RNF12, RNF14
// ==============================================================================

require_once __DIR__ . DIRECTORY_SEPARATOR . 'constants.php';

class Database {
    private static ?PDO $instance = null;

    /**
     * Construtor privado para impedir instanciação direta (Padrão Singleton)
     */
    private function __construct() {}

    /**
     * Previne clonagem do objeto
     */
    private function __clone() {}

    /**
     * Retorna a instância única e ativa do PDO
     *
     * @return PDO
     * @throws Exception Em caso de falha de conexão, sem vazar senhas (RNF12)
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $host = env('DB_HOST', '127.0.0.1');
            $port = env('DB_PORT', '3306');
            $db   = env('DB_NAME', 'cozinha_guiada');
            $user = env('DB_USER', 'root');
            $pass = env('DB_PASS', '');

            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

            $options = [
                // Lança exceções PDO em caso de falhas na query
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                // Retorna os resultados como arrays associativos por padrão
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Desativa a emulação de prepared statements (segurança máxima contra SQL Injection - RNF11)
                PDO::ATTR_EMULATE_PREPARES   => false,
                // Garante a codificação UTF-8 completa com suporte a acentuação e caracteres especiais
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // RNF12: Não exibe mensagens técnicas de erro, senhas ou host para o usuário final
                error_log("Erro de Conexão com o Banco de Dados [Cozinha Guiada]: " . $e->getMessage());
                throw new Exception("Não foi possível conectar ao banco de dados. Verifique as configurações no arquivo .env.");
            }
        }

        return self::$instance;
    }
}
