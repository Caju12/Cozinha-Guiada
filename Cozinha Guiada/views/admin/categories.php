<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: views/admin/categories.php
// DESCRIÇÃO: Gestão e CRUD de categorias de receitas (RF51, CA51)
// ==============================================================================

$pageTitle = 'Gestão de Categorias — Cozinha Guiada';
require VIEWS_PATH . '/layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- CABEÇALHO -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <a href="/admin" class="text-xs font-bold text-stone-500 hover:text-amber-600 mb-1 inline-block">&larr; Voltar ao Dashboard</a>
            <h1 class="text-3xl font-extrabold text-stone-900 tracking-tight">Categorias de Receitas</h1>
            <p class="text-sm text-stone-500">Crie e edite as categorias oficiais disponíveis para os produtores</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- FORMULÁRIO DE NOVA CATEGORIA -->
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm sticky top-24">
                <h2 class="text-base font-bold text-stone-900 mb-4 pb-2 border-b border-stone-100 flex items-center gap-2">
                    <span>➕</span> Nova Categoria
                </h2>

                <form action="/admin/categorias/criar" method="POST" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">

                    <div>
                        <label for="nome" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">
                            Nome da Categoria *
                        </label>
                        <input 
                            type="text" 
                            id="nome" 
                            name="nome" 
                            required 
                            placeholder="ex: Risotos, Bebidas Quentes"
                            class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs shadow-sm"
                        >
                    </div>

                    <div>
                        <label for="slug" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">
                            Slug na URL (Opcional)
                        </label>
                        <input 
                            type="text" 
                            id="slug" 
                            name="slug" 
                            placeholder="ex: risotos, bebidas-quentes"
                            class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs shadow-sm"
                        >
                    </div>

                    <div>
                        <label for="descricao" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">
                            Descrição
                        </label>
                        <textarea 
                            id="descricao" 
                            name="descricao" 
                            rows="3" 
                            placeholder="Breve texto sobre as receitas desta categoria..."
                            class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs shadow-sm"
                        ></textarea>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow transition-all"
                    >
                        Criar Categoria
                    </button>
                </form>
            </div>
        </div>

        <!-- LISTAGEM DE CATEGORIAS CADASTRADAS -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-stone-600">
                        <thead class="bg-stone-50 border-b border-stone-200 text-[11px] font-bold uppercase tracking-wider text-stone-700">
                            <tr>
                                <th class="p-4 sm:p-5">Categoria</th>
                                <th class="p-4 sm:p-5">Slug</th>
                                <th class="p-4 sm:p-5 text-center">Receitas</th>
                                <th class="p-4 sm:p-5 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            <?php foreach ($categories as $cat): ?>
                                <tr class="hover:bg-stone-50/70 transition-colors">
                                    
                                    <td class="p-4 sm:p-5">
                                        <div class="font-bold text-stone-900 text-sm"><?= htmlspecialchars($cat['nome']) ?></div>
                                        <?php if (!empty($cat['descricao'])): ?>
                                            <div class="text-stone-400 text-[11px] mt-0.5"><?= htmlspecialchars($cat['descricao']) ?></div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="p-4 sm:p-5 whitespace-nowrap font-mono text-[11px] text-stone-500">
                                        <?= htmlspecialchars($cat['slug']) ?>
                                    </td>

                                    <td class="p-4 sm:p-5 text-center whitespace-nowrap">
                                        <span class="bg-stone-100 text-stone-700 font-bold px-2 py-0.5 rounded-full text-[10px]">
                                            <?= (int)$cat['total_receitas'] ?>
                                        </span>
                                    </td>

                                    <td class="p-4 sm:p-5 text-right whitespace-nowrap space-x-2">
                                        <button 
                                            type="button" 
                                            onclick="openEditCategory(<?= (int)$cat['id'] ?>, '<?= htmlspecialchars(addslashes($cat['nome'])) ?>', '<?= htmlspecialchars(addslashes($cat['slug'])) ?>', '<?= htmlspecialchars(addslashes($cat['descricao'] ?? '')) ?>')"
                                            class="px-2.5 py-1 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs"
                                        >
                                            Editar
                                        </button>

                                        <form action="/admin/categorias/excluir" method="POST" class="inline" onsubmit="return confirm('Deseja realmente excluir a categoria <?= htmlspecialchars(addslashes($cat['nome'])) ?>?')">
                                            <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                            <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg text-rose-600 hover:bg-rose-50 font-bold text-xs">
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
        </div>

    </div>

</div>

<!-- MODAL DE EDIÇÃO DE CATEGORIA -->
<div id="modal-edit-category" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white max-w-md w-full rounded-3xl p-6 sm:p-8 shadow-2xl">
        <h3 class="text-lg font-bold text-stone-900 mb-4 pb-2 border-b border-stone-100">Editar Categoria</h3>

        <form action="/admin/categorias/editar" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
            <input type="hidden" id="edit-cat-id" name="id" value="">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">Nome *</label>
                <input type="text" id="edit-cat-nome" name="nome" required class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">Slug</label>
                <input type="text" id="edit-cat-slug" name="slug" class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">Descrição</label>
                <textarea id="edit-cat-descricao" name="descricao" rows="3" class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeEditCategory()" class="px-4 py-2 rounded-xl bg-stone-100 text-stone-700 font-bold text-xs">Cancelar</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditCategory(id, nome, slug, desc) {
        document.getElementById('edit-cat-id').value = id;
        document.getElementById('edit-cat-nome').value = nome;
        document.getElementById('edit-cat-slug').value = slug;
        document.getElementById('edit-cat-descricao').value = desc;

        const modal = document.getElementById('modal-edit-category');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditCategory() {
        const modal = document.getElementById('modal-edit-category');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }
</script>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
