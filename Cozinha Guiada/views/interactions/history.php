<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: views/interactions/history.php
// DESCRIÇÃO: Histórico de receitas acessadas e opção de limpeza (RF23, RF24)
// ==============================================================================

$pageTitle = 'Histórico de Receitas — Cozinha Guiada';
require VIEWS_PATH . '/layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- CABEÇALHO -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-2xl">🕒</span>
                <h1 class="text-3xl font-extrabold text-stone-900 tracking-tight">Histórico de Visualizações</h1>
            </div>
            <p class="text-sm text-stone-500">Pratos e preparos que você consultou recentemente</p>
        </div>

        <?php if (!empty($history)): ?>
            <!-- BOTÃO DE LIMPAR HISTÓRICO -->
            <form action="/historico/limpar" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <button 
                    type="submit" 
                    onclick="return confirm('Tem certeza de que deseja apagar todo o seu histórico de visualizações? Esta ação não pode ser desfeita.')"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-rose-300 text-rose-700 hover:bg-rose-50 text-xs font-bold transition-all shadow-sm"
                >
                    <span>🗑️ Limpar Todo o Histórico</span>
                </button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (empty($history)): ?>
        
        <!-- ESTADO VAZIO -->
        <div class="bg-white p-12 rounded-3xl border border-stone-200 text-center shadow-sm max-w-xl mx-auto my-12">
            <span class="text-5xl block mb-3">🕒💨</span>
            <h2 class="text-lg font-bold text-stone-800 mb-1">Seu histórico está vazio</h2>
            <p class="text-xs text-stone-500 max-w-md mx-auto mb-6">
                Conforme você abrir e explorar receitas na plataforma enquanto conectado, elas aparecerão listadas aqui em ordem cronológica.
            </p>
            <a href="/receitas" class="inline-block px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow transition-all">
                Ver Catálogo de Receitas
            </a>
        </div>

    <?php else: ?>

        <!-- GRADE DE HISTÓRICO -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($history as $item): ?>
                <div class="bg-white rounded-2xl border border-stone-200 shadow-sm hover:shadow-md hover:border-amber-300 transition-all flex flex-col justify-between overflow-hidden group">
                    <div>
                        <div class="h-40 bg-gradient-to-tr from-stone-700 to-stone-900 flex items-center justify-center text-4xl text-white shadow-inner relative">
                            🍲
                            <div class="absolute bottom-2.5 right-2.5 bg-black/60 backdrop-blur-sm text-white px-2 py-0.5 rounded-full text-[10px] font-bold">
                                <?= (int)$item['total_acessos'] ?> <?= (int)$item['total_acessos'] === 1 ? 'visita' : 'visitas' ?>
                            </div>
                        </div>

                        <div class="p-5">
                            <?php if (!empty($item['compatibilidades'])): ?>
                                <div class="flex flex-wrap gap-1 mb-2">
                                    <?php foreach ($item['compatibilidades'] as $comp): ?>
                                        <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">
                                            <?= htmlspecialchars($comp['nome']) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <h3 class="font-bold text-stone-900 group-hover:text-amber-600 transition-colors line-clamp-1 mb-1">
                                <a href="/receita?id=<?= (int)$item['id'] ?>">
                                    <?= htmlspecialchars($item['titulo']) ?>
                                </a>
                            </h3>

                            <p class="text-xs text-stone-600 line-clamp-2 leading-relaxed mb-4">
                                <?= htmlspecialchars($item['descricao']) ?>
                            </p>
                        </div>
                    </div>

                    <div class="px-5 pb-5 pt-3 border-t border-stone-100 flex items-center justify-between text-xs">
                        <span class="text-stone-400">
                            Acessado em: <?= date('d/m/Y H:i', strtotime($item['ultimo_acesso'])) ?>
                        </span>
                        <a href="/receita?id=<?= (int)$item['id'] ?>" class="font-bold text-amber-600 hover:text-amber-700">
                            Rever &rarr;
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
