<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: views/auth/register.php
// DESCRIÇÃO: Tela de Cadastro de Usuários e Preferências Iniciais (RF04, RF07)
// ==============================================================================

$pageTitle = 'Criar Conta — Cozinha Guiada';
require_once VIEWS_PATH . '/layouts/header.php';
?>

<div class="max-w-lg mx-auto my-12 px-4">
    <div class="bg-white p-8 rounded-2xl shadow-sm border border-stone-200">
        
        <div class="text-center mb-8">
            <span class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 text-2xl mb-3 shadow-inner">
                📝
            </span>
            <h1 class="text-2xl font-extrabold text-stone-900 tracking-tight">Crie sua Conta Gratuita</h1>
            <p class="text-sm text-stone-500 mt-1">Junte-se à comunidade e personalize suas preferências culinárias</p>
        </div>

        <form action="/cadastro" method="POST" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">

            <!-- NOME COMPLETO -->
            <div>
                <label for="nome" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                    Nome Completo *
                </label>
                <input 
                    type="text" 
                    id="nome" 
                    name="nome" 
                    required 
                    autocomplete="name"
                    placeholder="Seu nome ou apelido na cozinha"
                    class="w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm transition-all shadow-sm"
                >
            </div>

            <!-- E-MAIL -->
            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                    Endereço de E-mail *
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    autocomplete="email"
                    placeholder="seu.email@exemplo.com"
                    class="w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm transition-all shadow-sm"
                >
            </div>

            <!-- SENHA E CONFIRMAÇÃO -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="senha" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                        Senha (mín. 6 dígitos) *
                    </label>
                    <input 
                        type="password" 
                        id="senha" 
                        name="senha" 
                        required 
                        minlength="6"
                        autocomplete="new-password"
                        placeholder="••••••••"
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm transition-all shadow-sm"
                    >
                </div>
                <div>
                    <label for="senha_confirm" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                        Confirmar Senha *
                    </label>
                    <input 
                        type="password" 
                        id="senha_confirm" 
                        name="senha_confirm" 
                        required 
                        minlength="6"
                        autocomplete="new-password"
                        placeholder="••••••••"
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm transition-all shadow-sm"
                    >
                </div>
            </div>

            <!-- PREFERÊNCIAS ALIMENTARES -->
            <div class="pt-4 border-t border-stone-100">
                <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">
                    Preferências Alimentares (Opcional)
                </label>
                <p class="text-xs text-stone-500 mb-3">
                    Selecione suas restrições ou dietas para destacarmos receitas ideais para você:
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <?php if (!empty($compatibilidades)): ?>
                        <?php foreach ($compatibilidades as $comp): ?>
                            <label class="flex items-center gap-2.5 p-3 rounded-xl border border-stone-200 bg-stone-50 hover:bg-amber-50/50 hover:border-amber-300 cursor-pointer transition-colors text-xs font-medium text-stone-700">
                                <input 
                                    type="checkbox" 
                                    name="preferencias[]" 
                                    value="<?= (int)$comp['id'] ?>"
                                    class="rounded border-stone-300 text-amber-600 focus:ring-amber-500 h-4 w-4"
                                >
                                <span><?= htmlspecialchars($comp['nome']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-xs text-stone-400">Nenhuma classificação disponível no momento.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- BOTÃO DE CADASTRO -->
            <button 
                type="submit" 
                class="w-full py-3.5 px-4 bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm rounded-xl shadow-md hover:shadow-lg transition-all focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
            >
                Finalizar Cadastro
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-stone-100 text-center">
            <p class="text-sm text-stone-600">
                Já possui uma conta? 
                <a href="/login" class="font-bold text-amber-600 hover:text-amber-700 transition-colors">
                    Entrar agora
                </a>
            </p>
        </div>

    </div>
</div>

<?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
