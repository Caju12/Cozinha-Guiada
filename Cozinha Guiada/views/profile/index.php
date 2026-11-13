<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: views/profile/index.php
// DESCRIÇÃO: Gestão de perfil, preferências alimentares e transição para produtor
// ATENDE AOS REQUISITOS: RF06, RF07, RF30, CA06, CA07, CA30
// ==============================================================================

$pageTitle = 'Meu Perfil — Cozinha Guiada';
require VIEWS_PATH . '/layouts/header.php';

$userPreferences = $user['preferencias'] ?? [];
?>

<div class="max-w-4xl mx-auto my-10 px-4">
    
    <!-- CABEÇALHO DO PERFIL -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-stone-200 mb-8">
        <div class="flex flex-col sm:flex-row items-center gap-6">
            
            <!-- AVATAR COM INICIAIS -->
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-400 text-white flex items-center justify-center text-3xl font-extrabold shadow-md flex-shrink-0">
                <?= mb_strtoupper(mb_substr($user['nome'], 0, 1)) ?>
            </div>

            <!-- DETALHES GERAIS -->
            <div class="text-center sm:text-left flex-grow">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5 mb-1">
                    <h1 class="text-2xl font-extrabold text-stone-900"><?= htmlspecialchars($user['nome']) ?></h1>
                    
                    <?php if ($user['perfil'] === ROLE_ADMIN): ?>
                        <span class="bg-rose-100 text-rose-800 font-bold px-3 py-0.5 rounded-full text-xs">Administrador</span>
                    <?php elseif ($user['perfil'] === ROLE_PRODUTOR): ?>
                        <span class="bg-indigo-100 text-indigo-800 font-bold px-3 py-0.5 rounded-full text-xs">Produtor de Conteúdo / Chef</span>
                    <?php else: ?>
                        <span class="bg-amber-100 text-amber-800 font-bold px-3 py-0.5 rounded-full text-xs">Membro Cadastrado</span>
                    <?php endif; ?>
                </div>

                <p class="text-sm text-stone-500"><?= htmlspecialchars($user['email']) ?></p>
                <p class="text-xs text-stone-400 mt-1">Membro desde: <?= date('d/m/Y', strtotime($user['criado_em'])) ?></p>
            </div>

        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- COLUNA PRINCIPAL: FORMULÁRIO DE EDIÇÃO E PREFERÊNCIAS -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-stone-200">
                <h2 class="text-lg font-bold text-stone-900 mb-4 pb-3 border-b border-stone-100 flex items-center gap-2">
                    <span>✏️</span> Informações Pessoais
                </h2>

                <form action="/perfil" method="POST" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">

                    <!-- NOME -->
                    <div>
                        <label for="nome" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                            Nome Completo *
                        </label>
                        <input 
                            type="text" 
                            id="nome" 
                            name="nome" 
                            required 
                            value="<?= htmlspecialchars($user['nome']) ?>"
                            class="w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm transition-all shadow-sm"
                        >
                    </div>

                    <!-- E-MAIL (READ-ONLY) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-500 mb-1.5">
                            E-mail Cadastrado
                        </label>
                        <input 
                            type="email" 
                            disabled 
                            value="<?= htmlspecialchars($user['email']) ?>"
                            class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-100 text-stone-500 text-sm cursor-not-allowed"
                        >
                        <span class="text-[11px] text-stone-400 mt-1 block">O e-mail é a chave de acesso da sua conta e não pode ser alterado diretamente.</span>
                    </div>

                    <!-- BIOGRAFIA PÚBLICA -->
                    <div>
                        <label for="bio" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                            Biografia / Sobre você
                        </label>
                        <textarea 
                            id="bio" 
                            name="bio" 
                            rows="3" 
                            placeholder="Compartilhe um pouco da sua experiência na cozinha..."
                            class="w-full px-4 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm transition-all shadow-sm"
                        ><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    </div>

                    <!-- PREFERÊNCIAS ALIMENTARES -->
                    <div class="pt-4 border-t border-stone-100">
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">
                            Minhas Preferências Alimentares
                        </label>
                        <p class="text-xs text-stone-500 mb-3">
                            Marque suas restrições para personalizar filtros e destaques na plataforma:
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <?php foreach ($compatibilidades as $comp): ?>
                                <?php $isChecked = in_array((int)$comp['id'], array_map('intval', $userPreferences), true); ?>
                                <label class="flex items-center gap-2.5 p-3 rounded-xl border <?= $isChecked ? 'border-amber-400 bg-amber-50/60' : 'border-stone-200 bg-stone-50' ?> hover:bg-amber-50/50 hover:border-amber-300 cursor-pointer transition-colors text-xs font-medium text-stone-700">
                                    <input 
                                        type="checkbox" 
                                        name="preferencias[]" 
                                        value="<?= (int)$comp['id'] ?>"
                                        <?= $isChecked ? 'checked' : '' ?>
                                        class="rounded border-stone-300 text-amber-600 focus:ring-amber-500 h-4 w-4"
                                    >
                                    <div>
                                        <span class="font-bold block"><?= htmlspecialchars($comp['nome']) ?></span>
                                        <?php if (!empty($comp['descricao'])): ?>
                                            <span class="text-[10px] text-stone-500 block leading-tight"><?= htmlspecialchars($comp['descricao']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full sm:w-auto px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm rounded-xl shadow-md hover:shadow-lg transition-all focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
                    >
                        Salvar Alterações
                    </button>
                </form>
            </div>
        </div>

        <!-- COLUNA LATERAL: STATUS E UPGRADE DE PERFIL -->
        <div class="space-y-6">
            
            <!-- CARD DE TRANSIÇÃO PARA PRODUTOR -->
            <div class="bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-white p-6 rounded-2xl border border-amber-200/80 shadow-sm">
                
                <?php if ($user['perfil'] === ROLE_CADASTRADO): ?>
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-xl mb-3 shadow">
                        👩‍🍳
                    </div>
                    <h3 class="text-base font-bold text-stone-900 mb-2">Torne-se um Produtor!</h3>
                    <p class="text-xs text-stone-600 leading-relaxed mb-4">
                        Quer compartilhar suas próprias receitas na comunidade? Como produtor de conteúdo, você poderá cadastrar receitas completas, com fotos, modo de preparo guiado e opções de substituição.
                    </p>

                    <form action="/perfil/tornar-produtor" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                        <button 
                            type="submit" 
                            onclick="return confirm('Confirmar transição para Produtor de Conteúdo? Você poderá publicar suas receitas imediatamente!')"
                            class="w-full py-2.5 px-4 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow transition-all"
                        >
                            Quero me tornar Produtor
                        </button>
                    </form>

                <?php elseif ($user['perfil'] === ROLE_PRODUTOR): ?>
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xl mb-3 shadow">
                        🌟
                    </div>
                    <h3 class="text-base font-bold text-stone-900 mb-2">Perfil de Produtor Ativo</h3>
                    <p class="text-xs text-stone-600 leading-relaxed mb-4">
                        Você possui privilégios de publicação e gerenciamento de receitas na plataforma.
                    </p>
                    <div class="p-3 bg-white rounded-xl border border-indigo-100 text-xs text-indigo-900 font-medium">
                        Em breve na Etapa 6: Seu Painel com ferramentas avançadas para cadastrar e gerenciar receitas com modo guiado!
                    </div>

                <?php elseif ($user['perfil'] === ROLE_ADMIN): ?>
                    <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center text-xl mb-3 shadow">
                        🛡️
                    </div>
                    <h3 class="text-base font-bold text-stone-900 mb-2">Painel de Administrador</h3>
                    <p class="text-xs text-stone-600 leading-relaxed mb-4">
                        Sua conta possui acesso total para moderação de receitas, gestão de denúncias e usuários.
                    </p>
                    <div class="p-3 bg-white rounded-xl border border-rose-100 text-xs text-rose-900 font-medium">
                        Em breve na Etapa 7: Área de moderação de denúncias e gerenciamento global.
                    </div>
                <?php endif; ?>

            </div>

            <!-- CARD DE SEGURANÇA -->
            <div class="bg-white p-6 rounded-2xl border border-stone-200 shadow-sm text-xs text-stone-500 space-y-2">
                <h4 class="font-bold text-stone-800 text-sm flex items-center gap-1.5">
                    <span>🔒</span> Privacidade & Segurança
                </h4>
                <p>
                    Suas senhas são criptografadas com padrão industrial Bcrypt. Nenhum administrador tem acesso à sua senha em texto claro.
                </p>
                <div class="pt-2">
                    <a href="/logout" class="inline-flex items-center gap-1 text-rose-600 hover:text-rose-700 font-bold">
                        <span>Encerrar sessão com segurança &rarr;</span>
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
