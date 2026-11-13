<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: views/admin/dashboard.php
// DESCRIÇÃO: Painel de Controle Geral do Administrador (RF48)
// ==============================================================================

$pageTitle = 'Painel Administrativo — Cozinha Guiada';
require VIEWS_PATH . '/layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- CABEÇALHO -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-2xl">🛡️</span>
                <h1 class="text-3xl font-extrabold text-stone-900 tracking-tight">Painel Administrativo</h1>
            </div>
            <p class="text-sm text-stone-500">Supervisão de métricas operacionais, usuários, categorias e moderação</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="/admin/denuncias" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-1.5">
                <span>🚩 Fila de Denúncias</span>
                <?php if ($stats['denuncias_pendentes'] > 0): ?>
                    <span class="bg-white text-rose-700 px-1.5 py-0.2 rounded-full text-[10px] font-extrabold"><?= $stats['denuncias_pendentes'] ?></span>
                <?php endif; ?>
            </a>
            <a href="/admin/usuarios" class="px-4 py-2 bg-stone-800 hover:bg-stone-900 text-white font-bold text-xs rounded-xl shadow transition-all">
                👥 Gestão de Usuários
            </a>
            <a href="/admin/categorias" class="px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold text-xs rounded-xl transition-all">
                📂 Categorias
            </a>
        </div>
    </div>

    <!-- CARDS DE ESTATÍSTICAS GERAIS -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-10">
        
        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-stone-400 block mb-1">Usuários</span>
            <span class="text-2xl font-extrabold text-stone-900"><?= $stats['total_usuarios'] ?></span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-indigo-500 block mb-1">Produtores</span>
            <span class="text-2xl font-extrabold text-indigo-900"><?= $stats['total_produtores'] ?></span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-500 block mb-1">Receitas</span>
            <span class="text-2xl font-extrabold text-stone-900"><?= $stats['total_receitas'] ?></span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-stone-400 block mb-1">Privadas</span>
            <span class="text-2xl font-extrabold text-stone-600"><?= $stats['receitas_privadas'] ?></span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-rose-500 block mb-1">Denúncias</span>
            <span class="text-2xl font-extrabold text-rose-700"><?= $stats['denuncias_pendentes'] ?></span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-500 block mb-1">Comentários</span>
            <span class="text-2xl font-extrabold text-stone-900"><?= $stats['total_comentarios'] ?></span>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- DENÚNCIAS PENDENTES RECENTES -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-stone-100">
                <h2 class="text-lg font-bold text-stone-900 flex items-center gap-2">
                    <span>🚩</span> Denúncias que Exigem Análise
                </h2>
                <a href="/admin/denuncias" class="text-xs font-bold text-rose-600 hover:underline">Ver todas &rarr;</a>
            </div>

            <?php if (empty($ultimasDenuncias)): ?>
                <div class="text-center py-10 text-xs text-stone-400">
                    Nenhuma denúncia pendente no momento! A comunidade está em harmonia. ✨
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($ultimasDenuncias as $den): ?>
                        <div class="p-3.5 rounded-2xl bg-rose-50/50 border border-rose-200 text-xs flex items-start justify-between gap-3">
                            <div>
                                <span class="bg-rose-100 text-rose-800 font-bold px-2 py-0.5 rounded text-[10px] uppercase">
                                    <?= htmlspecialchars($den['tipo_alvo']) ?>
                                </span>
                                <p class="text-stone-800 font-medium mt-1 leading-snug">
                                    <?= htmlspecialchars(mb_strimwidth($den['motivo'], 0, 100, '...')) ?>
                                </p>
                                <span class="text-[10px] text-stone-400 block mt-1">Por <?= htmlspecialchars($den['denunciante_nome']) ?></span>
                            </div>
                            <a href="/admin/denuncias" class="text-rose-600 font-bold hover:underline whitespace-nowrap self-center text-xs">
                                Analisar &rarr;
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- USUÁRIOS RECENTES -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-stone-100">
                <h2 class="text-lg font-bold text-stone-900 flex items-center gap-2">
                    <span>👥</span> Membros Recentes
                </h2>
                <a href="/admin/usuarios" class="text-xs font-bold text-amber-600 hover:underline">Gerenciar &rarr;</a>
            </div>

            <div class="divide-y divide-stone-100">
                <?php foreach ($ultimosUsuarios as $u): ?>
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-bold text-stone-900"><?= htmlspecialchars($u['nome']) ?></span>
                            <span class="text-stone-400 text-[11px] block"><?= htmlspecialchars($u['email']) ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $u['perfil'] === ROLE_ADMIN ? 'bg-rose-100 text-rose-800' : ($u['perfil'] === ROLE_PRODUTOR ? 'bg-indigo-100 text-indigo-800' : 'bg-stone-100 text-stone-700') ?>">
                                <?= htmlspecialchars($u['perfil']) ?>
                            </span>
                            <span class="w-2 h-2 rounded-full <?= (int)$u['ativo'] === 1 ? 'bg-emerald-500' : 'bg-rose-500' ?>"></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

</div>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
