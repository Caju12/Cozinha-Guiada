<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: views/producer/dashboard.php
// DESCRIÇÃO: Painel de gestão de receitas do Produtor de Conteúdo (RF31 a RF45)
// ==============================================================================

$pageTitle = 'Painel do Produtor — Cozinha Guiada';
require VIEWS_PATH . '/layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- CABEÇALHO DO PAINEL -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-2xl">👩‍🍳</span>
                <h1 class="text-3xl font-extrabold text-stone-900 tracking-tight">Painel do Produtor</h1>
            </div>
            <p class="text-sm text-stone-500">Crie e gerencie suas receitas, etapas e substituições inclusivas</p>
        </div>

        <a 
            href="/produtor/receita/nova" 
            class="inline-flex items-center gap-2 px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm rounded-2xl shadow-md hover:shadow-lg transition-all self-start sm:self-auto"
        >
            <span>✨ Cadastrar Nova Receita</span>
        </a>
    </div>

    <!-- CARDS DE ESTATÍSTICAS RÁPIDAS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl font-bold">
                🍳
            </div>
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-stone-400 block">Minhas Receitas</span>
                <span class="text-2xl font-extrabold text-stone-900"><?= count($recipes) ?></span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-2xl font-bold">
                ❤️
            </div>
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-stone-400 block">Total de Favoritos</span>
                <span class="text-2xl font-extrabold text-stone-900">
                    <?= array_sum(array_column($recipes, 'total_favoritos')) ?>
                </span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl font-bold">
                ⭐
            </div>
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-stone-400 block">Avaliações Recebidas</span>
                <span class="text-2xl font-extrabold text-stone-900">
                    <?= array_sum(array_column($recipes, 'total_avaliacoes')) ?>
                </span>
            </div>
        </div>
    </div>

    <?php if (empty($recipes)): ?>
        
        <!-- ESTADO VAZIO -->
        <div class="bg-white p-12 rounded-3xl border border-stone-200 text-center shadow-sm max-w-xl mx-auto my-12">
            <span class="text-5xl block mb-3">🍳📝</span>
            <h2 class="text-lg font-bold text-stone-800 mb-1">Você ainda não publicou nenhuma receita</h2>
            <p class="text-xs text-stone-500 max-w-md mx-auto mb-6">
                Compartilhe seus pratos favoritos com ingredientes estruturados, opções de substituição e passos guiados para a comunidade.
            </p>
            <a href="/produtor/receita/nova" class="inline-block px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow transition-all">
                Criar Minha Primeira Receita
            </a>
        </div>

    <?php else: ?>

        <!-- TABELA / LISTA DE RECEITAS -->
        <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-stone-600">
                    <thead class="bg-stone-50 border-b border-stone-200 text-[11px] font-bold uppercase tracking-wider text-stone-700">
                        <tr>
                            <th class="p-4 sm:p-5">Receita</th>
                            <th class="p-4 sm:p-5">Tempo / Rendimento</th>
                            <th class="p-4 sm:p-5">Visibilidade</th>
                            <th class="p-4 sm:p-5">Avaliação</th>
                            <th class="p-4 sm:p-5 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <?php foreach ($recipes as $rec): ?>
                            <tr class="hover:bg-stone-50/70 transition-colors">
                                
                                <td class="p-4 sm:p-5">
                                    <div class="font-bold text-sm text-stone-900 mb-1">
                                        <a href="/receita?id=<?= (int)$rec['id'] ?>" class="hover:text-amber-600 transition-colors">
                                            <?= htmlspecialchars($rec['titulo']) ?>
                                        </a>
                                    </div>
                                    <div class="flex flex-wrap gap-1">
                                        <?php foreach ($rec['compatibilidades'] as $comp): ?>
                                            <span class="bg-emerald-50 text-emerald-800 text-[9px] font-bold px-1.5 py-0.5 rounded">
                                                <?= htmlspecialchars($comp['nome']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>

                                <td class="p-4 sm:p-5 whitespace-nowrap">
                                    <span class="font-bold text-stone-800">⏱️ <?= (int)$rec['tempo_preparo_minutos'] ?> min</span>
                                    <span class="block text-stone-400 text-[10px]"><?= htmlspecialchars($rec['rendimento'] ?: 'Livre') ?></span>
                                </td>

                                <td class="p-4 sm:p-5 whitespace-nowrap">
                                    <form action="/produtor/receita/visibilidade" method="POST" class="inline">
                                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= (int)$rec['id'] ?>">
                                        <button 
                                            type="submit" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase transition-all <?= $rec['visibilidade'] === VISIBILITY_PUBLIC ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-amber-100 text-amber-800 hover:bg-amber-200' ?>"
                                            title="Clique para alternar entre Pública e Privada"
                                        >
                                            <span><?= $rec['visibilidade'] === VISIBILITY_PUBLIC ? '🌐 Pública' : '🔒 Privada' ?></span>
                                        </button>
                                    </form>
                                </td>

                                <td class="p-4 sm:p-5 whitespace-nowrap">
                                    <span class="font-bold text-stone-800 text-xs">★ <?= number_format((float)$rec['media_avaliacao'], 1) ?></span>
                                    <span class="text-[10px] text-stone-400 block"><?= (int)$rec['total_avaliacoes'] ?> votos • <?= (int)$rec['total_favoritos'] ?> favoritos</span>
                                </td>

                                <td class="p-4 sm:p-5 text-right whitespace-nowrap space-x-2">
                                    <a 
                                        href="/receita?id=<?= (int)$rec['id'] ?>" 
                                        class="inline-block px-3 py-1.5 rounded-xl border border-stone-200 hover:bg-stone-100 text-stone-700 font-bold transition-all"
                                    >
                                        Ver
                                    </a>

                                    <a 
                                        href="/produtor/receita/editar?id=<?= (int)$rec['id'] ?>" 
                                        class="inline-block px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold transition-all"
                                    >
                                        Editar
                                    </a>

                                    <form action="/produtor/receita/excluir" method="POST" class="inline" onsubmit="return confirm('Deseja realmente excluir esta receita permanentemente? Esta ação não pode ser desfeita.')">
                                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= (int)$rec['id'] ?>">
                                        <button 
                                            type="submit" 
                                            class="inline-block px-3 py-1.5 rounded-xl text-rose-600 hover:bg-rose-50 font-bold transition-all"
                                        >
                                            Excluir
                                        </button>
                                    </form>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>

</div>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
