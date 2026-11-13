<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: tests/test_etapa4.php
// DESCRIÇÃO: Bateria de testes automatizados da Etapa 4 (Modo Guiado & Wake Lock)
// USO: php tests/test_etapa4.php
// ==============================================================================

require_once dirname(__DIR__) . '/config/constants.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/Models/Recipe.php';

echo "========================================================\n";
echo "   TESTES AUTOMATIZADOS - ETAPA 4 (MODO GUIADO & WAKE LOCK)\n";
echo "========================================================\n\n";

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
// TESTE 1: Renderização do Modo Guiado para Receita Válida (RF18)
// ------------------------------------------------------------------------------
echo "[1/4] Testando renderização do Modo Guiado (RF18)...\n";

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/receita/modo-guiado?id=1';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_GET = ['id' => '1'];

ob_start();
require PUBLIC_PATH . '/index.php';
$html = ob_get_clean();

assertTest("Página do modo guiado renderizou com sucesso", !empty($html));
assertTest("Título do modo guiado presente no HTML", str_contains($html, 'Modo de Preparo Guiado'));
assertTest("Título da receita presente no HTML", str_contains($html, 'Bolo de Cenoura'));
assertTest("Barra de progresso presente (RF18)", str_contains($html, 'id="progress-bar"'));
assertTest("Contador de passos presente (RF18)", str_contains($html, 'id="step-counter"'));
assertTest("Script interativo guided-mode.js referenciado", str_contains($html, 'guided-mode.js'));

// ------------------------------------------------------------------------------
// TESTE 2: Elementos de Controle do Screen Wake Lock (RF19, CA19)
// ------------------------------------------------------------------------------
echo "\n[2/4] Testando elementos de controle da API Wake Lock (RF19)...\n";
assertTest("Elemento de status do Wake Lock presente no DOM", str_contains($html, 'id="wakelock-status"'));
assertTest("Botão interativo de toggle do Wake Lock presente no DOM", str_contains($html, 'id="wakelock-toggle"'));

// ------------------------------------------------------------------------------
// TESTE 3: Temporizador Culinário e Parâmetros de Ponto/Fogo (RF17, RF18)
// ------------------------------------------------------------------------------
echo "\n[3/4] Testando Temporizador Culinário e Parâmetros Culinários (RF17)...\n";
assertTest("Display do cronômetro regressivo presente no DOM", str_contains($html, 'id="timer-display"'));
assertTest("Botão Iniciar/Pausar do timer presente", str_contains($html, 'id="btn-timer-start"'));
assertTest("Presets rápidos de tempo (+1 min, +3 min, etc.) presentes", str_contains($html, 'data-timer-add="1"'));
assertTest("Modal de conclusão de receita presente", str_contains($html, 'id="completion-modal"'));
assertTest("Parâmetro de temperatura presente (180°C)", str_contains($html, '180°C'));

// ------------------------------------------------------------------------------
// TESTE 4: Verificação da Lógica do Controlador JavaScript (public/js/guided-mode.js)
// ------------------------------------------------------------------------------
echo "\n[4/4] Testando implementação técnica do script guided-mode.js (RF18, RF19)...\n";
$jsPath = PUBLIC_PATH . '/js/guided-mode.js';
assertTest("Arquivo JavaScript public/js/guided-mode.js existe", file_exists($jsPath));

$jsContent = file_get_contents($jsPath);
assertTest("Utiliza API moderna navigator.wakeLock.request('screen') (RF19)", str_contains($jsContent, "navigator.wakeLock.request('screen')"));
assertTest("Implementa liberação correta wakeLockSentinel.release() (CA19)", str_contains($jsContent, "release()"));
assertTest("Implementa reaquisição automática no evento visibilitychange (CA19)", str_contains($jsContent, "visibilitychange"));
assertTest("Implementa navegação por teclado (ArrowRight e ArrowLeft) (RF18)", str_contains($jsContent, "ArrowRight") && str_contains($jsContent, "ArrowLeft"));
assertTest("Implementa emissão de alerta sonoro via Web Audio API", str_contains($jsContent, "AudioContext"));

echo "\n========================================================\n";
echo "   RESULTADO FINAL DOS TESTES DA ETAPA 4                \n";
echo "========================================================\n";
echo "Total de verificações: {$totalTests}\n";
echo "Aprovados:             {$testsPassed}\n";
echo "Falhas:                " . ($totalTests - $testsPassed) . "\n";
echo "========================================================\n";

if ($testsPassed === $totalTests) {
    echo "🎉 TODOS OS CRITÉRIOS DE TESTE DA ETAPA 4 FORAM APROVADOS!\n";
    exit(0);
} else {
    echo "⚠️ ALGUNS TESTES FALHARAM.\n";
    exit(1);
}
