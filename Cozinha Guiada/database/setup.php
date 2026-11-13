<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: database/setup.php
// DESCRIÇÃO: Script CLI para inicializar o banco de dados (schema + seed)
// USO: php database/setup.php
// ==============================================================================

require_once dirname(__DIR__) . '/config/constants.php';

echo "========================================================\n";
echo "   INICIALIZAÇÃO DO BANCO DE DADOS - COZINHA GUIADA     \n";
echo "========================================================\n\n";

$host = env('DB_HOST', '127.0.0.1');
$port = env('DB_PORT', '3306');
$db   = env('DB_NAME', 'cozinha_guiada');
$user = env('DB_USER', 'root');
$pass = env('DB_PASS', '');

echo "[1/4] Tentando conectar ao servidor MySQL ({$host}:{$port}) como '{$user}'...\n";

try {
    // Conecta sem selecionar DB inicialmente para poder criá-lo
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "  -> Conexão estabelecida com sucesso!\n";
} catch (PDOException $e) {
    echo "\n[ERRO CRÍTICO] Falha ao conectar ao MySQL: " . $e->getMessage() . "\n";
    echo "Verifique as configurações no arquivo .env (especialmente DB_USER e DB_PASS).\n";
    exit(1);
}

// 2. Executar schema.sql
echo "\n[2/4] Executando 'database/schema.sql' para criar tabelas e estrutura...\n";
$schemaFile = __DIR__ . '/schema.sql';
if (!file_exists($schemaFile)) {
    echo "  -> [ERRO] Arquivo schema.sql não encontrado!\n";
    exit(1);
}

$schemaSql = file_get_contents($schemaFile);

try {
    $pdo->exec($schemaSql);
    echo "  -> Esquema criado com sucesso no banco '{$db}'!\n";
} catch (PDOException $e) {
    echo "  -> [ERRO] Falha ao executar schema.sql: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Executar seed.sql
echo "\n[3/4] Executando 'database/seed.sql' para carregar dados iniciais...\n";
$seedFile = __DIR__ . '/seed.sql';
if (!file_exists($seedFile)) {
    echo "  -> [ERRO] Arquivo seed.sql não encontrado!\n";
    exit(1);
}

$seedSql = file_get_contents($seedFile);

try {
    $pdo->exec($seedSql);
    echo "  -> Carga inicial de dados concluída com sucesso!\n";
} catch (PDOException $e) {
    echo "  -> [ERRO] Falha ao executar seed.sql: " . $e->getMessage() . "\n";
    exit(1);
}

// 4. Teste de verificação da conexão Singleton
echo "\n[4/4] Testando a classe de conexão Database::getConnection()...\n";
require_once dirname(__DIR__) . '/config/database.php';

try {
    $dbTest = Database::getConnection();
    $stmt = $dbTest->query("SELECT COUNT(*) AS total_usuarios FROM usuarios");
    $row = $stmt->fetch();
    echo "  -> Sucesso! Total de usuários cadastrados: " . $row['total_usuarios'] . "\n";
} catch (Exception $e) {
    echo "  -> [ERRO] " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n========================================================\n";
echo "   ETAPA 1 FINALIZADA COM ÊXITO!                        \n";
echo "========================================================\n";
echo "Credenciais padrão criadas para teste:\n";
echo "  * Admin:     admin@cozinhaguiada.com     | Senha: Admin@2026\n";
echo "  * Produtor:  chef@cozinhaguiada.com      | Senha: Admin@2026\n";
echo "  * Leitor:    leitor@cozinhaguiada.com    | Senha: Admin@2026\n";
echo "========================================================\n";
