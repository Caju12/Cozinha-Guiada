<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: views/interactions/favorites.php
// DESCRIÇÃO: Listagem de receitas salvas pelo usuário autenticado (RF20, RF21, RF22)
// ==============================================================================

$pageTitle = 'Minhas Receitas Favoritas — Cozinha Guiada';
require VIEWS_PATH . '/layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- CABEÇALHO -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-2xl">⭐</span>
                <h1 class="text-3xl font-extrabold text-stone-900 tracking-tight">Receitas Favoritas</h1>
            </div>
            <p class="text-sm text-stone-500">Seu livro de receitas pessoal salvo para acesso rápido</p>
        </div>

        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-100 text-amber-900 text-xs font-bold self-start sm:self-auto">
            <span>❤️</span> <?= count($favorites) ?> <?= count($favorites) === 1 ? 'receita salva' : 'receitas salvas' ?>
        </span>
    </div>

    <?php if (empty($favorites)): ?>
        
        <!-- ESTADO VAZIO -->
        <div class="bg-white p-12 rounded-3xl border border-stone-200 text-center shadow-sm max-w-xl mx-auto my-12">
            <span class="text-5xl block mb-3">📖✨</span>
            <h2 class="text-lg font-bold text-stone-800 mb-1">Você ainda não favoritou nenhuma receita</h2>
            <p class="text-xs text-stone-500 max-w-md mx-auto mb-6">
                Ao navegar pelas receitas, clique no botão de coração ou "Salvar nos Favoritos" para guardá-las aqui.
            </p>
            <a href="/receitas" class="inline-block px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow transition-all">
                Explorar Catálogo de Receitas
            </a>
        </div>

    <?php else: ?>

        <!-- GRADE DE FAVORITOS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($favorites as $rec): ?>
                <div class="bg-white rounded-2xl border border-stone-200 shadow-sm hover:shadow-md hover:border-amber-300 transition-all flex flex-col justify-between overflow-hidden group">
                    <div>
                        <!-- TOPO DO CARD -->
                        <div class="h-44 bg-gradient-to-tr from-amber-500 to-orange-400 flex items-center justify-center text-5xl text-white shadow-inner relative">
                            🍽️
                            <div class="absolute bottom-2.5 right-2.5 bg-stone-900/80 backdrop-blur-sm text-white px-2.5 py-1 rounded-full text-[11px] font-bold">
                                ⏱️ <?= (int)$rec['tempo_preparo_minutos'] ?> min
                            </div>

                            <!-- BOTÃO DE REMOVER DOS FAVORITOS -->
                            <form action="/favoritos/toggle" method="POST" class="absolute top-3 right-3">
                                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                <input type="hidden" name="receita_id" value="<?= (int)$rec['id'] ?>">
                                <button 
                                    type="submit" 
                                    class="w-8 h-8 rounded-full bg-white/90 hover:bg-rose-50 text-rose-600 flex items-center justify-center text-sm shadow transition-all hover:scale-110"
                                    title="Remover dos favoritos"
                                >
                                    ❤️
                                </button>
                            </form>
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

                            <p class="text-xs text-stone-600 line-clamp-2 leading-relaxed mb-4">
                                <?= htmlspecialchars($rec['descricao']) ?>
                            </p>
                        </div>
                    </div>

                    <div class="px-5 pb-5 pt-3 border-t border-stone-100 flex items-center justify-between text-xs">
                        <span class="text-stone-400">
                            Salvo em: <?= date('d/m/Y', strtotime($rec['favoritado_em'])) ?>
                        </span>
                        <a href="/receita?id=<?= (int)$rec['id'] ?>" class="font-bold text-amber-600 hover:text-amber-700">
                            Ver Receita &rarr;
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
