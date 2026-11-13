<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: views/admin/reports.php
// DESCRIÇÃO: Fila de Moderação e Tratamento de Denúncias (RF49, RF50, RF52)
// ==============================================================================

$pageTitle = 'Moderação de Denúncias — Cozinha Guiada';
require VIEWS_PATH . '/layouts/header.php';
$currentStatus = $_GET['status'] ?? 'pendente';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- CABEÇALHO -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="/admin" class="text-xs font-bold text-stone-500 hover:text-amber-600 block">&larr; Voltar ao Dashboard</a>
            </div>
            <h1 class="text-3xl font-extrabold text-stone-900 tracking-tight">Fila de Moderação de Denúncias</h1>
            <p class="text-sm text-stone-500">Analise relatos da comunidade e modere receitas ou comentários inadequados</p>
        </div>

        <!-- ABAS DE STATUS -->
        <div class="flex items-center gap-1.5 p-1 bg-stone-100 rounded-2xl text-xs font-bold">
            <a href="/admin/denuncias?status=pendente" class="px-3 py-1.5 rounded-xl transition-all <?= $currentStatus === 'pendente' ? 'bg-white text-rose-700 shadow-sm' : 'text-stone-600 hover:text-stone-900' ?>">
                Pendentes
            </a>
            <a href="/admin/denuncias?status=conteudo_removido" class="px-3 py-1.5 rounded-xl transition-all <?= $currentStatus === 'conteudo_removido' ? 'bg-white text-emerald-700 shadow-sm' : 'text-stone-600 hover:text-stone-900' ?>">
                Removidos
            </a>
            <a href="/admin/denuncias?status=descartada" class="px-3 py-1.5 rounded-xl transition-all <?= $currentStatus === 'descartada' ? 'bg-white text-stone-700 shadow-sm' : 'text-stone-600 hover:text-stone-900' ?>">
                Descartadas
            </a>
        </div>
    </div>

    <?php if (empty($reports)): ?>
        
        <!-- ESTADO VAZIO -->
        <div class="bg-white p-12 rounded-3xl border border-stone-200 text-center shadow-sm max-w-xl mx-auto my-12">
            <span class="text-5xl block mb-3">🛡️✨</span>
            <h2 class="text-lg font-bold text-stone-800 mb-1">Nenhuma denúncia nesta categoria</h2>
            <p class="text-xs text-stone-500">Não há registros correspondentes ao filtro selecionado no momento.</p>
        </div>

    <?php else: ?>

        <!-- LISTA DE DENÚNCIAS -->
        <div class="space-y-4">
            <?php foreach ($reports as $rep): ?>
                <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-stone-100 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full font-bold uppercase text-[10px] <?= $rep['tipo_alvo'] === 'receita' ? 'bg-amber-100 text-amber-900' : 'bg-indigo-100 text-indigo-900' ?>">
                                Alvo: <?= htmlspecialchars($rep['tipo_alvo']) ?>
                            </span>

                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $rep['status'] === 'pendente' ? 'bg-rose-100 text-rose-800' : ($rep['status'] === 'conteudo_removido' ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-700') ?>">
                                Status: <?= str_replace('_', ' ', htmlspecialchars($rep['status'])) ?>
                            </span>
                        </div>

                        <span class="text-stone-400">
                            Registrada em: <?= date('d/m/Y H:i', strtotime($rep['criado_em'])) ?>
                        </span>
                    </div>

                    <!-- MOTIVO E DENUNCIANTE -->
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 block mb-1">Motivo Relatado</span>
                        <div class="p-3 rounded-2xl bg-rose-50/70 border border-rose-200 text-rose-950 font-medium text-xs leading-relaxed">
                            "<?= htmlspecialchars($rep['motivo']) ?>"
                        </div>
                        <div class="text-[11px] text-stone-500 mt-1">
                            Denunciante: <strong><?= htmlspecialchars($rep['autor_denuncia_nome']) ?></strong> (<?= htmlspecialchars($rep['autor_denuncia_email']) ?>)
                        </div>
                    </div>

                    <!-- CONTEÚDO ALVO DENUNCIADO -->
                    <div class="p-3.5 rounded-2xl bg-stone-50 border border-stone-200 text-xs">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 block mb-1">
                            Conteúdo em Análise (ID #<?= (int)$rep['alvo_id'] ?>)
                        </span>

                        <?php if ($rep['tipo_alvo'] === 'receita'): ?>
                            <?php if (!empty($rep['alvo_detalhes'])): ?>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-bold text-stone-900 text-sm"><?= htmlspecialchars($rep['alvo_detalhes']['titulo']) ?></span>
                                        <span class="block text-stone-400 text-[11px]">Status atual: <?= htmlspecialchars($rep['alvo_detalhes']['status_moderacao']) ?></span>
                                    </div>
                                    <a href="/receita?id=<?= (int)$rep['alvo_detalhes']['id'] ?>" target="_blank" class="text-amber-600 font-bold hover:underline">
                                        Abrir Receita &rarr;
                                    </a>
                                </div>
                            <?php else: ?>
                                <span class="text-stone-400 italic">Receita já excluída ou inexistente.</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if (!empty($rep['alvo_detalhes'])): ?>
                                <div>
                                    <p class="text-stone-800 italic font-medium">"<?= htmlspecialchars($rep['alvo_detalhes']['texto']) ?>"</p>
                                    <span class="block text-stone-400 text-[11px] mt-1">Status atual: <?= htmlspecialchars($rep['alvo_detalhes']['status']) ?></span>
                                </div>
                            <?php else: ?>
                                <span class="text-stone-400 italic">Comentário já excluído ou inexistente.</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <!-- AÇÕES DE MODERAÇÃO -->
                    <?php if ($rep['status'] === 'pendente'): ?>
                        <div class="flex flex-wrap items-center justify-end gap-3 pt-3 border-t border-stone-100">
                            <!-- DESCARTAR -->
                            <form action="/admin/denuncias/resolver" method="POST" onsubmit="return confirm('Deseja descartar esta denúncia como improcedente?')">
                                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                <input type="hidden" name="denuncia_id" value="<?= (int)$rep['id'] ?>">
                                <input type="hidden" name="acao" value="descartar">
                                <button type="submit" class="px-4 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs transition-colors">
                                    Descartar Denúncia
                                </button>
                            </form>

                            <!-- REMOVER CONTEÚDO -->
                            <form action="/admin/denuncias/resolver" method="POST" onsubmit="return confirm('Tem certeza de que deseja aplicar a sanção e REMOVER o conteúdo denunciado da plataforma?')">
                                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                <input type="hidden" name="denuncia_id" value="<?= (int)$rep['id'] ?>">
                                <input type="hidden" name="acao" value="remover_conteudo">
                                <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow transition-all">
                                    Remover Conteúdo Infrator
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="pt-2 text-[11px] text-stone-400 text-right">
                            Resolvido por <strong><?= htmlspecialchars($rep['admin_resolvido_nome'] ?? 'Administrador') ?></strong> em <?= date('d/m/Y H:i', strtotime($rep['resolvido_em'])) ?>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
