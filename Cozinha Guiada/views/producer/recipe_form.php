<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: views/producer/recipe_form.php
// DESCRIÇÃO: Formulário dinâmico de cadastro e edição de receitas
// ==============================================================================

$isEdit = ($mode === 'edit');
$pageTitle = ($isEdit ? 'Editar Receita' : 'Nova Receita') . ' — Cozinha Guiada';
require VIEWS_PATH . '/layouts/header.php';

$selectedCatIds = array_column($recipe['categorias'] ?? [], 'id');
$selectedCompIds = array_column($recipe['compatibilidades'] ?? [], 'id');
$selectedUtIds = array_column($recipe['utensilios'] ?? [], 'id');
$ingredients = !empty($recipe['ingredientes']) ? $recipe['ingredientes'] : [
    ['nome' => '', 'quantidade' => '', 'unidade' => '', 'observacao' => '', 'substituicoes' => []]
];
$steps = !empty($recipe['etapas']) ? $recipe['etapas'] : [
    ['descricao' => '']
];
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- CABEÇALHO DO FORMULÁRIO -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <a href="/produtor/painel" class="text-xs font-bold text-stone-500 hover:text-amber-600 mb-1 inline-block">
                &larr; Voltar ao Painel do Produtor
            </a>
            <h1 class="text-3xl font-extrabold text-stone-900 tracking-tight">
                <?= $isEdit ? 'Editar Receita' : 'Criar Nova Receita' ?>
            </h1>
            <p class="text-sm text-stone-500 mt-1">Preencha os detalhes e opções inclusivas para orientar outros cozinheiros</p>
        </div>
    </div>

    <form 
        action="<?= $isEdit ? '/produtor/receita/editar' : '/produtor/receita/nova' ?>" 
        method="POST" 
        enctype="multipart/form-data"
        class="space-y-8"
    >
        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int)$recipe['id'] ?>">
        <?php endif; ?>

        <!-- SEÇÃO 1: DADOS GERAIS -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-5">
            <h2 class="text-lg font-bold text-stone-900 pb-3 border-b border-stone-100 flex items-center gap-2">
                <span>📝</span> Informações Principais
            </h2>

            <div>
                <label for="titulo" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                    Título da Receita *
                </label>
                <input 
                    type="text" 
                    id="titulo" 
                    name="titulo" 
                    required 
                    value="<?= htmlspecialchars($recipe['titulo']) ?>"
                    placeholder="ex: Panqueca Fit de Aveia e Banana" 
                    class="w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm shadow-sm"
                >
            </div>

            <div>
                <label for="descricao" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                    Descrição Culinária *
                </label>
                <textarea 
                    id="descricao" 
                    name="descricao" 
                    rows="3" 
                    required 
                    placeholder="Apresente a proposta da receita, textura, aroma ou para qual ocasião é ideal..."
                    class="w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm shadow-sm"
                ><?= htmlspecialchars($recipe['descricao']) ?></textarea>
            </div>

            <!-- UPLOAD DE IMAGEM -->
            <div>
                <label for="imagem" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                    Foto da Receita (Opcional - JPG, PNG ou WEBP até 5MB)
                </label>
                <input 
                    type="file" 
                    id="imagem" 
                    name="imagem" 
                    accept="image/jpeg,image/png,image/webp"
                    class="w-full px-4 py-2 rounded-xl border border-stone-300 text-xs text-stone-500 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200 cursor-pointer"
                >
                <?php if ($isEdit && !empty($recipe['imagens'])): ?>
                    <p class="text-xs text-stone-400 mt-1">Imagem atual vinculada à receita. Enviar um novo arquivo substituirá a foto anterior.</p>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="tempo" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                        Tempo de Preparo (minutos) *
                    </label>
                    <input 
                        type="number" 
                        id="tempo" 
                        name="tempo_preparo_minutos" 
                        required 
                        min="1"
                        value="<?= (int)$recipe['tempo_preparo_minutos'] ?>" 
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm shadow-sm"
                    >
                </div>

                <div>
                    <label for="rendimento" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                        Rendimento das Porções
                    </label>
                    <input 
                        type="text" 
                        id="rendimento" 
                        name="rendimento" 
                        value="<?= htmlspecialchars($recipe['rendimento'] ?? '') ?>"
                        placeholder="ex: 4 porções, 12 fatias, 2 pessoas" 
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm shadow-sm"
                    >
                </div>
            </div>
        </div>

        <!-- SEÇÃO 2: PARÂMETROS CULINÁRIOS CRÍTICOS -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-5">
            <h2 class="text-lg font-bold text-stone-900 pb-3 border-b border-stone-100 flex items-center gap-2">
                <span>🔥</span> Temperatura, Fogo e Ponto Esperado
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="temperatura" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                        Temperatura do Forno / Cocção
                    </label>
                    <input 
                        type="text" 
                        id="temperatura" 
                        name="temperatura" 
                        value="<?= htmlspecialchars($recipe['temperatura'] ?? '') ?>"
                        placeholder="ex: 180°C, 200°C pré-aquecido" 
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm shadow-sm"
                    >
                </div>

                <div>
                    <label for="fogo" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                        Intensidade do Fogo
                    </label>
                    <input 
                        type="text" 
                        id="fogo" 
                        name="intensidade_fogo" 
                        value="<?= htmlspecialchars($recipe['intensidade_fogo'] ?? '') ?>"
                        placeholder="ex: Fogo baixo, Fogo médio-alto" 
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm shadow-sm"
                    >
                </div>
            </div>

            <div>
                <label for="ponto" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                    Ponto Esperado do Prato (Como saber que está pronto?)
                </label>
                <input 
                    type="text" 
                    id="ponto" 
                    name="ponto_esperado" 
                    value="<?= htmlspecialchars($recipe['ponto_esperado'] ?? '') ?>"
                    placeholder="ex: Palito seco ao espetar no centro, Dourado e crocante por fora" 
                    class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm shadow-sm"
                >
            </div>
        </div>

        <!-- SEÇÃO 3: CLASSIFICAÇÃO E RESTRIÇÕES ALIMENTARES -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-6">
            
            <!-- CATEGORIAS -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-stone-700 mb-2">
                    Categorias da Receita *
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <?php foreach ($categorias as $cat): ?>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-stone-200 bg-stone-50 hover:bg-amber-50 cursor-pointer text-xs">
                            <input 
                                type="checkbox" 
                                name="categorias[]" 
                                value="<?= (int)$cat['id'] ?>"
                                <?= in_array((int)$cat['id'], $selectedCatIds, true) ? 'checked' : '' ?>
                                class="rounded border-stone-300 text-amber-600 focus:ring-amber-500 h-4 w-4"
                            >
                            <span class="font-medium text-stone-800"><?= htmlspecialchars($cat['nome']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- COMPATIBILIDADES ALIMENTARES -->
            <div class="pt-4 border-t border-stone-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-800 mb-1">
                    Compatibilidades e Restrições Atendidas
                </h3>
                <p class="text-xs text-stone-500 mb-3">Marque se sua receita é naturalmente inclusiva ou livre de alergênicos:</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <?php foreach ($compatibilidades as $comp): ?>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-emerald-200 bg-emerald-50/50 hover:bg-emerald-50 cursor-pointer text-xs">
                            <input 
                                type="checkbox" 
                                name="compatibilidades[]" 
                                value="<?= (int)$comp['id'] ?>"
                                <?= in_array((int)$comp['id'], $selectedCompIds, true) ? 'checked' : '' ?>
                                class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                            >
                            <span class="font-bold text-emerald-950"><?= htmlspecialchars($comp['nome']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- UTENSÍLIOS -->
            <div class="pt-4 border-t border-stone-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-stone-700 mb-2">
                    Utensílios Necessários
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <?php foreach ($utensilios as $ut): ?>
                        <label class="flex items-center gap-2 p-2 rounded-xl border border-stone-200 bg-stone-50 text-xs cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="utensilios[]" 
                                value="<?= (int)$ut['id'] ?>"
                                <?= in_array((int)$ut['id'], $selectedUtIds, true) ? 'checked' : '' ?>
                                class="rounded border-stone-300 text-amber-600 h-3.5 w-3.5"
                            >
                            <span class="text-stone-700"><?= htmlspecialchars($ut['nome']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- SEÇÃO 4: CONSTRUTOR DINÂMICO DE INGREDIENTES E SUBSTITUIÇÕES -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                <div>
                    <h2 class="text-lg font-bold text-stone-900 flex items-center gap-2">
                        <span>🛒</span> Ingredientes e Substituições
                    </h2>
                    <p class="text-xs text-stone-500 mt-0.5">Adicione os ingredientes e informe alternativas inclusivas para restrições alimentares</p>
                </div>
                <button 
                    type="button" 
                    id="btn-add-ingredient" 
                    class="px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold text-xs rounded-xl transition-all"
                >
                    + Novo Ingrediente
                </button>
            </div>

            <div id="ingredients-container" class="space-y-4">
                <?php foreach ($ingredients as $idx => $ing): ?>
                    <?php 
                        $subst = !empty($ing['substituicoes'][0]) ? $ing['substituicoes'][0] : null;
                        $hasSubst = ($subst !== null);
                    ?>
                    <div class="ingredient-row p-4 rounded-2xl bg-stone-50 border border-stone-200 space-y-3 relative transition-all">
                        <button type="button" class="btn-remove-row absolute top-3 right-3 text-stone-400 hover:text-rose-600 font-bold text-xs" title="Remover ingrediente">&times; Remover</button>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-600 mb-1">Ingrediente *</label>
                                <input 
                                    type="text" 
                                    name="ingredientes[<?= $idx ?>][nome]" 
                                    required 
                                    value="<?= htmlspecialchars($ing['ingrediente_nome'] ?? $ing['nome'] ?? '') ?>"
                                    placeholder="ex: Farinha de trigo, Leite de vaca" 
                                    class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs"
                                >
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-600 mb-1">Quantidade</label>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    name="ingredientes[<?= $idx ?>][quantidade]" 
                                    value="<?= !empty($ing['quantidade']) ? (float)$ing['quantidade'] : '' ?>"
                                    placeholder="ex: 2" 
                                    class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs"
                                >
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-600 mb-1">Unidade</label>
                                <input 
                                    type="text" 
                                    name="ingredientes[<?= $idx ?>][unidade]" 
                                    value="<?= htmlspecialchars($ing['unidade'] ?? '') ?>"
                                    placeholder="ex: xícaras, ml, g, un" 
                                    class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-600 mb-1">Observação do Ingrediente (Opcional)</label>
                            <input 
                                type="text" 
                                name="ingredientes[<?= $idx ?>][observacao]" 
                                value="<?= htmlspecialchars($ing['observacao'] ?? '') ?>"
                                placeholder="ex: peneirada, em temperatura ambiente" 
                                class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs"
                            >
                        </div>

                        <!-- SEÇÃO DE SUBSTITUIÇÃO -->
                        <div class="pt-2 border-t border-stone-200/60">
                            <button type="button" class="btn-toggle-subst text-xs font-bold text-emerald-700 hover:underline flex items-center gap-1">
                                <span>💡 <?= $hasSubst ? 'Editar Opção de Substituição' : '+ Adicionar Opção de Substituição Inclusiva' ?></span>
                            </button>

                            <div class="subst-fields <?= $hasSubst ? '' : 'hidden' ?> mt-3 p-3 rounded-xl bg-emerald-50/70 border border-emerald-200 space-y-2">
                                <div>
                                    <label class="block text-[10px] font-bold uppercase text-emerald-900 mb-0.5">Ingrediente Substituto *</label>
                                    <input 
                                        type="text" 
                                        name="ingredientes[<?= $idx ?>][substituto_texto]" 
                                        value="<?= htmlspecialchars($subst['texto_substituto'] ?? '') ?>"
                                        placeholder="ex: Farinha de arroz ou mix sem glúten" 
                                        class="w-full px-3 py-1.5 rounded-lg border border-emerald-300 text-xs bg-white"
                                    >
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-emerald-900 mb-0.5">Proporção Recomendada</label>
                                        <input 
                                            type="text" 
                                            name="ingredientes[<?= $idx ?>][substituto_proporcao]" 
                                            value="<?= htmlspecialchars($subst['proporcao'] ?? '') ?>"
                                            placeholder="ex: Mesma quantidade (1:1)" 
                                            class="w-full px-3 py-1.5 rounded-lg border border-emerald-300 text-xs bg-white"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-emerald-900 mb-0.5">Dica de Aplicação</label>
                                        <input 
                                            type="text" 
                                            name="ingredientes[<?= $idx ?>][substituto_observacao]" 
                                            value="<?= htmlspecialchars($subst['observacao'] ?? '') ?>"
                                            placeholder="ex: Adicionar 1/2 colher de goma xantana" 
                                            class="w-full px-3 py-1.5 rounded-lg border border-emerald-300 text-xs bg-white"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- SEÇÃO 5: CONSTRUTOR DINÂMICO DE ETAPAS -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                <div>
                    <h2 class="text-lg font-bold text-stone-900 flex items-center gap-2">
                        <span>👩‍🍳</span> Etapas do Modo de Preparo
                    </h2>
                    <p class="text-xs text-stone-500 mt-0.5">Ordene as instruções sequenciais para o cozinheiro</p>
                </div>
                <button 
                    type="button" 
                    id="btn-add-step" 
                    class="px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold text-xs rounded-xl transition-all"
                >
                    + Novo Passo
                </button>
            </div>

            <div id="steps-container" class="space-y-3">
                <?php foreach ($steps as $sIdx => $step): ?>
                    <div class="step-row p-4 rounded-2xl bg-stone-50 border border-stone-200 space-y-2 relative transition-all">
                        <div class="flex items-center justify-between">
                            <span class="step-badge px-3 py-1 rounded-lg bg-amber-600 text-white font-extrabold text-xs">
                                Passo <?= $sIdx + 1 ?>
                            </span>
                            <button type="button" class="btn-remove-step text-stone-400 hover:text-rose-600 font-bold text-xs" title="Remover etapa">&times; Remover</button>
                        </div>
                        <textarea 
                            name="etapas[]" 
                            rows="2" 
                            required 
                            placeholder="Descreva esta etapa com clareza para o cozinheiro..."
                            class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs"
                        ><?= htmlspecialchars($step['descricao'] ?? '') ?></textarea>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- SEÇÃO 6: VISIBILIDADE DA RECEITA -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-stone-900 pb-3 border-b border-stone-100 flex items-center gap-2">
                <span>👁️</span> Visibilidade da Receita
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="flex items-start gap-3 p-4 rounded-2xl border cursor-pointer transition-all <?= ($recipe['visibilidade'] ?? '') === VISIBILITY_PUBLIC ? 'border-emerald-500 bg-emerald-50/50' : 'border-stone-200' ?>">
                    <input 
                        type="radio" 
                        name="visibilidade" 
                        value="<?= VISIBILITY_PUBLIC ?>"
                        <?= ($recipe['visibilidade'] ?? '') === VISIBILITY_PUBLIC ? 'checked' : '' ?>
                        class="mt-1 text-emerald-600 focus:ring-emerald-500"
                    >
                    <div>
                        <span class="font-bold text-stone-900 block text-sm">Pública (Recomendado)</span>
                        <span class="text-xs text-stone-500">Visível no catálogo aberto para todos os usuários e visitantes.</span>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-4 rounded-2xl border cursor-pointer transition-all <?= ($recipe['visibilidade'] ?? '') === VISIBILITY_PRIVATE ? 'border-amber-500 bg-amber-50/50' : 'border-stone-200' ?>">
                    <input 
                        type="radio" 
                        name="visibilidade" 
                        value="<?= VISIBILITY_PRIVATE ?>"
                        <?= ($recipe['visibilidade'] ?? '') === VISIBILITY_PRIVATE ? 'checked' : '' ?>
                        class="mt-1 text-amber-600 focus:ring-amber-500"
                    >
                    <div>
                        <span class="font-bold text-stone-900 block text-sm">Privada / Rascunho</span>
                        <span class="text-xs text-stone-500">Apenas você (autor) poderá visualizar e testar esta receita.</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- BOTÕES DE SUBMIT -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="/produtor/painel" class="px-6 py-3 rounded-2xl bg-stone-200 hover:bg-stone-300 text-stone-700 font-bold text-sm transition-all">
                Cancelar
            </a>
            <button 
                type="submit" 
                class="px-8 py-3.5 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-sm shadow-lg hover:shadow-xl transition-all"
            >
                <?= $isEdit ? 'Salvar Alterações' : 'Salvar e Publicar Receita' ?>
            </button>
        </div>

    </form>

</div>

<script src="/js/recipe-builder.js"></script>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
