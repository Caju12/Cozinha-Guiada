<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: views/home/index.php
// DESCRIÇÃO: Página inicial da plataforma
// ==============================================================================

$pageTitle = 'Cozinha Guiada — Receitas Práticas, Inclusivas e com Modo Guiado';
require_once VIEWS_PATH . '/layouts/header.php';
?>

<!-- SEÇÃO HERO -->
<section class="relative bg-gradient-to-b from-amber-500/10 via-amber-500/5 to-transparent py-16 sm:py-24 border-b border-stone-200/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        
        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200 shadow-sm mb-6">
            <span>✨</span> Culinária Acessível e Guiada
        </span>

        <h1 class="text-4xl sm:text-6xl font-extrabold text-stone-900 tracking-tight max-w-4xl mx-auto leading-tight">
            Cozinhe com Confiança, <br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-600 to-orange-600">
                Sem Complicações e Sem Restrições
            </span>
        </h1>

        <p class="mt-6 text-lg text-stone-600 max-w-2xl mx-auto leading-relaxed">
            Aprenda a preparar pratos deliciosos no seu ritmo com o modo de preparo passo a passo (que não apaga a tela do celular) e alternativas práticas para restrições alimentares.
        </p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
            <?php if (!Session::has('user_id')): ?>
                <a href="/cadastro" class="px-8 py-3.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5">
                    Comece Gratuitamente
                </a>
                <a href="/login" class="px-8 py-3.5 bg-white hover:bg-stone-50 text-stone-700 font-bold rounded-2xl border border-stone-300 shadow-sm transition-all">
                    Já sou cadastrado
                </a>
            <?php else: ?>
                <a href="/perfil" class="px-8 py-3.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5">
                    Acessar Meu Perfil
                </a>
                <a href="#receitas" class="px-8 py-3.5 bg-white hover:bg-stone-50 text-stone-700 font-bold rounded-2xl border border-stone-300 shadow-sm transition-all">
                    Ver Receitas em Destaque
                </a>
            <?php endif; ?>
        </div>

    </div>
</section>

<!-- DESTAQUES DE FUNCIONALIDADES -->
<section class="py-12 bg-white border-b border-stone-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <div class="flex items-start gap-4 p-5 rounded-2xl bg-stone-50 border border-stone-200">
                <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl flex-shrink-0">
                    📱
                </div>
                <div>
                    <h3 class="font-bold text-stone-900 text-base mb-1">Modo Guiado na Cozinha</h3>
                    <p class="text-xs text-stone-600 leading-relaxed">
                        Navegue passo a passo com botões grandes e a tela sempre ligada sem bloquear enquanto você cozinha.
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-4 p-5 rounded-2xl bg-stone-50 border border-stone-200">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl flex-shrink-0">
                    🌱
                </div>
                <div>
                    <h3 class="font-bold text-stone-900 text-base mb-1">100% Inclusivo</h3>
                    <p class="text-xs text-stone-600 leading-relaxed">
                        Filtros estritos para dietas vegetarianas, veganas, sem lactose e sem glúten com total clareza.
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-4 p-5 rounded-2xl bg-stone-50 border border-stone-200">
                <div class="w-12 h-12 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center text-2xl flex-shrink-0">
                    🔄
                </div>
                <div>
                    <h3 class="font-bold text-stone-900 text-base mb-1">Substituições Inteligentes</h3>
                    <p class="text-xs text-stone-600 leading-relaxed">
                        Faltou um ingrediente? Encontre proporções e alternativas recomendadas pelos próprios chefs.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- CATEGORIAS -->
<section id="categorias" class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl font-extrabold text-stone-900">Categorias de Receitas</h2>
                <p class="text-sm text-stone-500 mt-1">Explore pratos separados pelo que você deseja saborear</p>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <?php foreach ($categorias as $cat): ?>
                <a href="/receitas?categoria=<?= (int)$cat['id'] ?>" class="p-4 bg-white rounded-2xl border border-stone-200 shadow-sm hover:border-amber-400 hover:shadow-md transition-all text-center group">
                    <span class="text-2xl block mb-2 group-hover:scale-110 transition-transform">🍽️</span>
                    <span class="font-bold text-sm text-stone-800 group-hover:text-amber-600 transition-colors"><?= htmlspecialchars($cat['nome']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- RECEITAS EM DESTAQUE -->
<section id="receitas" class="py-16 bg-stone-100/70 border-t border-stone-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h2 class="text-2xl font-extrabold text-stone-900">Receitas em Destaque</h2>
            <p class="text-sm text-stone-500 mt-1">Pratos testados e aprovados para começar hoje mesmo</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($destaques as $rec): ?>
                <div class="bg-white rounded-2xl border border-stone-200 shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-all">
                    
                    <!-- BANNER DA RECEITA -->
                    <div class="h-44 bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-5xl text-white shadow-inner">
                        🍰
                    </div>

                    <div class="p-6 flex-grow flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-3 text-xs text-stone-500 mb-2 font-medium">
                                <span>⏱️ <?= (int)$rec['tempo_preparo_minutos'] ?> min</span>
                                <span>•</span>
                                <span>🍽️ <?= htmlspecialchars($rec['rendimento'] ?? 'Porções variadas') ?></span>
                            </div>

                            <h3 class="text-lg font-bold text-stone-900 mb-2 leading-snug hover:text-amber-600 transition-colors">
                                <a href="/receita?id=<?= (int)$rec['id'] ?>">
                                    <?= htmlspecialchars($rec['titulo']) ?>
                                </a>
                            </h3>

                            <p class="text-xs text-stone-600 line-clamp-2 leading-relaxed mb-4">
                                <?= htmlspecialchars($rec['descricao']) ?>
                            </p>
                        </div>

                        <div class="pt-4 border-t border-stone-100 flex items-center justify-between text-xs">
                            <span class="text-stone-500 font-medium">
                                Por <strong><?= htmlspecialchars($rec['autor_nome']) ?></strong>
                            </span>
                            <a href="/receita?id=<?= (int)$rec['id'] ?>" class="text-amber-600 font-bold hover:underline">
                                Ver Detalhes &rarr;
                            </a>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
