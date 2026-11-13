<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: views/recipes/show.php
// DESCRIÇÃO: Página de visualização detalhada da receita
// ATENDE AOS REQUISITOS: RF12, RF13, RF14, RF15, RF16, RF17, RF53
// ==============================================================================

$pageTitle = htmlspecialchars($recipe['titulo']) . ' — Cozinha Guiada';
require_once VIEWS_PATH . '/layouts/header.php';
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- NAVEGAÇÃO BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs text-stone-500 mb-6">
        <a href="/" class="hover:text-amber-600">Início</a>
        <span>&rsaquo;</span>
        <a href="/receitas" class="hover:text-amber-600">Receitas</a>
        <span>&rsaquo;</span>
        <span class="text-stone-800 font-bold truncate"><?= htmlspecialchars($recipe['titulo']) ?></span>
    </nav>

    <!-- CABEÇALHO DA RECEITA -->
    <div class="bg-white p-6 sm:p-10 rounded-3xl shadow-sm border border-stone-200 mb-8">
        
        <!-- BADGES DE COMPATIBILIDADE E CATEGORIAS -->
        <div class="flex flex-wrap items-center gap-2 mb-4">
            <?php foreach ($recipe['compatibilidades'] as $comp): ?>
                <span class="bg-emerald-100 text-emerald-800 font-bold px-3 py-1 rounded-full text-xs uppercase tracking-wider flex items-center gap-1">
                    <span>🌱</span> <?= htmlspecialchars($comp['nome']) ?>
                </span>
            <?php endforeach; ?>

            <?php foreach ($recipe['categorias'] as $cat): ?>
                <span class="bg-amber-100 text-amber-800 font-bold px-3 py-1 rounded-full text-xs">
                    📁 <?= htmlspecialchars($cat['nome']) ?>
                </span>
            <?php endforeach; ?>
        </div>

        <h1 class="text-3xl sm:text-4xl font-extrabold text-stone-900 tracking-tight mb-4 leading-tight">
            <?= htmlspecialchars($recipe['titulo']) ?>
        </h1>

        <p class="text-base text-stone-600 leading-relaxed mb-6">
            <?= nl2br(htmlspecialchars($recipe['descricao'])) ?>
        </p>

        <!-- DADOS DO AUTOR E AVALIAÇÃO -->
        <div class="flex flex-wrap items-center justify-between gap-4 pt-6 border-t border-stone-100 text-xs text-stone-500">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold text-sm shadow">
                    <?= mb_strtoupper(mb_substr($recipe['autor_nome'], 0, 1)) ?>
                </div>
                <div>
                    <span class="block text-stone-400">Criado por</span>
                    <a href="/produtor?id=<?= (int)$recipe['autor_id'] ?>" class="font-bold text-stone-800 hover:text-amber-600 transition-colors text-sm">
                        <?= htmlspecialchars($recipe['autor_nome']) ?>
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <!-- AVALIAÇÃO MÉDIA -->
                <div class="flex items-center gap-1.5">
                    <span class="text-amber-500 text-lg">★</span>
                    <span class="font-extrabold text-stone-800 text-sm">
                        <?= number_format((float)$recipe['media_avaliacao'], 1) ?>
                    </span>
                    <span class="text-stone-400">
                        (<?= (int)$recipe['total_avaliacoes'] ?> <?= (int)$recipe['total_avaliacoes'] === 1 ? 'avaliação' : 'avaliações' ?>)
                    </span>
                </div>

            <div class="flex flex-wrap items-center gap-3">
                
                <!-- BOTÃO DE FAVORITOS -->
                <?php if (Session::has('user_id')): ?>
                    <form action="/favoritos/toggle" method="POST" class="inline">
                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                        <input type="hidden" name="receita_id" value="<?= (int)$recipe['id'] ?>">
                        <button 
                            type="submit" 
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border <?= $isFavorited ? 'border-rose-300 bg-rose-50 text-rose-700 font-extrabold' : 'border-stone-300 bg-white text-stone-700 font-bold hover:bg-stone-50' ?> text-xs shadow-sm transition-all"
                            title="<?= $isFavorited ? 'Remover dos favoritos' : 'Salvar nos favoritos' ?>"
                        >
                            <span><?= $isFavorited ? '❤️' : '🤍' ?></span>
                            <span><?= $isFavorited ? 'Favoritada' : 'Salvar nos Favoritos' ?></span>
                        </button>
                    </form>
                <?php else: ?>
                    <a 
                        href="/login" 
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-stone-300 bg-white text-stone-600 font-bold text-xs shadow-sm hover:bg-stone-50 transition-all"
                        title="Faça login para salvar esta receita"
                    >
                        <span>🤍 Salvar</span>
                    </a>
                <?php endif; ?>

                <!-- BOTÃO DE DENUNCIAR RECEITA -->
                <button 
                    type="button" 
                    onclick="openReportModal('receita', <?= (int)$recipe['id'] ?>, '<?= htmlspecialchars(addslashes($recipe['titulo'])) ?>')"
                    class="p-2.5 rounded-xl border border-stone-200 text-stone-400 hover:text-rose-600 hover:border-rose-200 text-xs transition-colors"
                    title="Denunciar esta receita"
                >
                    🚩
                </button>

                <!-- BOTÃO MODO GUIADO (CALL TO ACTION PRINCIPAL) -->
                <a 
                    href="/receita/modo-guiado?id=<?= (int)$recipe['id'] ?>" 
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-md hover:shadow-lg transition-all"
                >
                    <span>⏱️ Modo de Preparo Guiado</span>
                </a>
            </div>
        </div>

    </div>

    <!-- FICHA TÉCNICA E PARÂMETROS CULINÁRIOS -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        
        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm text-center">
            <span class="text-2xl block mb-1">⏱️</span>
            <span class="text-[11px] font-bold uppercase tracking-wider text-stone-400 block">Tempo Total</span>
            <span class="text-sm font-extrabold text-stone-800"><?= (int)$recipe['tempo_preparo_minutos'] ?> minutos</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm text-center">
            <span class="text-2xl block mb-1">🍽️</span>
            <span class="text-[11px] font-bold uppercase tracking-wider text-stone-400 block">Rendimento</span>
            <span class="text-sm font-extrabold text-stone-800"><?= htmlspecialchars($recipe['rendimento'] ?: 'Não especificado') ?></span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm text-center">
            <span class="text-2xl block mb-1">🌡️</span>
            <span class="text-[11px] font-bold uppercase tracking-wider text-stone-400 block">Temperatura</span>
            <span class="text-sm font-extrabold text-stone-800"><?= htmlspecialchars($recipe['temperatura'] ?: 'Ambiente / N/A') ?></span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm text-center">
            <span class="text-2xl block mb-1">🔥</span>
            <span class="text-[11px] font-bold uppercase tracking-wider text-stone-400 block">Fogo / Intensidade</span>
            <span class="text-sm font-extrabold text-stone-800"><?= htmlspecialchars($recipe['intensidade_fogo'] ?: 'Não requer fogo direto') ?></span>
        </div>

    </div>

    <!-- PONTO ESPERADO E UTENSÍLIOS -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        
        <!-- PONTO ESPERADO -->
        <div class="bg-amber-50/70 p-5 rounded-2xl border border-amber-200">
            <h3 class="text-xs font-bold uppercase tracking-wider text-amber-900 mb-1 flex items-center gap-1.5">
                <span>🎯</span> Ponto Esperado do Prato
            </h3>
            <p class="text-sm text-amber-950 font-medium leading-relaxed">
                <?= htmlspecialchars($recipe['ponto_esperado'] ?: 'Consistência e cor conforme indicado nas etapas do modo de preparo.') ?>
            </p>
        </div>

        <!-- UTENSÍLIOS NECESSÁRIOS -->
        <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-sm">
            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-700 mb-2 flex items-center gap-1.5">
                <span>🥣</span> Utensílios Recomendados
            </h3>
            <?php if (!empty($recipe['utensilios'])): ?>
                <div class="flex flex-wrap gap-1.5">
                    <?php foreach ($recipe['utensilios'] as $ut): ?>
                        <span class="bg-stone-100 text-stone-700 font-semibold text-xs px-2.5 py-1 rounded-lg">
                            <?= htmlspecialchars($ut['nome']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-xs text-stone-400">Utensílios básicos de cozinha (tigela, faca e colher).</p>
            <?php endif; ?>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
        
        <!-- INGREDIENTES E SUBSTITUIÇÕES -->
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm sticky top-24">
                <h2 class="text-lg font-bold text-stone-900 mb-4 pb-3 border-b border-stone-100 flex items-center gap-2">
                    <span>🛒</span> Ingredientes
                </h2>

                <ul class="space-y-3.5 text-xs">
                    <?php foreach ($recipe['ingredientes'] as $ing): ?>
                        <li class="pb-3 border-b border-stone-100 last:border-0 last:pb-0">
                            <div class="flex items-start justify-between gap-2">
                                <span class="font-bold text-stone-900 text-sm">
                                    <?= htmlspecialchars($ing['ingrediente_nome']) ?>
                                </span>
                                <?php if ($ing['quantidade']): ?>
                                    <span class="font-extrabold text-amber-700 bg-amber-50 px-2 py-0.5 rounded text-xs whitespace-nowrap">
                                        <?= (float)$ing['quantidade'] ?> <?= htmlspecialchars($ing['unidade'] ?? '') ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($ing['observacao'])): ?>
                                <p class="text-[11px] text-stone-500 mt-0.5">
                                    <em>(<?= htmlspecialchars($ing['observacao']) ?>)</em>
                                </p>
                            <?php endif; ?>

                            <!-- OPÇÃO DE SUBSTITUIÇÃO -->
                            <?php if (!empty($ing['substituicoes'])): ?>
                                <?php foreach ($ing['substituicoes'] as $subst): ?>
                                    <div class="mt-2 p-2.5 bg-emerald-50 rounded-xl border border-emerald-200 text-[11px] text-emerald-900">
                                        <div class="font-bold flex items-center gap-1">
                                            <span>💡 Alternativa / Substituição:</span>
                                        </div>
                                        <div class="mt-0.5 font-semibold text-emerald-950">
                                            <?= htmlspecialchars($subst['texto_substituto']) ?>
                                        </div>
                                        <?php if (!empty($subst['proporcao'])): ?>
                                            <div class="text-[10px] text-emerald-800 mt-0.5">
                                                <strong>Proporção:</strong> <?= htmlspecialchars($subst['proporcao']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($subst['observacao'])): ?>
                                            <div class="text-[10px] text-emerald-700 italic mt-0.5">
                                                <?= htmlspecialchars($subst['observacao']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- MODO DE PREPARO / ETAPAS -->
        <div class="lg:col-span-2">
            <div id="modo-de-preparo" class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm">
                <div class="flex items-center justify-between pb-4 mb-6 border-b border-stone-100">
                    <div>
                        <h2 class="text-xl font-bold text-stone-900 flex items-center gap-2">
                            <span>👩‍🍳</span> Modo de Preparo
                        </h2>
                        <p class="text-xs text-stone-500 mt-0.5">Siga as etapas na ordem para garantir o ponto perfeito</p>
                    </div>

                    <span class="text-xs font-bold text-stone-400 bg-stone-100 px-3 py-1 rounded-full">
                        <?= count($recipe['etapas']) ?> passos
                    </span>
                </div>

                <div class="space-y-6">
                    <?php foreach ($recipe['etapas'] as $etapa): ?>
                        <div class="flex items-start gap-4">
                            <div class="w-9 h-9 rounded-2xl bg-amber-600 text-white flex items-center justify-center font-extrabold text-sm flex-shrink-0 shadow-md">
                                <?= (int)$etapa['ordem'] ?>
                            </div>
                            <div class="pt-1 flex-grow">
                                <p class="text-sm text-stone-700 leading-relaxed font-medium">
                                    <?= nl2br(htmlspecialchars($etapa['descricao'])) ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- CARD CALLOUT PARA O MODO GUIADO (ETAPA 4) -->
                <div class="mt-10 p-6 bg-gradient-to-r from-amber-500/15 to-orange-500/15 rounded-2xl border border-amber-300 text-center">
                    <span class="text-3xl block mb-2">📱🍳</span>
                    <h3 class="text-base font-bold text-stone-900 mb-1">Cozinhando agora? Use o Modo Guiado!</h3>
                    <p class="text-xs text-stone-600 max-w-md mx-auto mb-4">
                        Uma interface especial focada com letras grandes, botões fáceis para tocar com as mãos sujas e tecnologia Wake Lock que mantém a tela do celular sempre acesa.
                    </p>
                    <a 
                        href="/receita/modo-guiado?id=<?= (int)$recipe['id'] ?>" 
                        class="inline-block px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow transition-all"
                    >
                        Abrir Modo Guiado na Cozinha
                    </a>
                </div>

            </div>
        </div>

    </div>

    <!-- BIOGRAFIA PÚBLICA DO PRODUTOR -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm flex flex-col sm:flex-row items-center sm:items-start gap-6 mb-8">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-400 text-white flex items-center justify-center font-extrabold text-2xl shadow flex-shrink-0">
            <?= mb_strtoupper(mb_substr($recipe['autor_nome'], 0, 1)) ?>
        </div>
        <div class="text-center sm:text-left flex-grow">
            <span class="text-xs uppercase font-bold tracking-wider text-amber-600 block mb-0.5">Chef Criador da Receita</span>
            <h3 class="text-lg font-bold text-stone-900 mb-1"><?= htmlspecialchars($recipe['autor_nome']) ?></h3>
            <p class="text-xs text-stone-600 leading-relaxed mb-3">
                <?= htmlspecialchars($recipe['autor_bio'] ?: 'Produtor de receitas e entusiasta da culinária prática e inclusiva.') ?>
            </p>
            <a href="/produtor?id=<?= (int)$recipe['autor_id'] ?>" class="text-xs font-bold text-amber-600 hover:text-amber-700">
                Ver todas as receitas de <?= htmlspecialchars($recipe['autor_nome']) ?> &rarr;
            </a>
        </div>
    </div>

    <!-- SEÇÃO DE AVALIAÇÃO COM ESTRELAS -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-stone-900 flex items-center gap-2">
                    <span>⭐</span> Avalie esta Receita
                </h3>
                <p class="text-xs text-stone-500 mt-0.5">
                    Sua nota ajuda a comunidade a encontrar os melhores preparos
                </p>
            </div>

            <?php if (Session::has('user_id')): ?>
                <form action="/receita/avaliar" method="POST" class="flex items-center gap-2">
                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                    <input type="hidden" name="receita_id" value="<?= (int)$recipe['id'] ?>">

                    <div class="flex items-center gap-1 text-2xl text-amber-400">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <label class="cursor-pointer hover:scale-125 transition-transform" title="Nota <?= $i ?>">
                                <input 
                                    type="radio" 
                                    name="nota" 
                                    value="<?= $i ?>" 
                                    <?= $userRating === $i ? 'checked' : '' ?>
                                    onchange="this.form.submit()" 
                                    class="sr-only"
                                >
                                <span class="<?= $userRating !== null && $i <= $userRating ? 'text-amber-500' : 'text-stone-300 hover:text-amber-400' ?>">★</span>
                            </label>
                        <?php endfor; ?>
                    </div>

                    <?php if ($userRating !== null): ?>
                        <span class="text-xs font-bold text-stone-500 ml-2">Sua nota: <?= $userRating ?>/5</span>
                    <?php endif; ?>
                </form>
            <?php else: ?>
                <div class="text-xs text-stone-500">
                    <a href="/login" class="font-bold text-amber-600 hover:underline">Faça login</a> para registrar sua avaliação.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- SEÇÃO DE COMENTÁRIOS E FEEDBACK -->
    <div id="comentarios" class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm mb-12">
        <div class="flex items-center justify-between pb-4 mb-6 border-b border-stone-100">
            <div>
                <h3 class="text-xl font-bold text-stone-900 flex items-center gap-2">
                    <span>💬</span> Comentários da Comunidade
                </h3>
                <p class="text-xs text-stone-500 mt-0.5">Dicas, variações e opiniões de quem já preparou</p>
            </div>
            <span class="text-xs font-bold text-stone-400 bg-stone-100 px-3 py-1 rounded-full">
                <?= count($comments) ?> <?= count($comments) === 1 ? 'comentário' : 'comentários' ?>
            </span>
        </div>

        <!-- FORMULÁRIO DE NOVO COMENTÁRIO -->
        <?php if (Session::has('user_id')): ?>
            <form action="/comentario/criar" method="POST" class="mb-8">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="receita_id" value="<?= (int)$recipe['id'] ?>">

                <label for="texto-comentario" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-2">
                    Deixe sua opinião ou dica culinária
                </label>
                <textarea 
                    id="texto-comentario" 
                    name="texto" 
                    rows="3" 
                    required 
                    maxlength="1000"
                    placeholder="Conte como ficou sua receita, substituições que você testou ou tire dúvidas..."
                    class="w-full px-4 py-3 rounded-2xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm shadow-sm transition-all mb-3"
                ></textarea>

                <div class="flex justify-end">
                    <button 
                        type="submit" 
                        class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow transition-all"
                    >
                        Publicar Comentário
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 mb-8 flex items-center justify-between">
                <span>Quer participar da conversa e deixar sua opinião sobre esta receita?</span>
                <a href="/login" class="font-bold underline text-amber-800 hover:text-amber-950">Entrar ou Cadastre-se</a>
            </div>
        <?php endif; ?>

        <!-- LISTA DE COMENTÁRIOS -->
        <?php if (empty($comments)): ?>
            <div class="text-center py-8 text-xs text-stone-400">
                Seja a primeira pessoa a comentar nesta receita!
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($comments as $com): ?>
                    <?php 
                        $isOwner = (int)Session::get('user_id') === (int)$com['usuario_id'];
                        $isAdmin = Session::get('user_role') === ROLE_ADMIN;
                    ?>
                    <div id="comentario-<?= (int)$com['id'] ?>" class="p-4 sm:p-5 rounded-2xl bg-stone-50 border border-stone-200 text-xs transition-all">
                        
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold text-xs">
                                    <?= mb_strtoupper(mb_substr($com['autor_nome'], 0, 1)) ?>
                                </div>
                                <span class="font-bold text-stone-800 text-sm"><?= htmlspecialchars($com['autor_nome']) ?></span>
                                <?php if ($com['autor_perfil'] === 'produtor'): ?>
                                    <span class="bg-indigo-100 text-indigo-800 font-bold px-2 py-0.5 rounded-full text-[10px]">Chef</span>
                                <?php elseif ($com['autor_perfil'] === 'administrador'): ?>
                                    <span class="bg-rose-100 text-rose-800 font-bold px-2 py-0.5 rounded-full text-[10px]">Admin</span>
                                <?php endif; ?>
                            </div>

                            <span class="text-stone-400 text-[11px]"><?= date('d/m/Y H:i', strtotime($com['criado_em'])) ?></span>
                        </div>

                        <!-- TEXTO DO COMENTÁRIO -->
                        <div id="texto-exibicao-<?= (int)$com['id'] ?>" class="text-stone-700 leading-relaxed text-sm my-2">
                            <?= nl2br(htmlspecialchars($com['texto'])) ?>
                        </div>

                        <!-- FORMULÁRIO DE EDIÇÃO INLINE (OCULTO POR PADRÃO) -->
                        <?php if ($isOwner): ?>
                            <form id="form-edicao-<?= (int)$com['id'] ?>" action="/comentario/editar" method="POST" class="hidden my-3">
                                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                <input type="hidden" name="comentario_id" value="<?= (int)$com['id'] ?>">
                                <textarea 
                                    name="texto" 
                                    rows="3" 
                                    required 
                                    class="w-full px-3 py-2 rounded-xl border border-stone-300 text-sm mb-2"
                                ><?= htmlspecialchars($com['texto']) ?></textarea>
                                <div class="flex gap-2 justify-end">
                                    <button type="button" onclick="toggleEditComment(<?= (int)$com['id'] ?>)" class="px-3 py-1.5 rounded-lg bg-stone-200 text-stone-700 font-bold text-xs">Cancelar</button>
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-amber-600 text-white font-bold text-xs">Salvar Alterações</button>
                                </div>
                            </form>
                        <?php endif; ?>

                        <!-- AÇÕES: EDITAR / EXCLUIR / DENUNCIAR -->
                        <div class="flex items-center gap-3 pt-2 border-t border-stone-200/60 text-[11px]">
                            <?php if ($isOwner): ?>
                                <button type="button" onclick="toggleEditComment(<?= (int)$com['id'] ?>)" class="text-amber-600 hover:underline font-bold">
                                    Editar
                                </button>
                            <?php endif; ?>

                            <?php if ($isOwner || $isAdmin): ?>
                                <form action="/comentario/excluir" method="POST" class="inline" onsubmit="return confirm('Deseja realmente excluir este comentário?')">
                                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                    <input type="hidden" name="comentario_id" value="<?= (int)$com['id'] ?>">
                                    <button type="submit" class="text-rose-600 hover:underline font-bold">
                                        Excluir
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if (!$isOwner && Session::has('user_id')): ?>
                                <button 
                                    type="button" 
                                    onclick="openReportModal('comentario', <?= (int)$com['id'] ?>, 'Comentário de <?= htmlspecialchars(addslashes($com['autor_nome'])) ?>')"
                                    class="text-stone-400 hover:text-rose-600 transition-colors ml-auto flex items-center gap-1"
                                    title="Denunciar comentário abusivo"
                                >
                                    <span>🚩 Denunciar</span>
                                </button>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <!-- MODAL DE DENÚNCIA -->
    <div id="report-modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden items-center justify-center p-4">
        <div class="bg-white max-w-md w-full rounded-3xl p-6 sm:p-8 shadow-2xl">
            <div class="flex items-center gap-2 text-rose-600 mb-2">
                <span class="text-2xl">🚩</span>
                <h3 class="text-lg font-bold text-stone-900">Registrar Denúncia</h3>
            </div>
            <p id="report-target-label" class="text-xs text-stone-500 mb-4 truncate"></p>

            <form action="/denuncia/enviar" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" id="report-tipo-alvo" name="tipo_alvo" value="">
                <input type="hidden" id="report-alvo-id" name="alvo_id" value="">

                <label for="report-motivo" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                    Motivo da Denúncia *
                </label>
                <textarea 
                    id="report-motivo" 
                    name="motivo" 
                    rows="4" 
                    required 
                    placeholder="Explique por que este conteúdo viola as diretrizes (ex: informações falsas, discurso abusivo, plágio)..."
                    class="w-full px-3 py-2.5 rounded-xl border border-stone-300 text-xs mb-4 focus:ring-2 focus:ring-rose-500"
                ></textarea>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeReportModal()" class="px-4 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow transition-all">
                        Enviar Denúncia
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleEditComment(id) {
            const form = document.getElementById(`form-edicao-${id}`);
            const text = document.getElementById(`texto-exibicao-${id}`);
            if (form && text) {
                form.classList.toggle('hidden');
                text.classList.toggle('hidden');
            }
        }

        function openReportModal(tipo, id, label) {
            document.getElementById('report-tipo-alvo').value = tipo;
            document.getElementById('report-alvo-id').value = id;
            document.getElementById('report-target-label').textContent = `Alvo: ${label}`;
            const modal = document.getElementById('report-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeReportModal() {
            const modal = document.getElementById('report-modal');
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }
    </script>

</div>

<?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
