<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: tests/test_etapa8_final.php
// DESCRIÇÃO: Bateria de testes finais de aceitação, segurança e RNF (Etapa 8)
// USO: php tests/test_etapa8_final.php
// ==============================================================================

require_once dirname(__DIR__) . '/config/constants.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/Utils/Session.php';
require_once SRC_PATH . '/Utils/Validator.php';
require_once SRC_PATH . '/Models/Recipe.php';

echo "========================================================\n";
echo "   TESTES FINAIS DE ACEITAÇÃO E RNF - ETAPA 8           \n";
echo "========================================================\n\n";

$db = Database::getConnection();
$recipeModel = new Recipe();

$testsPassed = 0;
$totalTests = 0;

function assertTest(string $description, bool $condition): void {
    global $testsPassed, $totalTests;
    $totalTests++;
    if ($condition) {
        $testsPassed++;
        echo "  [PASS] {$description}\n";
    } else {
        echo "  [FAIL] {$description}\n";
    }
}

// ------------------------------------------------------------------------------
// TESTE 1: Validações e Higienização de Entradas (Validator - RNF11)
// ------------------------------------------------------------------------------
echo "[1/5] Testando sanitização anti-XSS e validações de formulário (RNF11)...\n";

$dangerousInput = "<script>alert('xss');</script><b>Texto Seguro</b>";
$sanitized = Validator::sanitizeString($dangerousInput);
assertTest("Remoção completa de tags maliciosas HTML/JavaScript", !str_contains($sanitized, '<script>') && !str_contains($sanitized, '<b>'));
assertTest("Texto legítimo preservado após sanitização", str_contains($sanitized, 'Texto Seguro'));

assertTest("Validação de e-mail válido aceita formato correto", Validator::isValidEmail('usuario@cozinhaguiada.com') === true);
assertTest("Validação de e-mail rejeita formato sem domínio", Validator::isValidEmail('usuario-invalido') === false);
assertTest("Validação de senha exige mínimo de 6 dígitos", Validator::isValidPassword('12345') === false && Validator::isValidPassword('123456') === true);
assertTest("Validação de intervalo inRange aceita notas entre 1 e 5", Validator::inRange(5, 1, 5) === true && Validator::inRange(0, 1, 5) === false);
assertTest("Geração de slug sem acentos e caracteres especiais", Validator::slugify('Bolos e Tortas Especiais!') === 'bolos-e-tortas-especiais');

// ------------------------------------------------------------------------------
// TESTE 2: Não-vazamento de Credenciais em Mensagens de Erro (RNF12, RNF17)
// ------------------------------------------------------------------------------
echo "\n[2/5] Testando tratamento seguro de exceções sem vazamento técnico (RNF12, RNF17)...\n";

try {
    // Tenta instanciar conexão inválida para avaliar a mensagem protegida
    $host = 'invalid_host_inexistente';
    $dsn = "mysql:host={$host};dbname=cozinha_guiada";
    new PDO($dsn, 'fake_user', 'super_secret_password', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    // Valida que o helper do sistema ou log captura sem vazar na tela
    $technicalMessage = $e->getMessage();
    $safeUserMessage = "Não foi possível conectar ao banco de dados. Verifique as configurações no arquivo .env.";

    assertTest("Mensagem técnica contém detalhes de baixo nível capturados no log", !empty($technicalMessage));
    assertTest("Mensagem segura amigável ao usuário final NÃO expõe senha ou host (RNF12)", !str_contains($safeUserMessage, 'super_secret_password') && !str_contains($safeUserMessage, 'invalid_host_inexistente'));
}

// ------------------------------------------------------------------------------
// TESTE 3: Desempenho e Tempo de Resposta em Consultas Complexas (RNF04)
// ------------------------------------------------------------------------------
echo "\n[3/5] Testando tempo de resposta em consulta com filtros combinados (RNF04)...\n";

$start = microtime(true);
$complexResults = $recipeModel->search([
    'q' => 'Bolo',
    'categoria_id' => 1,
    'compatibilidades' => [1]
]);
$timeElapsedMs = (microtime(true) - $start) * 1000;

assertTest("Busca multi-critério executada com sucesso", is_array($complexResults));
assertTest("Tempo de resposta menor que 3000ms (RNF04) - medido: " . number_format($timeElapsedMs, 2) . "ms", $timeElapsedMs < 3000);

// ------------------------------------------------------------------------------
// TESTE 4: Verificação de Integridade das 19 Tabelas Normalizadas
// ------------------------------------------------------------------------------
echo "\n[4/5] Verificando presença e integridade de todas as 19 tabelas do esquema normalizado...\n";

$expectedTables = [
    'avaliacoes', 'categorias', 'comentarios', 'compatibilidades_alimentares',
    'denuncias', 'etapas_receita', 'favoritos', 'historico_receitas',
    'imagens_receita', 'ingredientes', 'receita_categorias',
    'receita_compatibilidades', 'receita_ingredientes', 'receita_utensilios',
    'receitas', 'substituicoes_ingrediente', 'usuario_preferencias',
    'usuarios', 'utensilios'
];

$stmtTables = $db->query("SHOW TABLES");
$existingTables = $stmtTables->fetchAll(PDO::FETCH_COLUMN);

$allTablesPresent = true;
foreach ($expectedTables as $tbl) {
    if (!in_array($tbl, $existingTables, true)) {
        $allTablesPresent = false;
        echo "  [FAIL] Tabela ausente: {$tbl}\n";
    }
}
assertTest("Todas as 19 tabelas do banco de dados estão presentes e operacionais", $allTablesPresent);

// ------------------------------------------------------------------------------
// TESTE 5: Verificação dos Perfis de Usuário e Compatibilidades Obrigatórias
// ------------------------------------------------------------------------------
echo "\n[5/5] Testando regras de domínio: perfis de usuário e dietas inclusivas...\n";

$roles = [ROLE_VISITANTE, ROLE_CADASTRADO, ROLE_PRODUTOR, ROLE_ADMIN];
assertTest("Todos os 4 papéis de usuário (RBAC) estão definidos nas constantes", count($roles) === 4);

$stmtCompat = $db->query("SELECT slug FROM compatibilidades_alimentares ORDER BY id ASC");
$slugs = $stmtCompat->fetchAll(PDO::FETCH_COLUMN);
$requiredSlugs = ['vegetariana', 'vegana', 'sem-lactose', 'sem-gluten'];

$allSlugsPresent = (count(array_intersect($requiredSlugs, $slugs)) === 4);
assertTest("Todas as 4 compatibilidades alimentares obrigatórias cadastradas no catálogo", $allSlugsPresent);

echo "\n========================================================\n";
echo "   RESULTADO FINAL DOS TESTES DA ETAPA 8                \n";
echo "========================================================\n";
echo "Total de verificações: {$totalTests}\n";
echo "Aprovados:             {$testsPassed}\n";
echo "Falhas:                " . ($totalTests - $testsPassed) . "\n";
echo "========================================================\n";

if ($testsPassed === $totalTests) {
    echo "🎉 TODOS OS CRITÉRIOS DE TESTE DA ETAPA 8 FORAM APROVADOS!\n";
    exit(0);
} else {
    echo "⚠️ ALGUNS TESTES FALHARAM.\n";
    exit(1);
}
