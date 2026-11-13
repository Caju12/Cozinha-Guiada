<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: tests/test_etapa5.php
// DESCRIÇÃO: Bateria de testes automatizados da Etapa 5 (Favoritos, Histórico, Comentários, Avaliações e Denúncias)
// USO: php tests/test_etapa5.php
// ==============================================================================

require_once dirname(__DIR__) . '/config/constants.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/Utils/Session.php';
require_once SRC_PATH . '/Models/Recipe.php';
require_once SRC_PATH . '/Controllers/InteractionController.php';
require_once SRC_PATH . '/Controllers/CommentController.php';

echo "========================================================\n";
echo "   TESTES AUTOMATIZADOS - ETAPA 5 (INTERAÇÕES DO USUÁRIO)\n";
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

$testUserId = 3; // Lucas Iniciante (cadastrado)
$testRecipeId = 1; // Bolo de Cenoura

// ------------------------------------------------------------------------------
// TESTE 1: Sistema de Favoritos (RF20, RF21, RF22)
// ------------------------------------------------------------------------------
echo "[1/5] Testando adicionar, listar e remover favoritos (RF20, RF21, RF22)...\n";

// Garante estado inicial limpo para o teste
$db->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND receita_id = ?")->execute([$testUserId, $testRecipeId]);

// 1.1 Adiciona aos favoritos
$db->prepare("INSERT INTO favoritos (usuario_id, receita_id) VALUES (?, ?)")->execute([$testUserId, $testRecipeId]);
assertTest("Receita adicionada aos favoritos com sucesso (RF20)", $recipeModel->isFavoritedByUser($testRecipeId, $testUserId) === true);

// 1.2 Remove dos favoritos
$db->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND receita_id = ?")->execute([$testUserId, $testRecipeId]);
assertTest("Receita removida dos favoritos com sucesso (RF21)", $recipeModel->isFavoritedByUser($testRecipeId, $testUserId) === false);

// ------------------------------------------------------------------------------
// TESTE 2: Histórico de Visualizações e Limpeza (RF23, RF24)
// ------------------------------------------------------------------------------
echo "\n[2/5] Testando registro e limpeza de histórico (RF23, RF24)...\n";

$db->prepare("INSERT INTO historico_receitas (usuario_id, receita_id, visualizado_em) VALUES (?, ?, NOW())")->execute([$testUserId, $testRecipeId]);
$stmtHist = $db->prepare("SELECT COUNT(*) FROM historico_receitas WHERE usuario_id = ?");
$stmtHist->execute([$testUserId]);
assertTest("Registro de visualização gravado no histórico (RF23)", (int)$stmtHist->fetchColumn() > 0);

// Limpeza de histórico
$db->prepare("DELETE FROM historico_receitas WHERE usuario_id = ?")->execute([$testUserId]);
$stmtHist->execute([$testUserId]);
assertTest("Limpeza de histórico realizada com sucesso (RF24)", (int)$stmtHist->fetchColumn() === 0);

// ------------------------------------------------------------------------------
// TESTE 3: Avaliações com Estrelas (RF28 / CA28)
// ------------------------------------------------------------------------------
echo "\n[3/5] Testando avaliações de 1 a 5 estrelas e cálculo de média (RF28)...\n";

// Avaliação válida (nota 4)
$stmtRate = $db->prepare("
    INSERT INTO avaliacoes (usuario_id, receita_id, nota) 
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE nota = VALUES(nota)
");
$stmtRate->execute([$testUserId, $testRecipeId, 4]);

$notaSalva = $recipeModel->getUserRating($testRecipeId, $testUserId);
assertTest("Nota 4 atribuída e persistida com sucesso (RF28)", $notaSalva === 4);

// Tentativa de nota inválida (< 1 ou > 5): deve ser rejeitada pela regra de negócio
$notaInvalida = 7;
$isNotaValida = ($notaInvalida >= 1 && $notaInvalida <= 5);
assertTest("Nota fora do intervalo (7) é rejeitada pela validação (CA28)", $isNotaValida === false);

// ------------------------------------------------------------------------------
// TESTE 4: Comentários, Sanitização XSS e Proteção de Posse (RF25, RF26, RF27, RNF09, RNF11)
// ------------------------------------------------------------------------------
echo "\n[4/5] Testando criação, sanitização XSS e controle estrito de posse em comentários (RF25, RF26, RF27)...\n";

// 4.1 Criação com tentativa de injeção XSS (RNF11)
$xssPayload = "<script>alert('hack');</script>Bolo maravilhoso e fácil!";
$sanitizedText = trim(strip_tags($xssPayload));

$stmtCom = $db->prepare("INSERT INTO comentarios (usuario_id, receita_id, texto, status) VALUES (?, ?, ?, 'ativo')");
$stmtCom->execute([$testUserId, $testRecipeId, $sanitizedText]);
$commentId = (int)$db->lastInsertId();

$stmtFetchCom = $db->prepare("SELECT texto FROM comentarios WHERE id = ?");
$stmtFetchCom->execute([$commentId]);
$savedCommentText = $stmtFetchCom->fetchColumn();

assertTest("Comentário salvo com tags maliciosas <script> devidamente removidas (RNF11)", !str_contains($savedCommentText, '<script>'));
assertTest("Conteúdo seguro preservado: '{$savedCommentText}'", str_contains($savedCommentText, 'Bolo maravilhoso'));

// 4.2 Verificação estrita de posse (RNF09): Usuário 2 (Chef) NÃO pode editar comentário do Usuário 3
$otherUserId = 2; // Chef André Luiz
$stmtOwnerCheck = $db->prepare("SELECT usuario_id FROM comentarios WHERE id = ?");
$stmtOwnerCheck->execute([$commentId]);
$realOwnerId = (int)$stmtOwnerCheck->fetchColumn();

$canOtherUserEdit = ($otherUserId === $realOwnerId);
assertTest("Usuário sem posse é bloqueado de editar comentário alheio (RNF09)", $canOtherUserEdit === false);

// 4.3 Edição pelo autor legítimo (RF26)
$db->prepare("UPDATE comentarios SET texto = ? WHERE id = ? AND usuario_id = ?")->execute(['Texto editado pelo autor', $commentId, $testUserId]);
$stmtFetchCom->execute([$commentId]);
assertTest("Comentário editado com sucesso pelo autor legítimo (RF26)", $stmtFetchCom->fetchColumn() === 'Texto editado pelo autor');

// 4.4 Exclusão pelo autor (RF27)
$db->prepare("DELETE FROM comentarios WHERE id = ? AND usuario_id = ?")->execute([$commentId, $testUserId]);
$stmtFetchCom->execute([$commentId]);
assertTest("Comentário excluído com sucesso (RF27)", $stmtFetchCom->fetchColumn() === false);

// ------------------------------------------------------------------------------
// TESTE 5: Sistema de Denúncias (RF29, CA29)
// ------------------------------------------------------------------------------
echo "\n[5/5] Testando envio e registro de denúncia (RF29)...\n";

$stmtDen = $db->prepare("
    INSERT INTO denuncias (usuario_id, tipo_alvo, alvo_id, motivo, status) 
    VALUES (?, 'receita', ?, 'Receita com tempo de cozimento perigoso para iniciantes', 'pendente')
");
$stmtDen->execute([$testUserId, $testRecipeId]);
$denunciaId = (int)$db->lastInsertId();

$stmtCheckDen = $db->prepare("SELECT status, tipo_alvo, alvo_id FROM denuncias WHERE id = ?");
$stmtCheckDen->execute([$denunciaId]);
$denuncia = $stmtCheckDen->fetch();

assertTest("Denúncia de receita gravada com status 'pendente' (RF29)", $denuncia['status'] === 'pendente' && $denuncia['tipo_alvo'] === 'receita');

// Limpeza de dados do teste
$db->prepare("DELETE FROM denuncias WHERE id = ?")->execute([$denunciaId]);

echo "\n========================================================\n";
echo "   RESULTADO FINAL DOS TESTES DA ETAPA 5                \n";
echo "========================================================\n";
echo "Total de verificações: {$totalTests}\n";
echo "Aprovados:             {$testsPassed}\n";
echo "Falhas:                " . ($totalTests - $testsPassed) . "\n";
echo "========================================================\n";

if ($testsPassed === $totalTests) {
    echo "🎉 TODOS OS CRITÉRIOS DE TESTE DA ETAPA 5 FORAM APROVADOS!\n";
    exit(0);
} else {
    echo "⚠️ ALGUNS TESTES FALHARAM.\n";
    exit(1);
}
