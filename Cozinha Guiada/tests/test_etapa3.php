<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: tests/test_etapa3.php
// DESCRIÇÃO: Bateria de testes automatizados da Etapa 3 (Catálogo, Busca e Filtros)
// USO: php tests/test_etapa3.php
// ==============================================================================

require_once dirname(__DIR__) . '/config/constants.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/Models/Recipe.php';

echo "========================================================\n";
echo "   TESTES AUTOMATIZADOS - ETAPA 3 (CATÁLOGO & FILTROS)   \n";
echo "========================================================\n\n";

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
// TESTE 1: Busca textual livre por título (RF02) e tempo de resposta (RNF04)
// ------------------------------------------------------------------------------
echo "[1/6] Testando busca textual e tempo de resposta (RF02, RNF04)...\n";
$start = microtime(true);
$resultsText = $recipeModel->search(['q' => 'Cenoura']);
$durationMs = (microtime(true) - $start) * 1000;

assertTest("Busca por 'Cenoura' retorna pelo menos 1 resultado", count($resultsText) >= 1);
assertTest("Receita retornada possui o termo no título", str_contains($resultsText[0]['titulo'], 'Cenoura'));
assertTest("Tempo de resposta menor que 3000ms (RNF04) - medido: " . number_format($durationMs, 2) . "ms", $durationMs < 3000);

// ------------------------------------------------------------------------------
// TESTE 2: Filtro por Categoria (RF03)
// ------------------------------------------------------------------------------
echo "\n[2/6] Testando filtro por categoria (RF03)...\n";
$resultsCat = $recipeModel->search(['categoria_id' => 1]); // Bolos e Tortas
assertTest("Filtro Categoria 1 (Bolos e Tortas) retorna o Bolo de Cenoura", count($resultsCat) >= 1 && $resultsCat[0]['id'] === 1);

// ------------------------------------------------------------------------------
// TESTE 3: Filtro estrito de compatibilidades com relação "E" (AND - RF11)
// ------------------------------------------------------------------------------
echo "\n[3/6] Testando filtro estrito de múltiplas compatibilidades simultâneas (RF10, RF11)...\n";

// Caso A: Busca apenas "Vegetariana" (ID 1) -> Bolo de Cenoura E Panqueca Fit devem vir
$resultsVeg = $recipeModel->search(['compatibilidades' => [1]]);
$idsVeg = array_column($resultsVeg, 'id');
assertTest("Filtro [Vegetariana] retorna ambas as receitas (IDs 1 e 2)", in_array(1, $idsVeg) && in_array(2, $idsVeg));

// Caso B: Busca simultânea [Vegana (2), Sem lactose (3), Sem glúten (4)] -> Apenas Panqueca Fit atende a todos!
$resultsAll = $recipeModel->search(['compatibilidades' => [2, 3, 4]]);
$idsAll = array_column($resultsAll, 'id');
assertTest("Filtro combinado [Vegana + Sem Lactose + Sem Glúten] retorna apenas a Panqueca Fit (ID 2)", count($idsAll) === 1 && $idsAll[0] === 2);
assertTest("Bolo de Cenoura (que tem leite/trigo) foi corretamente excluído pelo filtro estrito (RF11)", !in_array(1, $idsAll));

// ------------------------------------------------------------------------------
// TESTE 4: Busca por Ingredientes na Despensa (RF08 / CA08)
// ------------------------------------------------------------------------------
echo "\n[4/6] Testando busca por ingredientes disponíveis na despensa (RF08)...\n";
$resultsPantry = $recipeModel->search(['ingredientes' => [10]]); // Banana madura
assertTest("Busca pelo ingrediente 'Banana' retorna a receita de Panqueca Fit", count($resultsPantry) >= 1 && $resultsPantry[0]['id'] === 2);

// ------------------------------------------------------------------------------
// TESTE 5: Detalhes da Receita com Parâmetros Culinários e Substituições (RF12 a RF17, RF43)
// ------------------------------------------------------------------------------
echo "\n[5/6] Testando integridade da ficha técnica detalhada da receita (RF12 a RF17)...\n";
$recipeDetail = $recipeModel->findById(1);

assertTest("Receita 1 encontrada com sucesso", $recipeDetail !== null);
assertTest("Tempo de preparo presente (RF16)", (int)$recipeDetail['tempo_preparo_minutos'] === 45);
assertTest("Ponto esperado presente (RF17)", !empty($recipeDetail['ponto_esperado']));
assertTest("Temperatura presente (RF17)", $recipeDetail['temperatura'] === '180°C');
assertTest("Utensílios vinculados presentes (RF16)", count($recipeDetail['utensilios']) > 0);
assertTest("Etapas sequenciais presentes (RF15)", count($recipeDetail['etapas']) === 7);
assertTest("Ingredientes presentes com quantidades e unidades (RF14)", count($recipeDetail['ingredientes']) === 9);

// Verifica presença de alternativa de substituição (RF13, RF43)
$hasSubstitution = false;
foreach ($recipeDetail['ingredientes'] as $ing) {
    if (!empty($ing['substituicoes'])) {
        $hasSubstitution = true;
        break;
    }
}
assertTest("Ingrediente possui substituição alternativa registrada (RF13, RF43)", $hasSubstitution);

// ------------------------------------------------------------------------------
// TESTE 6: Perfil Público do Produtor e suas Receitas (RF53, CA53)
// ------------------------------------------------------------------------------
echo "\n[6/6] Testando perfil público do produtor (RF53)...\n";
$producer = $recipeModel->getProducerProfile(2);
$producerRecipes = $recipeModel->findByProducer(2);

assertTest("Perfil do produtor ID 2 retornado com sucesso", $producer !== null && $producer['nome'] === 'Chef André Luiz');
assertTest("Bio do produtor disponível para exibição pública", !empty($producer['bio']));
assertTest("Receitas públicas do produtor listadas corretamente", count($producerRecipes) >= 2);

echo "\n========================================================\n";
echo "   RESULTADO FINAL DOS TESTES DA ETAPA 3                \n";
echo "========================================================\n";
echo "Total de verificações: {$totalTests}\n";
echo "Aprovados:             {$testsPassed}\n";
echo "Falhas:                " . ($totalTests - $testsPassed) . "\n";
echo "========================================================\n";

if ($testsPassed === $totalTests) {
    echo "🎉 TODOS OS CRITÉRIOS DE TESTE DA ETAPA 3 FORAM APROVADOS!\n";
    exit(0);
} else {
    echo "⚠️ ALGUNS TESTES FALHARAM.\n";
    exit(1);
}
