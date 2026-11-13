/**
 * ==============================================================================
 * PROJETO: Cozinha Guiada
 * ARQUIVO: public/js/recipe-builder.js
 * DESCRIÇÃO: Manipulação dinâmica de linhas de ingredientes, substituições e etapas
 * ATENDE AOS REQUISITOS: RF32, RF33, RF43, RF44
 * ==============================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    let ingIndex = document.querySelectorAll('.ingredient-row').length;
    let stepIndex = document.querySelectorAll('.step-row').length;

    const ingContainer = document.getElementById('ingredients-container');
    const stepsContainer = document.getElementById('steps-container');
    const btnAddIng = document.getElementById('btn-add-ingredient');
    const btnAddStep = document.getElementById('btn-add-step');

    // --------------------------------------------------------------------------
    // INGREDIENTES E SUBSTITUIÇÕES (RF32, RF43, RF44)
    // --------------------------------------------------------------------------
    if (btnAddIng && ingContainer) {
        btnAddIng.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'ingredient-row p-4 rounded-2xl bg-stone-50 border border-stone-200 space-y-3 relative transition-all';
            row.innerHTML = `
                <button type="button" class="btn-remove-row absolute top-3 right-3 text-stone-400 hover:text-rose-600 font-bold text-xs" title="Remover ingrediente">&times; Remover</button>
                
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-600 mb-1">Ingrediente *</label>
                        <input type="text" name="ingredientes[${ingIndex}][nome]" required placeholder="ex: Farinha de trigo, Leite de vaca" class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-600 mb-1">Quantidade</label>
                        <input type="number" step="0.01" name="ingredientes[${ingIndex}][quantidade]" placeholder="ex: 2" class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-600 mb-1">Unidade</label>
                        <input type="text" name="ingredientes[${ingIndex}][unidade]" placeholder="ex: xícaras, ml, g, un" class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-600 mb-1">Observação do Ingrediente (Opcional)</label>
                    <input type="text" name="ingredientes[${ingIndex}][observacao]" placeholder="ex: peneirada, em temperatura ambiente" class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs">
                </div>

                <!-- SEÇÃO DE SUBSTITUIÇÃO (RF13, RF43, RF44) -->
                <div class="pt-2 border-t border-stone-200/60">
                    <button type="button" class="btn-toggle-subst text-xs font-bold text-emerald-700 hover:underline flex items-center gap-1">
                        <span>💡 + Adicionar Opção de Substituição Inclusiva</span>
                    </button>

                    <div class="subst-fields hidden mt-3 p-3 rounded-xl bg-emerald-50/70 border border-emerald-200 space-y-2">
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-emerald-900 mb-0.5">Ingrediente Substituto *</label>
                            <input type="text" name="ingredientes[${ingIndex}][substituto_texto]" placeholder="ex: Farinha de arroz ou mix sem glúten" class="w-full px-3 py-1.5 rounded-lg border border-emerald-300 text-xs bg-white">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-emerald-900 mb-0.5">Proporção Recomendada</label>
                                <input type="text" name="ingredientes[${ingIndex}][substituto_proporcao]" placeholder="ex: Mesma quantidade (1:1)" class="w-full px-3 py-1.5 rounded-lg border border-emerald-300 text-xs bg-white">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-emerald-900 mb-0.5">Dica de Aplicação</label>
                                <input type="text" name="ingredientes[${ingIndex}][substituto_observacao]" placeholder="ex: Adicionar 1/2 colher de goma xantana" class="w-full px-3 py-1.5 rounded-lg border border-emerald-300 text-xs bg-white">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            ingContainer.appendChild(row);
            ingIndex++;
            bindRowEvents(row);
        });
    }

    // --------------------------------------------------------------------------
    // ETAPAS DO MODO DE PREPARO (RF33)
    // --------------------------------------------------------------------------
    if (btnAddStep && stepsContainer) {
        btnAddStep.addEventListener('click', () => {
            const currentStepsCount = stepsContainer.querySelectorAll('.step-row').length + 1;
            const row = document.createElement('div');
            row.className = 'step-row p-4 rounded-2xl bg-stone-50 border border-stone-200 space-y-2 relative transition-all';
            row.innerHTML = `
                <div class="flex items-center justify-between">
                    <span class="step-badge px-3 py-1 rounded-lg bg-amber-600 text-white font-extrabold text-xs">
                        Passo ${currentStepsCount}
                    </span>
                    <button type="button" class="btn-remove-step text-stone-400 hover:text-rose-600 font-bold text-xs" title="Remover etapa">&times; Remover</button>
                </div>
                <textarea name="etapas[]" rows="2" required placeholder="Descreva esta etapa com clareza para o cozinheiro..." class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs"></textarea>
            `;
            stepsContainer.appendChild(row);
            stepIndex++;
            bindStepEvents(row);
        });
    }

    // Vincula eventos nos elementos existentes
    document.querySelectorAll('.ingredient-row').forEach(bindRowEvents);
    document.querySelectorAll('.step-row').forEach(bindStepEvents);

    function bindRowEvents(row) {
        const btnRemove = row.querySelector('.btn-remove-row');
        if (btnRemove) {
            btnRemove.addEventListener('click', () => {
                if (document.querySelectorAll('.ingredient-row').length > 1) {
                    row.remove();
                } else {
                    alert('A receita precisa conter pelo menos 1 ingrediente.');
                }
            });
        }

        const btnToggleSubst = row.querySelector('.btn-toggle-subst');
        const substFields = row.querySelector('.subst-fields');
        if (btnToggleSubst && substFields) {
            btnToggleSubst.addEventListener('click', () => {
                substFields.classList.toggle('hidden');
            });
        }
    }

    function bindStepEvents(row) {
        const btnRemove = row.querySelector('.btn-remove-step');
        if (btnRemove) {
            btnRemove.addEventListener('click', () => {
                if (document.querySelectorAll('.step-row').length > 1) {
                    row.remove();
                    renumberSteps();
                } else {
                    alert('A receita precisa conter pelo menos 1 etapa no modo de preparo.');
                }
            });
        }
    }

    function renumberSteps() {
        if (!stepsContainer) return;
        const stepBadges = stepsContainer.querySelectorAll('.step-badge');
        stepBadges.forEach((badge, idx) => {
            badge.textContent = `Passo ${idx + 1}`;
        });
    }
});
