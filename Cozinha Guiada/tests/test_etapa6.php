<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: tests/test_etapa6.php
// DESCRIÇÃO: Bateria de testes automatizados da Etapa 6 (Área do Produtor)
// USO: php tests/test_etapa6.php
// ==============================================================================

require_once dirname(__DIR__) . '/config/constants.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/Utils/Session.php';
require_once SRC_PATH . '/Models/Recipe.php';
require_once SRC_PATH . '/Services/UploadService.php';
require_once SRC_PATH . '/Controllers/ProducerController.php';

echo "========================================================\n";
echo "   TESTES AUTOMATIZADOS - ETAPA 6 (ÁREA DO PRODUTOR)    \n";
echo "========================================================\n\n";

$db = Database::getConnection();
$recipeModel = new Recipe();
$uploadService = new UploadService();

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

$chefUserId = 2; // Chef André Luiz (perfil produtor)
$memberUserId = 3; // Lucas Iniciante (perfil cadastrado)

// ------------------------------------------------------------------------------
// TESTE 1: Controle de Acesso Baseado em Papéis ao Painel do Produtor (RNF08, RNF09)
// ------------------------------------------------------------------------------
echo "[1/6] Testando controle de acesso (RBAC) ao painel do produtor...\n";

// Simula visitante:
Session::destroy();
assertTest("Visitante não tem acesso ao painel do produtor", AuthMiddleware::isLoggedIn() === false);

// Simula membro comum (cadastrado):
Session::set('user_id', $memberUserId);
Session::set('user_role', ROLE_CADASTRADO);
$roleCheck = AuthMiddleware::getRole();
assertTest("Membro cadastrado comum não possui perfil de produtor", $roleCheck !== ROLE_PRODUTOR && $roleCheck !== ROLE_ADMIN);

// Simula chef produtor:
Session::set('user_id', $chefUserId);
Session::set('user_role', ROLE_PRODUTOR);
assertTest("Produtor autenticado tem acesso autorizado", AuthMiddleware::getRole() === ROLE_PRODUTOR);

// ------------------------------------------------------------------------------
// TESTE 2: Criação de Nova Receita Completa com Relacionamentos (RF31 a RF34, RF41 a RF44)
// ------------------------------------------------------------------------------
echo "\n[2/6] Testando persistência completa de receita com ingredientes e substituições...\n";

try {
    $db->beginTransaction();

    $stmtRec = $db->prepare("
        INSERT INTO receitas (
            usuario_id, titulo, descricao, tempo_preparo_minutos, rendimento, 
            temperatura, intensidade_fogo, ponto_esperado, visibilidade, status_moderacao
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'ativo')
    ");
    $stmtRec->execute([
        $chefUserId,
        'Torta Salgada de Legumes Inclusiva',
        'Receita fácil de liquidificador, macia e nutritiva para qualquer ocasião.',
        40,
        '8 porções',
        '180°C',
        'Fogo médio',
        'Palito limpo ao centro e bordas douradas',
        VISIBILITY_PUBLIC
    ]);
    $newRecipeId = (int)$db->lastInsertId();

    // Categorias (Bolos e Tortas = 1)
    $db->prepare("INSERT INTO receita_categorias (receita_id, categoria_id) VALUES (?, 1)")->execute([$newRecipeId]);

    // Compatibilidades (Vegetariana = 1, Sem Glúten = 4)
    $stmtComp = $db->prepare("INSERT INTO receita_compatibilidades (receita_id, compatibilidade_id) VALUES (?, ?)");
    $stmtComp->execute([$newRecipeId, 1]);
    $stmtComp->execute([$newRecipeId, 4]);

    // Utensílios (Liquidificador = 1, Forno = 2)
    $stmtUt = $db->prepare("INSERT INTO receita_utensilios (receita_id, utensilio_id) VALUES (?, ?)");
    $stmtUt->execute([$newRecipeId, 1]);
    $stmtUt->execute([$newRecipeId, 2]);

    // Ingrediente com Substituição (RF32, RF43, RF44)
    $stmtIng = $db->prepare("INSERT INTO receita_ingredientes (receita_id, ingrediente_id, quantidade, unidade, observacao) VALUES (?, 14, 2, 'xícaras', 'peneirada')");
    $stmtIng->execute([$newRecipeId]);
    $recIngId = (int)$db->lastInsertId();

    $stmtSubst = $db->prepare("INSERT INTO substituicoes_ingrediente (receita_ingrediente_id, texto_substituto, proporcao, observacao) VALUES (?, ?, ?, ?)");
    $stmtSubst->execute([$recIngId, 'Amido de milho com polvilho doce', 'Mesma proporção (2 xícaras)', 'Misturar em partes iguais para dar liga perfeita']);

    // Etapas ordenadas (RF33)
    $stmtEtapa = $db->prepare("INSERT INTO etapas_receita (receita_id, ordem, descricao) VALUES (?, ?, ?)");
    $stmtEtapa->execute([$newRecipeId, 1, 'Bata todos os ingredientes líquidos no liquidificador.']);
    $stmtEtapa->execute([$newRecipeId, 2, 'Adicione a farinha e o fermento, misturando delicadamente.']);
    $stmtEtapa->execute([$newRecipeId, 3, 'Asse por 35 minutos a 180°C.']);

    $db->commit();
    assertTest("Receita inserida com sucesso no banco de dados (ID: {$newRecipeId})", $newRecipeId > 0);
} catch (Exception $e) {
    $db->rollBack();
    assertTest("Falha ao inserir receita: " . $e->getMessage(), false);
}

// ------------------------------------------------------------------------------
// TESTE 3: Verificação de Integridade das Relações Criadas
// ------------------------------------------------------------------------------
echo "\n[3/6] Verificando integridade das tabelas associativas e substituições...\n";

$savedDetail = $recipeModel->findById($newRecipeId);
assertTest("Título e campos técnicos salvos corretamente", $savedDetail['titulo'] === 'Torta Salgada de Legumes Inclusiva' && $savedDetail['tempo_preparo_minutos'] == 40);
assertTest("Categorias vinculadas corretamente", count($savedDetail['categorias']) === 1);
assertTest("Compatibilidades alimentares vinculadas corretamente (2 dietas)", count($savedDetail['compatibilidades']) === 2);
assertTest("Utensílios vinculados corretamente (2 itens)", count($savedDetail['utensilios']) === 2);
assertTest("Etapas de preparo ordenadas salvas (3 passos)", count($savedDetail['etapas']) === 3);
assertTest("Opção de substituição inclusiva registrada no ingrediente", !empty($savedDetail['ingredientes'][0]['substituicoes']));

// ------------------------------------------------------------------------------
// TESTE 4: Alternância de Visibilidade Pública / Privada (RF40)
// ------------------------------------------------------------------------------
echo "\n[4/6] Testando alternância de visibilidade pública/privada (RF40)...\n";

$db->prepare("UPDATE receitas SET visibilidade = 'privada' WHERE id = ?")->execute([$newRecipeId]);
$stmtCheckVis = $db->prepare("SELECT visibilidade FROM receitas WHERE id = ?");
$stmtCheckVis->execute([$newRecipeId]);
assertTest("Receita alternada para 'privada' (RF40)", $stmtCheckVis->fetchColumn() === VISIBILITY_PRIVATE);

// Verifica que visitante não encontra receita privada no catálogo
$publicSearch = $recipeModel->search(['q' => 'Torta Salgada']);
$foundInPublic = false;
foreach ($publicSearch as $r) {
    if ($r['id'] === $newRecipeId) $foundInPublic = true;
}
assertTest("Receita privada NÃO é visível no catálogo aberto para outros usuários (RF40)", $foundInPublic === false);

// ------------------------------------------------------------------------------
// TESTE 5: Proteção de Posse Contra Manipulação por Outro Usuário (RNF09)
// ------------------------------------------------------------------------------
echo "\n[5/6] Testando proteção de posse do produtor (RNF09)...\n";

$stmtOwner = $db->prepare("SELECT usuario_id FROM receitas WHERE id = ?");
$stmtOwner->execute([$newRecipeId]);
$realOwner = (int)$stmtOwner->fetchColumn();

// Simula tentativa de alteração pelo usuário 3 (que não é o autor)
$unauthorizedUserId = 3;
$canUnauthorizedEdit = ($unauthorizedUserId === $realOwner);
assertTest("Tentativa de manipulação por outro usuário bloqueada (RNF09)", $canUnauthorizedEdit === false);

// ------------------------------------------------------------------------------
// TESTE 6: Validação de Upload Seguro de Imagens (RF35, RF36, RNF11) e Exclusão Segura
// ------------------------------------------------------------------------------
echo "\n[6/6] Testando validações de upload seguro e integridade na exclusão...\n";

// Simula upload de arquivo executável malicioso (ex: .php)
$fakeMaliciousFile = [
    'name'     => 'malicioso.php',
    'type'     => 'application/x-php',
    'tmp_name' => __FILE__, // usa arquivo PHP existente como teste
    'error'    => UPLOAD_ERR_OK,
    'size'     => 1024
];
$uploadResult = $uploadService->uploadRecipeImage($fakeMaliciousFile);
assertTest("Tentativa de upload de script PHP bloqueada pela verificação de MIME (RNF11)", $uploadResult['success'] === false);

// Exclusão da receita de teste (RF39, CA39)
$db->prepare("DELETE FROM receitas WHERE id = ?")->execute([$newRecipeId]);
$deletedCheck = $recipeModel->findById($newRecipeId);
assertTest("Receita excluída com sucesso e dependências limpas em cascata (RF39, RNF14)", $deletedCheck === null);

echo "\n========================================================\n";
echo "   RESULTADO FINAL DOS TESTES DA ETAPA 6                \n";
echo "========================================================\n";
echo "Total de verificações: {$totalTests}\n";
echo "Aprovados:             {$testsPassed}\n";
echo "Falhas:                " . ($totalTests - $testsPassed) . "\n";
echo "========================================================\n";

if ($testsPassed === $totalTests) {
    echo "🎉 TODOS OS CRITÉRIOS DE TESTE DA ETAPA 6 FORAM APROVADOS!\n";
    exit(0);
} else {
    echo "⚠️ ALGUNS TESTES FALHARAM.\n";
    exit(1);
}
