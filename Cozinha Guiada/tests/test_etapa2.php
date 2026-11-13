<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: database/test_etapa2.php
// DESCRIÇÃO: Bateria de testes automatizados da Etapa 2 (Autenticação e RBAC)
// USO: php database/test_etapa2.php
// ==============================================================================

require_once dirname(__DIR__) . '/config/constants.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/Utils/Session.php';
require_once SRC_PATH . '/Services/AuthService.php';
require_once SRC_PATH . '/Middleware/AuthMiddleware.php';

echo "========================================================\n";
echo "   TESTES AUTOMATIZADOS - ETAPA 2 (AUTENTICAÇÃO & RBAC)  \n";
echo "========================================================\n\n";

$authService = new AuthService();
$db = Database::getConnection();

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
// TESTE 1: Tentativa de login com senha incorreta
// ------------------------------------------------------------------------------
echo "[1/7] Testando validação de senha incorreta no login (RNF06)...\n";
$loginFail = $authService->login('admin@cozinhaguiada.com', 'SenhaIncorreta!123');
assertTest("Login com senha incorreta é rejeitado", $loginFail['success'] === false);

// ------------------------------------------------------------------------------
// TESTE 2: Login com credenciais válidas e hash Bcrypt
// ------------------------------------------------------------------------------
echo "\n[2/7] Testando login com credenciais válidas (Admin@2026)...\n";
$loginOk = $authService->login('chef@cozinhaguiada.com', 'Admin@2026');
assertTest("Login com senha válida é aceito", $loginOk['success'] === true);
assertTest("Sessão armazena perfil correto ('produtor')", Session::get('user_role') === ROLE_PRODUTOR);

// ------------------------------------------------------------------------------
// TESTE 3: Cadastro de novo usuário e atribuição estrita de perfil 'cadastrado' (RF04, CA04)
// ------------------------------------------------------------------------------
echo "\n[3/7] Testando cadastro de novo usuário com preferências (RF04, RF07)...\n";
$randomEmail = 'teste_' . time() . '@exemplo.com';
$regResult = $authService->register('Usuário de Teste QA', $randomEmail, 'Senha@123456', [1, 3]); // Veg e Sem Lactose

assertTest("Cadastro de novo usuário concluído com sucesso", $regResult['success'] === true);

if ($regResult['success']) {
    $newUserId = $regResult['user_id'];
    
    // Verifica no banco se perfil é estritamente 'cadastrado'
    $stmt = $db->prepare("SELECT perfil FROM usuarios WHERE id = ?");
    $stmt->execute([$newUserId]);
    $perfilDb = $stmt->fetchColumn();
    assertTest("Perfil atribuído é estritamente 'cadastrado' (prevenção de elevação de privilégio)", $perfilDb === ROLE_CADASTRADO);

    // Verifica preferências salvas
    $stmtPref = $db->prepare("SELECT compatibilidade_id FROM usuario_preferencias WHERE usuario_id = ? ORDER BY compatibilidade_id ASC");
    $stmtPref->execute([$newUserId]);
    $prefs = $stmtPref->fetchAll(PDO::FETCH_COLUMN);
    assertTest("Preferências alimentares (1 e 3) salvas corretamente no banco", $prefs == [1, 3]);
}

// ------------------------------------------------------------------------------
// TESTE 4: Tentativa de cadastro com e-mail duplicado
// ------------------------------------------------------------------------------
echo "\n[4/7] Testando proteção contra e-mails duplicados...\n";
$dupResult = $authService->register('Tentativa Duplicada', $randomEmail, 'Senha@123456');
assertTest("Cadastro com e-mail duplicado é rejeitado", $dupResult['success'] === false);

// ------------------------------------------------------------------------------
// TESTE 5: Atualização de perfil e novas preferências alimentares (RF06, RF07)
// ------------------------------------------------------------------------------
echo "\n[5/7] Testando atualização de perfil e preferências (RF06, RF07)...\n";
if (!empty($newUserId)) {
    $updateResult = $authService->updateProfile($newUserId, 'Nome Atualizado QA', 'Minha nova biografia culinária', [2, 4]);
    assertTest("Atualização de perfil executada com sucesso", $updateResult['success'] === true);

    $stmt = $db->prepare("SELECT nome, bio FROM usuarios WHERE id = ?");
    $stmt->execute([$newUserId]);
    $userUpdated = $stmt->fetch();
    assertTest("Nome e bio atualizados no banco de dados", $userUpdated['nome'] === 'Nome Atualizado QA' && $userUpdated['bio'] === 'Minha nova biografia culinária');

    $stmtPref = $db->prepare("SELECT compatibilidade_id FROM usuario_preferencias WHERE usuario_id = ? ORDER BY compatibilidade_id ASC");
    $stmtPref->execute([$newUserId]);
    $prefsUpdated = $stmtPref->fetchAll(PDO::FETCH_COLUMN);
    assertTest("Preferências atualizadas para [2, 4] no banco", $prefsUpdated == [2, 4]);
}

// ------------------------------------------------------------------------------
// TESTE 6: Solicitação de perfil de Produtor de Conteúdo (RF30, CA30)
// ------------------------------------------------------------------------------
echo "\n[6/7] Testando transição de membro para Produtor de Conteúdo (RF30)...\n";
if (!empty($newUserId)) {
    // Simula login do novo usuário
    Session::set('user_id', $newUserId);
    Session::set('user_role', ROLE_CADASTRADO);

    $prodResult = $authService->becomeProducer($newUserId);
    assertTest("Solicitação de tornar-se produtor bem-sucedida", $prodResult['success'] === true);

    $stmt = $db->prepare("SELECT perfil FROM usuarios WHERE id = ?");
    $stmt->execute([$newUserId]);
    $perfilProd = $stmt->fetchColumn();
    assertTest("Perfil no banco alterado para 'produtor'", $perfilProd === ROLE_PRODUTOR);
    assertTest("Perfil na sessão atualizado para 'produtor'", Session::get('user_role') === ROLE_PRODUTOR);
}

// ------------------------------------------------------------------------------
// TESTE 7: Teste do Logout e destruição segura da sessão (RNF07, RNF10)
// ------------------------------------------------------------------------------
echo "\n[7/7] Testando encerramento de sessão (Logout)...\n";
$authService->logout();
assertTest("Sessão destruída: user_id não existe mais", Session::get('user_id') === null);
assertTest("Sessão destruída: user_role é visitante", Session::get('user_role') === null || AuthMiddleware::getRole() === ROLE_VISITANTE);

// Limpeza do usuário de teste
if (!empty($newUserId)) {
    $db->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$newUserId]);
}

echo "\n========================================================\n";
echo "   RESULTADO FINAL DOS TESTES DA ETAPA 2                \n";
echo "========================================================\n";
echo "Total de verificações: {$totalTests}\n";
echo "Aprovados:             {$testsPassed}\n";
echo "Falhas:                " . ($totalTests - $testsPassed) . "\n";
echo "========================================================\n";

if ($testsPassed === $totalTests) {
    echo "🎉 TODOS OS CRITÉRIOS DE TESTE DA ETAPA 2 FORAM APROVADOS!\n";
    exit(0);
} else {
    echo "⚠️ ALGUNS TESTES FALHARAM. VERIFIQUE OS LOGS ACIMA.\n";
    exit(1);
}
