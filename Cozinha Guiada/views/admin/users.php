<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: views/admin/users.php
// DESCRIÇÃO: Gestão de contas de usuários, bloqueio e papéis RBAC (RF46, RF47)
// ==============================================================================

$pageTitle = 'Gestão de Usuários — Cozinha Guiada';
require VIEWS_PATH . '/layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- CABEÇALHO -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="/admin" class="text-xs font-bold text-stone-500 hover:text-amber-600 block">&larr; Voltar ao Dashboard</a>
            </div>
            <h1 class="text-3xl font-extrabold text-stone-900 tracking-tight">Gestão de Usuários</h1>
            <p class="text-sm text-stone-500">Controle de acessos, papéis RBAC e bloqueio de contas irregulares</p>
        </div>

        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-stone-100 text-stone-700 text-xs font-bold">
            👥 <?= count($users) ?> usuários listados
        </span>
    </div>

    <!-- FILTROS DE BUSCA -->
    <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-sm mb-6">
        <form action="/admin/usuarios" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-grow">
                <input 
                    type="text" 
                    name="q" 
                    value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                    placeholder="Buscar usuário por nome ou e-mail..."
                    class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-xs shadow-sm"
                >
            </div>

            <select name="perfil" class="px-3 py-2.5 rounded-xl border border-stone-300 text-xs bg-white">
                <option value="">Todos os Perfis</option>
                <option value="cadastrado" <?= ($_GET['perfil'] ?? '') === 'cadastrado' ? 'selected' : '' ?>>Cadastrado (Membro)</option>
                <option value="produtor" <?= ($_GET['perfil'] ?? '') === 'produtor' ? 'selected' : '' ?>>Produtor de Conteúdo</option>
                <option value="administrador" <?= ($_GET['perfil'] ?? '') === 'administrador' ? 'selected' : '' ?>>Administrador</option>
            </select>

            <button type="submit" class="px-6 py-2.5 bg-stone-800 hover:bg-stone-900 text-white font-bold text-xs rounded-xl shadow transition-all">
                Filtrar
            </button>

            <?php if (!empty($_GET['q']) || !empty($_GET['perfil'])): ?>
                <a href="/admin/usuarios" class="px-4 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-xl transition-all text-center">
                    Limpar
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- TABELA DE USUÁRIOS -->
    <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-stone-600">
                <thead class="bg-stone-50 border-b border-stone-200 text-[11px] font-bold uppercase tracking-wider text-stone-700">
                    <tr>
                        <th class="p-4 sm:p-5">Usuário</th>
                        <th class="p-4 sm:p-5">Papel / Perfil</th>
                        <th class="p-4 sm:p-5">Status</th>
                        <th class="p-4 sm:p-5">Cadastro</th>
                        <th class="p-4 sm:p-5 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-stone-50/70 transition-colors">
                            
                            <!-- USUÁRIO -->
                            <td class="p-4 sm:p-5">
                                <div class="font-bold text-stone-900 text-sm"><?= htmlspecialchars($u['nome']) ?></div>
                                <div class="text-stone-400 text-[11px]"><?= htmlspecialchars($u['email']) ?></div>
                            </td>

                            <!-- PERFIL RBAC -->
                            <td class="p-4 sm:p-5 whitespace-nowrap">
                                <form action="/admin/usuarios/perfil" method="POST" class="inline">
                                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                    <select 
                                        name="perfil" 
                                        onchange="if(confirm('Deseja realmente alterar o perfil deste usuário?')) this.form.submit(); else this.value='<?= $u['perfil'] ?>';"
                                        class="px-2.5 py-1 rounded-lg border border-stone-300 text-xs font-bold <?= $u['perfil'] === ROLE_ADMIN ? 'bg-rose-50 text-rose-800' : ($u['perfil'] === ROLE_PRODUTOR ? 'bg-indigo-50 text-indigo-800' : 'bg-stone-50 text-stone-700') ?>"
                                    >
                                        <option value="cadastrado" <?= $u['perfil'] === ROLE_CADASTRADO ? 'selected' : '' ?>>Cadastrado</option>
                                        <option value="produtor" <?= $u['perfil'] === ROLE_PRODUTOR ? 'selected' : '' ?>>Produtor</option>
                                        <option value="administrador" <?= $u['perfil'] === ROLE_ADMIN ? 'selected' : '' ?>>Administrador</option>
                                    </select>
                                </form>
                            </td>

                            <!-- STATUS -->
                            <td class="p-4 sm:p-5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase <?= (int)$u['ativo'] === 1 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?= (int)$u['ativo'] === 1 ? 'bg-emerald-600' : 'bg-rose-600' ?>"></span>
                                    <span><?= (int)$u['ativo'] === 1 ? 'Ativo' : 'Bloqueado' ?></span>
                                </span>
                            </td>

                            <!-- DATA -->
                            <td class="p-4 sm:p-5 whitespace-nowrap text-stone-400">
                                <?= date('d/m/Y', strtotime($u['criado_em'])) ?>
                            </td>

                            <!-- AÇÕES: BLOQUEAR / ATIVAR -->
                            <td class="p-4 sm:p-5 text-right whitespace-nowrap">
                                <form action="/admin/usuarios/status" method="POST" class="inline" onsubmit="return confirm('Deseja realmente <?= (int)$u['ativo'] === 1 ? 'bloquear' : 'ativar' ?> este usuário?')">
                                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                    <button 
                                        type="submit" 
                                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all <?= (int)$u['ativo'] === 1 ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' ?>"
                                    >
                                        <?= (int)$u['ativo'] === 1 ? 'Bloquear Acesso' : 'Ativar Acesso' ?>
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

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
