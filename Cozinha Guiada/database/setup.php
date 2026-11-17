<?php
// Inicializa o banco PostgreSQL já criado no Supabase.
// Execute apenas em ambiente controlado após configurar DB_*.
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/config/database.php';

try {
    $pdo = Database::getConnection();
    foreach (['schema.sql', 'seed.sql'] as $file) {
        $path = __DIR__ . DIRECTORY_SEPARATOR . $file;
        if (!is_file($path)) {
            throw new RuntimeException("Arquivo não encontrado: {$file}");
        }
        $pdo->exec(file_get_contents($path));
        echo "{$file} executado com sucesso.\n";
    }
    echo "Banco Cozinha Guiada inicializado com sucesso.\n";
} catch (Throwable $e) {
    error_log("Falha na inicialização do banco: " . $e->getMessage());
    fwrite(STDERR, "Falha na inicialização. Consulte o log para detalhes.\n");
    exit(1);
}
