<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: views/users/producer_profile.php
// DESCRIÇÃO: Perfil público do Produtor de Conteúdo e suas receitas (RF53, CA53)
// ==============================================================================

$pageTitle = 'Chef ' . htmlspecialchars($producer['nome']) . ' — Cozinha Guiada';
require_once VIEWS_PATH . '/layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- NAVEGAÇÃO BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs text-stone-500 mb-6">
        <a href="/" class="hover:text-amber-600">Início</a>
        <span>&rsaquo;</span>
        <a href="/receitas" class="hover:text-amber-600">Receitas</a>
        <span>&rsaquo;</span>
        <span class="text-stone-800 font-bold truncate">Chef <?= htmlspecialchars($producer['nome']) ?></span>
    </nav>

    <!-- CABEÇALHO DO PRODUTOR -->
    <div class="bg-white p-6 sm:p-10 rounded-3xl shadow-sm border border-stone-200 mb-10">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
            
            <div class="w-24 h-24 rounded-3xl bg-gradient-to-tr from-amber-500 to-amber-400 text-white flex items-center justify-center font-extrabold text-4xl shadow-md flex-shrink-0">
                <?= mb_strtoupper(mb_substr($producer['nome'], 0, 1)) ?>
            </div>

            <div class="text-center sm:text-left flex-grow">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5 mb-2">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900"><?= htmlspecialchars($producer['nome']) ?></h1>
                    <span class="bg-indigo-100 text-indigo-800 font-bold px-3 py-1 rounded-full text-xs uppercase tracking-wider">
                        Chef / Produtor
                    </span>
                </div>

                <p class="text-sm text-stone-600 leading-relaxed max-w-2xl mb-4">
                    <?= htmlspecialchars($producer['bio'] ?: 'Criador de conteúdo culinário apaixonado por ensinar receitas fáceis e inclusivas.') ?>
                </p>

                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-6 text-xs text-stone-500 pt-4 border-t border-stone-100">
                    <div>
                        <span class="font-extrabold text-stone-900 text-sm"><?= count($recipes) ?></span>
                        <span class="text-stone-400 ml-1">receitas publicadas</span>
                    </div>
                    <div>
                        <span>Membro desde: <strong><?= date('m/Y', strtotime($producer['criado_em'])) ?></strong></span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- LISTAGEM DE RECEITAS DO PRODUTOR -->
    <div>
        <div class="mb-6">
            <h2 class="text-xl font-bold text-stone-900">Receitas de <?= htmlspecialchars($producer['nome']) ?></h2>
            <p class="text-xs text-stone-500 mt-0.5">Todas as criações culinárias compartilhadas publicamente</p>
        </div>

        <?php if (empty($recipes)): ?>
            <div class="bg-white p-12 rounded-3xl border border-stone-200 text-center shadow-sm">
                <span class="text-4xl block mb-2">🍽️</span>
                <p class="text-sm text-stone-500">Este produtor ainda não publicou receitas abertas.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($recipes as $rec): ?>
                    <div class="bg-white rounded-2xl border border-stone-200 shadow-sm hover:shadow-md hover:border-amber-300 transition-all flex flex-col justify-between overflow-hidden group">
                        <div>
                            <div class="h-40 bg-gradient-to-tr from-amber-500 to-orange-400 flex items-center justify-center text-4xl text-white shadow-inner relative">
                                🍳
                                <div class="absolute bottom-2.5 right-2.5 bg-stone-900/80 backdrop-blur-sm text-white px-2 py-0.5 rounded-full text-[10px] font-bold">
                                    ⏱️ <?= (int)$rec['tempo_preparo_minutos'] ?> min
                                </div>
                            </div>

                            <div class="p-5">
                                <?php if (!empty($rec['compatibilidades'])): ?>
                                    <div class="flex flex-wrap gap-1 mb-2">
                                        <?php foreach ($rec['compatibilidades'] as $comp): ?>
                                            <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">
                                                <?= htmlspecialchars($comp['nome']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <h3 class="font-bold text-stone-900 group-hover:text-amber-600 transition-colors line-clamp-1 mb-1">
                                    <a href="/receita?id=<?= (int)$rec['id'] ?>">
                                        <?= htmlspecialchars($rec['titulo']) ?>
                                    </a>
                                </h3>

                                <p class="text-xs text-stone-600 line-clamp-2 leading-relaxed">
                                    <?= htmlspecialchars($rec['descricao']) ?>
                                </p>
                            </div>
                        </div>

                        <div class="px-5 pb-5 pt-3 border-t border-stone-100 flex items-center justify-between text-xs">
                            <span class="text-stone-400">
                                Rendimento: <strong><?= htmlspecialchars($rec['rendimento'] ?: 'Livre') ?></strong>
                            </span>
                            <a href="/receita?id=<?= (int)$rec['id'] ?>" class="font-bold text-amber-600 hover:text-amber-700">
                                Ver Detalhes &rarr;
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
