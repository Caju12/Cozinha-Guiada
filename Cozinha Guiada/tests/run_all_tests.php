<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: tests/run_all_tests.php
// DESCRIÇÃO: Executor unificado de todas as suítes de testes do sistema (Etapas 2 a 8)
// USO: php tests/run_all_tests.php
// ==============================================================================

$phpBinary = PHP_BINARY ?: 'php';
$testsDir = __DIR__;

$testSuites = [
    'Etapa 2 - Autenticação & RBAC'      => $testsDir . '/test_etapa2.php',
    'Etapa 3 - Catálogo & Filtros E'     => $testsDir . '/test_etapa3.php',
    'Etapa 4 - Modo Guiado & Wake Lock'  => $testsDir . '/test_etapa4.php',
    'Etapa 5 - Interações Sociais'       => $testsDir . '/test_etapa5.php',
    'Etapa 6 - Área do Produtor'         => $testsDir . '/test_etapa6.php',
    'Etapa 7 - Painel Admin & Moderação' => $testsDir . '/test_etapa7.php',
    'Etapa 8 - Aceite Final & RNFs'      => $testsDir . '/test_etapa8_final.php',
    'Rotas - Renderização Front-End'     => $testsDir . '/test_routes.php',
];

echo "====================================================================\n";
echo "       COZINHA GUIADA — EXECUÇÃO UNIFICADA DE TESTES                \n";
echo "====================================================================\n\n";

$allPassed = true;
$summary = [];

foreach ($testSuites as $name => $filePath) {
    if (!file_exists($filePath)) {
        echo "❌ Arquivo não encontrado: {$filePath}\n";
        $summary[$name] = 'AUSENTE';
        $allPassed = false;
        continue;
    }

    echo "▶️ Executando: {$name}...\n";
    $output = [];
    $returnVar = 0;
    exec("\"{$phpBinary}\" \"{$filePath}\" 2>&1", $output, $returnVar);

    if ($returnVar === 0) {
        echo "  ✅ APROVADO\n\n";
        $summary[$name] = 'APROVADO';
    } else {
        echo "  ❌ FALHA (Código de saída: {$returnVar})\n";
        echo implode("\n", array_slice($output, -10)) . "\n\n";
        $summary[$name] = 'FALHOU';
        $allPassed = false;
    }
}

echo "====================================================================\n";
echo "                    RESUMO GERAL DE HOMOLOGAÇÃO                     \n";
echo "====================================================================\n";

foreach ($summary as $suite => $status) {
    $icon = ($status === 'APROVADO') ? '✅' : '❌';
    printf("  %-40s %s %s\n", $suite, $icon, $status);
}

echo "====================================================================\n";

if ($allPassed) {
    echo "🎉 SUCESSO TOTAL! Todas as suítes de teste foram concluídas com êxito.\n";
    echo "   O sistema está 100% em conformidade com os requisitos e RNFs.\n";
    exit(0);
} else {
    echo "⚠️ ATENÇÃO: Uma ou mais suítes de teste apresentaram falhas.\n";
    exit(1);
}
