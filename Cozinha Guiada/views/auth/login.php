<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: views/auth/login.php
// DESCRIÇÃO: Tela de Login (RF05, CA05, RNF07)
// ==============================================================================

$pageTitle = 'Entrar — Cozinha Guiada';
require_once VIEWS_PATH . '/layouts/header.php';
?>

<div class="max-w-md mx-auto my-12 px-4">
    <div class="bg-white p-8 rounded-2xl shadow-sm border border-stone-200">
        
        <div class="text-center mb-8">
            <span class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 text-2xl mb-3 shadow-inner">
                🔑
            </span>
            <h1 class="text-2xl font-extrabold text-stone-900 tracking-tight">Bem-vindo(a) de volta!</h1>
            <p class="text-sm text-stone-500 mt-1">Insira suas credenciais para acessar sua conta</p>
        </div>

        <form action="/login" method="POST" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">

            <!-- CAMPO E-MAIL -->
            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                    E-mail
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

            <!-- CAMPO SENHA -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="senha" class="block text-xs font-bold uppercase tracking-wider text-stone-700">
                        Senha
                    </label>
                </div>
                <input 
                    type="password" 
                    id="senha" 
                    name="senha" 
                    required 
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm transition-all shadow-sm"
                >
            </div>

            <!-- BOTÃO DE SUBMIT -->
            <button 
                type="submit" 
                class="w-full py-3 px-4 bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm rounded-xl shadow-md hover:shadow-lg transition-all focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
            >
                Entrar na Plataforma
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-stone-100 text-center">
            <p class="text-sm text-stone-600">
                Ainda não tem cadastro? 
                <a href="/cadastro" class="font-bold text-amber-600 hover:text-amber-700 transition-colors">
                    Criar conta grátis
                </a>
            </p>
        </div>

    </div>

    <!-- BOX DE CONTAS DE DEMONSTRAÇÃO (PARA AVALIAÇÃO / TESTES) -->
    <div class="mt-6 p-4 bg-amber-50/70 border border-amber-200 rounded-xl text-xs text-amber-900">
        <p class="font-bold mb-1 flex items-center gap-1.5">
            <span>💡</span> Contas de demonstração pré-configuradas:
        </p>
        <ul class="space-y-1 text-amber-800">
            <li><strong>Admin:</strong> <code>admin@cozinhaguiada.com</code> (Senha: <code>Admin@2026</code>)</li>
            <li><strong>Chef Produtor:</strong> <code>chef@cozinhaguiada.com</code> (Senha: <code>Admin@2026</code>)</li>
            <li><strong>Leitor Comum:</strong> <code>leitor@cozinhaguiada.com</code> (Senha: <code>Admin@2026</code>)</li>
        </ul>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
