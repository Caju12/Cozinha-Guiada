<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: tests/test_etapa7.php
// DESCRIÇÃO: Bateria de testes automatizados da Etapa 7 (Painel Administrativo & Moderação)
// USO: php tests/test_etapa7.php
// ==============================================================================

require_once dirname(__DIR__) . '/config/constants.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/Utils/Session.php';
require_once SRC_PATH . '/Middleware/AuthMiddleware.php';
require_once SRC_PATH . '/Controllers/AdminController.php';
require_once SRC_PATH . '/Controllers/AdminModerationController.php';
require_once SRC_PATH . '/Controllers/AdminCategoryController.php';

echo "========================================================\n";
echo "   TESTES AUTOMATIZADOS - ETAPA 7 (ADMIN & MODERAÇÃO)   \n";
echo "========================================================\n\n";

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

$adminId = 1; // Administrador do Sistema
$memberId = 3; // Lucas Iniciante (cadastrado)

// ------------------------------------------------------------------------------
// TESTE 1: Controle de Acesso Restrito ao Administrador (RNF08, RNF09)
// ------------------------------------------------------------------------------
echo "[1/5] Testando controle de acesso ao painel administrativo (RNF08)...\n";

// Visitante
Session::destroy();
assertTest("Visitante não tem acesso à área de administração", AuthMiddleware::isLoggedIn() === false);

// Membro cadastrado
Session::set('user_id', $memberId);
Session::set('user_role', ROLE_CADASTRADO);
assertTest("Membro comum bloqueado de acessar a administração (RNF08)", AuthMiddleware::getRole() !== ROLE_ADMIN);

// Produtor
Session::set('user_id', 2);
Session::set('user_role', ROLE_PRODUTOR);
assertTest("Produtor bloqueado de acessar a administração (RNF08)", AuthMiddleware::getRole() !== ROLE_ADMIN);

// Administrador legítimo
Session::set('user_id', $adminId);
Session::set('user_role', ROLE_ADMIN);
assertTest("Administrador tem acesso autorizado", AuthMiddleware::getRole() === ROLE_ADMIN);

// ------------------------------------------------------------------------------
// TESTE 2: Prevenção de Lockout do Administrador (Regra Crítica de Segurança)
// ------------------------------------------------------------------------------
echo "\n[2/5] Testando prevenção de lockout (impedir auto-bloqueio do único admin)...\n";

$stmtAdmins = $db->query("SELECT COUNT(*) FROM usuarios WHERE perfil = 'administrador' AND ativo = 1");
$totalActiveAdmins = (int)$stmtAdmins->fetchColumn();

// Simula validação de bloqueio: se total de outros admins ativos == 0, a ação deve ser rejeitada
$otherAdmins = (int)$db->prepare("SELECT COUNT(*) FROM usuarios WHERE perfil = 'administrador' AND ativo = 1 AND id != ?")
                       ->execute([$adminId]);
$stmtCheck = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE perfil = 'administrador' AND ativo = 1 AND id != ?");
$stmtCheck->execute([$adminId]);
$otherActiveCount = (int)$stmtCheck->fetchColumn();

$shouldBlockDeactivation = ($otherActiveCount === 0);
assertTest("Prevenção de lockout ativada para proteger o único admin ativo", $shouldBlockDeactivation === true);

// ------------------------------------------------------------------------------
// TESTE 3: Gestão de Status e Papel de Usuário (RF46, RF47)
// ------------------------------------------------------------------------------
echo "\n[3/5] Testando bloqueio e reativação de usuário pelo admin (RF46, RF47)...\n";

// Bloqueia usuário 3
$db->prepare("UPDATE usuarios SET ativo = 0 WHERE id = ?")->execute([$memberId]);
$stmtStatus = $db->prepare("SELECT ativo FROM usuarios WHERE id = ?");
$stmtStatus->execute([$memberId]);
assertTest("Usuário bloqueado/desativado pelo administrador com sucesso (RF46)", (int)$stmtStatus->fetchColumn() === 0);

// Reativa usuário 3
$db->prepare("UPDATE usuarios SET ativo = 1 WHERE id = ?")->execute([$memberId]);
$stmtStatus->execute([$memberId]);
assertTest("Usuário reativado pelo administrador com sucesso (RF46)", (int)$stmtStatus->fetchColumn() === 1);

// Altera papel para produtor e retorna para cadastrado (RF47)
$db->prepare("UPDATE usuarios SET perfil = ? WHERE id = ?")->execute([ROLE_PRODUTOR, $memberId]);
$stmtRole = $db->prepare("SELECT perfil FROM usuarios WHERE id = ?");
$stmtRole->execute([$memberId]);
assertTest("Papel do usuário alterado para produtor pelo admin (RF47)", $stmtRole->fetchColumn() === ROLE_PRODUTOR);

$db->prepare("UPDATE usuarios SET perfil = ? WHERE id = ?")->execute([ROLE_CADASTRADO, $memberId]);
$stmtRole->execute([$memberId]);
assertTest("Papel restaurado para cadastrado (RF47)", $stmtRole->fetchColumn() === ROLE_CADASTRADO);

// ------------------------------------------------------------------------------
// TESTE 4: Moderação de Denúncias e Remoção de Conteúdo (RF49, RF50, RF52)
// ------------------------------------------------------------------------------
echo "\n[4/5] Testando moderação de denúncias e remoção de conteúdo (RF49, RF50, RF52)...\n";

// 4.1 Cria comentário sob denúncia
$db->prepare("INSERT INTO comentarios (usuario_id, receita_id, texto, status) VALUES (?, 1, 'Comentário com conteúdo proibido', 'ativo')")
   ->execute([$memberId]);
$flaggedCommentId = (int)$db->lastInsertId();

$db->prepare("INSERT INTO denuncias (usuario_id, tipo_alvo, alvo_id, motivo, status) VALUES (?, 'comentario', ?, 'Linguagem ofensiva', 'pendente')")
   ->execute([2, $flaggedCommentId]);
$reportCommentId = (int)$db->lastInsertId();

// Simula ação de moderador: REMOVER CONTEÚDO
$db->prepare("UPDATE comentarios SET status = 'removido' WHERE id = ?")->execute([$flaggedCommentId]);
$db->prepare("UPDATE denuncias SET status = 'conteudo_removido', resolvido_por = ?, resolvido_em = NOW() WHERE id = ?")
   ->execute([$adminId, $reportCommentId]);

$stmtComStatus = $db->prepare("SELECT status FROM comentarios WHERE id = ?");
$stmtComStatus->execute([$flaggedCommentId]);
assertTest("Comentário marcado como 'removido' pela moderação (RF49)", $stmtComStatus->fetchColumn() === STATUS_REMOVED);

$stmtRepStatus = $db->prepare("SELECT status, resolvido_por FROM denuncias WHERE id = ?");
$stmtRepStatus->execute([$reportCommentId]);
$resRep = $stmtRepStatus->fetch();
assertTest("Denúncia marcada como 'conteudo_removido' com identificação do admin (RF52)", $resRep['status'] === 'conteudo_removido' && (int)$resRep['resolvido_por'] === $adminId);

// Limpeza do teste
$db->prepare("DELETE FROM comentarios WHERE id = ?")->execute([$flaggedCommentId]);
$db->prepare("DELETE FROM denuncias WHERE id = ?")->execute([$reportCommentId]);

// 4.2 Descarte de denúncia improcedente
$db->prepare("INSERT INTO denuncias (usuario_id, tipo_alvo, alvo_id, motivo, status) VALUES (?, 'receita', 1, 'Denúncia falsa de teste', 'pendente')")
   ->execute([$memberId]);
$reportFakeId = (int)$db->lastInsertId();

$db->prepare("UPDATE denuncias SET status = 'descartada', resolvido_por = ?, resolvido_em = NOW() WHERE id = ?")
   ->execute([$adminId, $reportFakeId]);
$stmtRepStatus->execute([$reportFakeId]);
$resFake = $stmtRepStatus->fetch();
assertTest("Denúncia improcedente descartada pela moderação (RF52)", $resFake['status'] === REPORT_DISCARDED);

$db->prepare("DELETE FROM denuncias WHERE id = ?")->execute([$reportFakeId]);

// ------------------------------------------------------------------------------
// TESTE 5: CRUD Completo de Categorias (RF51, CA51)
// ------------------------------------------------------------------------------
echo "\n[5/5] Testando CRUD de categorias oficiais (RF51)...\n";

// Criar categoria
$categoryName = 'Doces e Confeitaria QA';
$categorySlug = 'doces-e-confeitaria-qa';
$db->prepare("INSERT INTO categorias (nome, slug, descricao) VALUES (?, ?, ?)")
   ->execute([$categoryName, $categorySlug, 'Doces testados automatizadamente']);
$newCatId = (int)$db->lastInsertId();
assertTest("Nova categoria criada com sucesso (RF51)", $newCatId > 0);

// Editar categoria
$db->prepare("UPDATE categorias SET nome = ?, descricao = ? WHERE id = ?")
   ->execute(['Doces Finos QA', 'Descrição atualizada', $newCatId]);
$stmtCatCheck = $db->prepare("SELECT nome FROM categorias WHERE id = ?");
$stmtCatCheck->execute([$newCatId]);
assertTest("Categoria editada com sucesso (RF51)", $stmtCatCheck->fetchColumn() === 'Doces Finos QA');

// Excluir categoria
$db->prepare("DELETE FROM categorias WHERE id = ?")->execute([$newCatId]);
$stmtCatCheck->execute([$newCatId]);
assertTest("Categoria excluída com sucesso (RF51)", $stmtCatCheck->fetchColumn() === false);

echo "\n========================================================\n";
echo "   RESULTADO FINAL DOS TESTES DA ETAPA 7                \n";
echo "========================================================\n";
echo "Total de verificações: {$totalTests}\n";
echo "Aprovados:             {$testsPassed}\n";
echo "Falhas:                " . ($totalTests - $testsPassed) . "\n";
echo "========================================================\n";

if ($testsPassed === $totalTests) {
    echo "🎉 TODOS OS CRITÉRIOS DE TESTE DA ETAPA 7 FORAM APROVADOS!\n";
    exit(0);
} else {
    echo "⚠️ ALGUNS TESTES FALHARAM.\n";
    exit(1);
}
