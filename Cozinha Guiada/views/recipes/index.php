<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: views/recipes/index.php
// DESCRIÇÃO: Catálogo público com pesquisa e filtros combinados
// ATENDE AOS REQUISITOS: RF01, RF02, RF03, RF08, RF09, RF10, RF11, RNF04
// ==============================================================================

$pageTitle = 'Catálogo de Receitas — Cozinha Guiada';
require_once VIEWS_PATH . '/layouts/header.php';

$selectedCompatibilidades = isset($_GET['compatibilidades']) && is_array($_GET['compatibilidades']) 
    ? array_map('intval', $_GET['compatibilidades']) 
    : [];

$selectedIngredientes = isset($_GET['ingredientes']) && is_array($_GET['ingredientes']) 
    ? array_map('intval', $_GET['ingredientes']) 
    : [];

$selectedCategoria = !empty($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$searchQuery = $_GET['q'] ?? '';
$hasActiveFilters = !empty($searchQuery) || !empty($selectedCategoria) || !empty($selectedCompatibilidades) || !empty($selectedIngredientes);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- CABEÇALHO DO CATÁLOGO -->
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-stone-900 tracking-tight">Catálogo de Receitas</h1>
        <p class="text-sm text-stone-500 mt-1">Explore pratos com instruções passo a passo e opções inclusivas</p>
    </div>

    <!-- BARRA DE BUSCA PRINCIPAL -->
    <form action="/receitas" method="GET" class="mb-8">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-grow">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-stone-400 text-lg">
                    🔍
                </span>
                <input 
                    type="text" 
                    name="q" 
                    value="<?= htmlspecialchars($searchQuery) ?>"
                    placeholder="Buscar por nome da receita ou palavra-chave (ex: bolo, panqueca, sopa)..."
                    class="w-full pl-11 pr-4 py-3 rounded-2xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm shadow-sm transition-all"
                >
            </div>
            
            <button 
                type="submit" 
                class="px-8 py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm rounded-2xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2"
            >
                <span>Buscar</span>
            </button>

            <?php if ($hasActiveFilters): ?>
                <a 
                    href="/receitas" 
                    class="px-4 py-3 bg-stone-200 hover:bg-stone-300 text-stone-700 font-semibold text-sm rounded-2xl transition-all text-center flex items-center justify-center"
                    title="Remover todos os filtros"
                >
                    Limpar Filtros
                </a>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 mt-8">
            
            <!-- FILTROS LATERAIS -->
            <aside class="space-y-6">
                
                <!-- 1. CATEGORIAS -->
                <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-stone-800 mb-3 flex items-center gap-1.5">
                        <span>📂</span> Categorias
                    </h3>
                    <div class="space-y-1.5 max-h-48 overflow-y-auto pr-1 text-xs">
                        <label class="flex items-center gap-2 cursor-pointer py-1 px-2 rounded-lg hover:bg-stone-50">
                            <input 
                                type="radio" 
                                name="categoria" 
                                value="" 
                                <?= empty($selectedCategoria) ? 'checked' : '' ?>
                                onchange="this.form.submit()"
                                class="text-amber-600 focus:ring-amber-500"
                            >
                            <span class="<?= empty($selectedCategoria) ? 'font-bold text-amber-700' : 'text-stone-600' ?>">Todas as Categorias</span>
                        </label>

                        <?php foreach ($categorias as $cat): ?>
                            <label class="flex items-center gap-2 cursor-pointer py-1 px-2 rounded-lg hover:bg-stone-50">
                                <input 
                                    type="radio" 
                                    name="categoria" 
                                    value="<?= (int)$cat['id'] ?>"
                                    <?= $selectedCategoria === (int)$cat['id'] ? 'checked' : '' ?>
                                    onchange="this.form.submit()"
                                    class="text-amber-600 focus:ring-amber-500"
                                >
                                <span class="<?= $selectedCategoria === (int)$cat['id'] ? 'font-bold text-amber-700' : 'text-stone-600' ?>">
                                    <?= htmlspecialchars($cat['nome']) ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 2. COMPATIBILIDADES ALIMENTARES -->
                <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-stone-800 flex items-center gap-1.5">
                            <span>🌱</span> Restrições Alimentares
                        </h3>
                    </div>
                    <p class="text-[11px] text-stone-400 mb-3">Exibe receitas que atendem a <strong>todas</strong> as dietas selecionadas simultaneamente:</p>

                    <div class="space-y-2">
                        <?php foreach ($todasCompatibilidades as $comp): ?>
                            <?php $isChecked = in_array((int)$comp['id'], $selectedCompatibilidades, true); ?>
                            <label class="flex items-center gap-2.5 p-2 rounded-xl border <?= $isChecked ? 'border-emerald-400 bg-emerald-50/70 text-emerald-900 font-bold' : 'border-stone-200 bg-stone-50/50 text-stone-700' ?> hover:bg-emerald-50/50 cursor-pointer transition-all text-xs">
                                <input 
                                    type="checkbox" 
                                    name="compatibilidades[]" 
                                    value="<?= (int)$comp['id'] ?>"
                                    <?= $isChecked ? 'checked' : '' ?>
                                    onchange="this.form.submit()"
                                    class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                                >
                                <span><?= htmlspecialchars($comp['nome']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 3. BUSCA POR DESPENSA -->
                <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-stone-800 mb-1 flex items-center gap-1.5">
                        <span>🥫</span> Minha Despensa
                    </h3>
                    <p class="text-[11px] text-stone-400 mb-3">Selecione o que você já tem em casa:</p>

                    <div class="space-y-1.5 max-h-52 overflow-y-auto pr-1 text-xs">
                        <?php foreach ($catalogoIngredientes as $ing): ?>
                            <?php $isIngChecked = in_array((int)$ing['id'], $selectedIngredientes, true); ?>
                            <label class="flex items-center gap-2 py-1 px-1.5 rounded-lg hover:bg-stone-50 cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="ingredientes[]" 
                                    value="<?= (int)$ing['id'] ?>"
                                    <?= $isIngChecked ? 'checked' : '' ?>
                                    onchange="this.form.submit()"
                                    class="rounded border-stone-300 text-amber-600 focus:ring-amber-500 h-3.5 w-3.5"
                                >
                                <span class="<?= $isIngChecked ? 'font-bold text-amber-800' : 'text-stone-600' ?>">
                                    <?= htmlspecialchars($ing['nome']) ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

            </aside>

            <!-- GRADE DE RECEITAS -->
            <div class="lg:col-span-3">
                
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider">
                        <?= count($recipes) ?> <?= count($recipes) === 1 ? 'receita encontrada' : 'receitas encontradas' ?>
                    </span>
                </div>

                <?php if (empty($recipes)): ?>
                    
                    <!-- ESTADO VAZIO -->
                    <div class="bg-white p-12 rounded-2xl border border-stone-200 text-center shadow-sm">
                        <div class="text-5xl mb-4">🥣</div>
                        <h2 class="text-lg font-bold text-stone-800 mb-1">Nenhuma receita encontrada</h2>
                        <p class="text-xs text-stone-500 max-w-md mx-auto mb-6">
                            Não encontramos receitas que combinem todos os critérios e filtros selecionados. Tente desmarcar algumas opções ou buscar por outros termos.
                        </p>
                        <a href="/receitas" class="inline-block px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow transition-all">
                            Limpar Filtros e Ver Todas
                        </a>
                    </div>

                <?php else: ?>

                    <!-- GRID COM CARDS -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-6">
                        <?php foreach ($recipes as $recipe): ?>
                            <div class="bg-white rounded-2xl border border-stone-200 shadow-sm hover:shadow-md hover:border-amber-300 transition-all flex flex-col justify-between overflow-hidden group">
                                
                                <div>
                                    <!-- TOPO DO CARD / IMAGEM PLACEHOLDER -->
                                    <div class="h-44 bg-gradient-to-tr from-amber-500 via-amber-400 to-orange-400 flex items-center justify-center text-5xl text-white shadow-inner relative">
                                        🍽️
                                        
                                        <!-- BADGE DE TEMPO -->
                                        <div class="absolute bottom-3 right-3 bg-stone-900/80 backdrop-blur-sm text-white px-2.5 py-1 rounded-full text-[11px] font-bold flex items-center gap-1 shadow">
                                            <span>⏱️</span> <?= (int)$recipe['tempo_preparo_minutos'] ?> min
                                        </div>

                                        <?php if ((float)$recipe['media_avaliacao'] > 0): ?>
                                            <div class="absolute top-3 left-3 bg-amber-400 text-stone-900 px-2.5 py-1 rounded-full text-[11px] font-extrabold flex items-center gap-1 shadow">
                                                <span>★</span> <?= number_format($recipe['media_avaliacao'], 1) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="p-5">
                                        <!-- BADGES DE COMPATIBILIDADE -->
                                        <?php if (!empty($recipe['compatibilidades'])): ?>
                                            <div class="flex flex-wrap gap-1 mb-2.5">
                                                <?php foreach ($recipe['compatibilidades'] as $comp): ?>
                                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">
                                                        <?= htmlspecialchars($comp['nome']) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                        <!-- TÍTULO -->
                                        <h2 class="text-base font-bold text-stone-900 group-hover:text-amber-600 transition-colors line-clamp-1 mb-1.5">
                                            <a href="/receita?id=<?= (int)$recipe['id'] ?>">
                                                <?= htmlspecialchars($recipe['titulo']) ?>
                                            </a>
                                        </h2>

                                        <!-- DESCRIÇÃO -->
                                        <p class="text-xs text-stone-600 line-clamp-2 leading-relaxed mb-4">
                                            <?= htmlspecialchars($recipe['descricao']) ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="px-5 pb-5 pt-3 border-t border-stone-100 flex items-center justify-between text-xs">
                                    <a href="/produtor?id=<?= (int)$recipe['autor_id'] ?>" class="text-stone-500 hover:text-stone-900 font-medium">
                                        Chef: <span class="text-stone-800 font-bold"><?= htmlspecialchars($recipe['autor_nome']) ?></span>
                                    </a>

                                    <a href="/receita?id=<?= (int)$recipe['id'] ?>" class="font-bold text-amber-600 hover:text-amber-700 flex items-center gap-1">
                                        Ver Receita &rarr;
                                    </a>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>

            </div>

        </div>
    </form>

</div>

<?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
