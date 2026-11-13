<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: views/recipes/guided_mode.php
// DESCRIÇÃO: Interface imersiva do Modo Guiado com Wake Lock e Timer (RF18, RF19)
// ==============================================================================

$pageTitle = 'Modo Guiado: ' . htmlspecialchars($recipe['titulo']) . ' — Cozinha Guiada';
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-stone-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="min-h-full flex flex-col bg-stone-950 text-stone-100 antialiased selection:bg-amber-500 selection:text-white">

    <!-- BARRA SUPERIOR FIXA DO MODO GUIADO -->
    <header class="sticky top-0 z-40 bg-stone-900/90 backdrop-blur-md border-b border-stone-800 px-4 sm:px-8 py-3.5 flex items-center justify-between">
        
        <!-- BOTÃO DE SAÍDA / RETORNO -->
        <a 
            href="/receita?id=<?= (int)$recipe['id'] ?>" 
            class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 hover:text-white px-3 py-2 rounded-xl bg-stone-800/80 hover:bg-stone-800 transition-colors"
            title="Sair do Modo Guiado e voltar para a receita"
        >
            <span>&larr; Sair</span>
            <span class="hidden sm:inline">do Modo Guiado</span>
        </a>

        <!-- TÍTULO DA RECEITA -->
        <div class="text-center max-w-md truncate px-2">
            <span class="text-xs font-extrabold uppercase tracking-wider text-amber-500 block">Modo de Preparo Guiado</span>
            <h1 class="text-sm font-bold text-stone-200 truncate"><?= htmlspecialchars($recipe['titulo']) ?></h1>
        </div>

        <!-- INDICADOR E TOGGLE DO SCREEN WAKE LOCK -->
        <div class="flex items-center gap-2">
            <button 
                id="wakelock-toggle" 
                type="button" 
                class="cursor-pointer"
                title="Clique para alternar o bloqueio de tela"
            >
                <div id="wakelock-status" class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-stone-800 border border-stone-700 text-xs shadow-sm transition-all">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                    <span class="text-stone-300 font-medium">Iniciando tela acesa...</span>
                </div>
            </button>
        </div>

    </header>

    <!-- BARRA DE PROGRESSO PERCENTUAL -->
    <div class="w-full bg-stone-800 h-2 relative overflow-hidden">
        <div id="progress-bar" class="h-full bg-gradient-to-r from-amber-500 to-orange-500 transition-all duration-300 ease-out" style="width: 0%;"></div>
    </div>

    <!-- CONTEÚDO PRINCIPAL DO PASSO A PASSO -->
    <main class="flex-grow max-w-4xl w-full mx-auto px-4 sm:px-8 py-8 sm:py-12 flex flex-col justify-between">
        
        <!-- CARD DO PASSO ATUAL -->
        <div class="bg-stone-900 border border-stone-800 rounded-3xl p-6 sm:p-12 shadow-2xl relative overflow-hidden">
            
            <!-- CONTADOR E BADGE -->
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-stone-800/80">
                <span id="step-number-badge" class="px-4 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 font-extrabold text-sm uppercase tracking-wider">
                    Passo 1
                </span>

                <div class="text-stone-400 text-xs font-bold tracking-wider">
                    Progresso: <span id="step-counter" class="text-stone-100 text-sm font-extrabold">1</span> de <span id="step-total" class="text-stone-100 text-sm font-extrabold"><?= count($recipe['etapas']) ?></span>
                </div>
            </div>

            <!-- TEXTO DA ETAPA EM DESTAQUE (GRANDE VISIBILIDADE) -->
            <div class="min-h-[160px] flex items-center mb-8">
                <p 
                    id="step-description" 
                    class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-stone-100 leading-snug tracking-tight transition-opacity duration-200"
                >
                    Carregando etapa...
                </p>
            </div>

            <!-- PARÂMETROS CULINÁRIOS CRÍTICOS -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-6 border-t border-stone-800/80 text-xs">
                <?php if (!empty($recipe['temperatura'])): ?>
                    <div class="p-3 rounded-2xl bg-stone-950/60 border border-stone-800 flex items-center gap-2.5">
                        <span class="text-lg">🌡️</span>
                        <div>
                            <span class="block text-[10px] text-stone-500 font-bold uppercase">Temperatura</span>
                            <span class="font-extrabold text-stone-300"><?= htmlspecialchars($recipe['temperatura']) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($recipe['intensidade_fogo'])): ?>
                    <div class="p-3 rounded-2xl bg-stone-950/60 border border-stone-800 flex items-center gap-2.5">
                        <span class="text-lg">🔥</span>
                        <div>
                            <span class="block text-[10px] text-stone-500 font-bold uppercase">Intensidade do Fogo</span>
                            <span class="font-extrabold text-stone-300"><?= htmlspecialchars($recipe['intensidade_fogo']) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($recipe['ponto_esperado'])): ?>
                    <div class="p-3 rounded-2xl bg-stone-950/60 border border-stone-800 flex items-center gap-2.5 sm:col-span-1">
                        <span class="text-lg">🎯</span>
                        <div class="truncate">
                            <span class="block text-[10px] text-stone-500 font-bold uppercase">Ponto Esperado</span>
                            <span class="font-extrabold text-stone-300 truncate block"><?= htmlspecialchars($recipe['ponto_esperado']) ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- TEMPORIZADOR INTEGRADO (TIMER CULINÁRIO) -->
        <div class="mt-8 bg-stone-900/60 border border-stone-800/80 rounded-2xl p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
            
            <div class="flex items-center gap-3">
                <span class="text-2xl">⏱️</span>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 block">Temporizador Auxiliar</span>
                    <span id="timer-display" class="font-mono text-2xl font-extrabold text-amber-400">00:00</span>
                </div>
            </div>

            <!-- PRESETS DE TEMPO -->
            <div class="flex flex-wrap items-center gap-1.5">
                <button type="button" data-timer-add="1" class="px-2.5 py-1.5 rounded-lg bg-stone-800 hover:bg-stone-700 text-stone-300 font-bold text-xs transition-colors">+1 min</button>
                <button type="button" data-timer-add="3" class="px-2.5 py-1.5 rounded-lg bg-stone-800 hover:bg-stone-700 text-stone-300 font-bold text-xs transition-colors">+3 min</button>
                <button type="button" data-timer-add="5" class="px-2.5 py-1.5 rounded-lg bg-stone-800 hover:bg-stone-700 text-stone-300 font-bold text-xs transition-colors">+5 min</button>
                <button type="button" data-timer-add="10" class="px-2.5 py-1.5 rounded-lg bg-stone-800 hover:bg-stone-700 text-stone-300 font-bold text-xs transition-colors">+10 min</button>
            </div>

            <!-- AÇÕES DO TIMER -->
            <div class="flex items-center gap-2">
                <button id="btn-timer-start" type="button" class="px-4 py-2 bg-stone-800 hover:bg-stone-700 text-white font-bold text-xs rounded-xl shadow transition-all">Iniciar</button>
                <button id="btn-timer-reset" type="button" class="px-3 py-2 text-stone-400 hover:text-white font-bold text-xs transition-colors">Zerar</button>
            </div>

        </div>

        <!-- BARRA INFERIOR DE NAVEGAÇÃO DE PASSOS (TOUCH-FRIENDLY) -->
        <div class="mt-8 flex items-center justify-between gap-4">
            
            <!-- BOTÃO ANTERIOR -->
            <button 
                id="btn-prev" 
                type="button" 
                class="flex-1 py-4 sm:py-5 px-6 rounded-2xl bg-stone-800 hover:bg-stone-700 text-stone-200 font-extrabold text-base sm:text-lg shadow-lg transition-all flex items-center justify-center gap-2 active:scale-95"
            >
                <span class="text-xl">&larr;</span>
                <span>Anterior</span>
            </button>

            <!-- BOTÃO PRÓXIMO -->
            <button 
                id="btn-next" 
                type="button" 
                class="flex-[2] py-4 sm:py-5 px-6 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-base sm:text-lg shadow-lg hover:shadow-amber-500/20 transition-all flex items-center justify-center gap-2 active:scale-95"
            >
                <span>Próximo Passo</span>
                <span class="text-xl">&rarr;</span>
            </button>

        </div>

    </main>

    <!-- MODAL DE FINALIZAÇÃO DA RECEITA -->
    <div 
        id="completion-modal" 
        class="fixed inset-0 z-50 bg-stone-950/80 backdrop-blur-md hidden items-center justify-center p-4"
    >
        <div class="bg-stone-900 border border-stone-800 max-w-lg w-full rounded-3xl p-8 text-center shadow-2xl animate-fade-in">
            <div class="w-20 h-20 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-4xl mx-auto mb-4">
                🎉
            </div>

            <h2 class="text-2xl font-extrabold text-white mb-2">Receita Concluída!</h2>
            <p class="text-sm text-stone-400 mb-6 leading-relaxed">
                Você executou com êxito todas as etapas de <strong><?= htmlspecialchars($recipe['titulo']) ?></strong>. Bom apetite!
            </p>

            <div class="space-y-3">
                <a 
                    href="/receita?id=<?= (int)$recipe['id'] ?>" 
                    class="block w-full py-3.5 px-6 bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm rounded-xl shadow transition-all"
                >
                    Avaliar e Ver Receita Completa
                </a>
                <a 
                    href="/receitas" 
                    class="block w-full py-3 px-6 bg-stone-800 hover:bg-stone-700 text-stone-300 font-semibold text-sm rounded-xl transition-all"
                >
                    Explorar Novas Receitas
                </a>
            </div>
        </div>
    </div>

    <!-- SCRIPT DE INICIALIZAÇÃO DO MODO GUIADO -->
    <script src="/js/guided-mode.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const recipeData = {
                recipeId: <?= (int)$recipe['id'] ?>,
                recipeTitle: <?= json_encode($recipe['titulo']) ?>,
                steps: <?= json_encode($recipe['etapas'], JSON_UNESCAPED_UNICODE) ?>
            };

            window.guidedApp = new GuidedMode(recipeData);
        });
    </script>
</body>
</html>
