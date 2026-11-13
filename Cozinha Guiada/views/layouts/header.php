<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: views/layouts/header.php
// DESCRIÇÃO: Cabeçalho unificado e responsivo com navegação e alertas flash
// ==============================================================================

require_once SRC_PATH . '/Utils/Session.php';
$isLoggedIn = Session::has('user_id');
$userName   = Session::get('user_name', '');
$userRole   = Session::get('user_role', 'visitante');
$flashes    = Session::getFlashes();
$pageTitle  = $pageTitle ?? 'Cozinha Guiada - Receitas Práticas e Inclusivas';
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-stone-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                            800: '#92400e',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    <!-- Estilos Customizados e Acessibilidade -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="min-h-full flex flex-col text-stone-800 antialiased selection:bg-amber-200">

    <!-- BARRA DE NAVEGAÇÃO -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-stone-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- LOGO E MARCA -->
                <a href="/" class="flex items-center gap-2.5 group">
                    <span class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-xl shadow-md group-hover:scale-105 transition-transform">
                        🍳
                    </span>
                    <div>
                        <span class="text-xl font-extrabold text-stone-900 tracking-tight group-hover:text-amber-600 transition-colors">
                            Cozinha<span class="text-amber-600">Guiada</span>
                        </span>
                        <span class="block text-[10px] uppercase font-bold tracking-wider text-stone-600 -mt-1">
                            Receitas Inclusivas
                        </span>
                    </div>
                </a>

                <!-- LINKS CENTRAIS -->
                <nav class="hidden md:flex items-center gap-6 text-sm font-semibold text-stone-600">
                    <a href="/" class="hover:text-amber-600 transition-colors">Início</a>
                    <a href="/receitas" class="hover:text-amber-600 transition-colors">Catálogo de Receitas</a>
                    <a href="/#categorias" class="hover:text-amber-600 transition-colors">Categorias</a>
                </nav>

                <!-- ÁREA DO USUÁRIO -->
                <div class="flex items-center gap-3">
                    <?php if ($isLoggedIn): ?>
                        
                        <!-- BADGE DE PERFIL -->
                        <div class="hidden sm:flex items-center gap-2 pl-3 pr-2 py-1 bg-stone-100 rounded-full border border-stone-200 text-xs">
                            <span class="font-medium text-stone-700">Olá, <strong><?= htmlspecialchars(explode(' ', $userName)[0]) ?></strong></span>
                            <?php if ($userRole === 'administrador'): ?>
                                <span class="bg-rose-100 text-rose-800 font-bold px-2 py-0.5 rounded-full text-[10px] uppercase">Admin</span>
                            <?php elseif ($userRole === 'produtor'): ?>
                                <span class="bg-indigo-100 text-indigo-800 font-bold px-2 py-0.5 rounded-full text-[10px] uppercase">Chef / Produtor</span>
                            <?php else: ?>
                                <span class="bg-amber-100 text-amber-800 font-bold px-2 py-0.5 rounded-full text-[10px] uppercase">Membro</span>
                            <?php endif; ?>
                        </div>

                        <!-- MENU RÁPIDO DO USUÁRIO -->
                        <?php if ($userRole === ROLE_ADMIN): ?>
                            <a href="/admin" class="inline-flex items-center gap-1 px-3 py-2 text-sm font-bold rounded-xl text-rose-800 bg-rose-100 hover:bg-rose-200 transition-colors" title="Painel de Administração">
                                <span>🛡️ Painel Admin</span>
                            </a>
                        <?php endif; ?>

                        <?php if ($userRole === ROLE_PRODUTOR || $userRole === ROLE_ADMIN): ?>
                            <a href="/produtor/painel" class="inline-flex items-center gap-1 px-3 py-2 text-sm font-bold rounded-xl text-amber-800 bg-amber-100 hover:bg-amber-200 transition-colors" title="Painel de Criação do Produtor">
                                <span>👩‍🍳 Painel Chef</span>
                            </a>
                        <?php endif; ?>

                        <a href="/favoritos" class="hidden md:inline-flex items-center gap-1 px-3 py-2 text-sm font-medium rounded-xl text-stone-700 hover:text-amber-600 hover:bg-stone-100 transition-colors" title="Receitas Favoritas">
                            <span>⭐ Favoritos</span>
                        </a>

                        <a href="/historico" class="hidden md:inline-flex items-center gap-1 px-3 py-2 text-sm font-medium rounded-xl text-stone-700 hover:text-amber-600 hover:bg-stone-100 transition-colors" title="Histórico de Visualizações">
                            <span>🕒 Histórico</span>
                        </a>

                        <a href="/perfil" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium rounded-xl text-stone-700 bg-stone-100 hover:bg-stone-200 transition-colors">
                            <span>👤 Meu Perfil</span>
                        </a>

                        <a href="/logout" class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium rounded-xl text-rose-600 hover:bg-rose-50 transition-colors" title="Encerrar Sessão">
                            <span>Sair</span>
                        </a>

                    <?php else: ?>

                        <!-- VISITANTE: ENTRAR E REGISTRAR -->
                        <a href="/login" class="text-sm font-semibold text-stone-700 hover:text-amber-600 px-3 py-2 transition-colors">
                            Entrar
                        </a>
                        <a href="/cadastro" class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-sm hover:shadow transition-all">
                            Cadastre-se
                        </a>

                    <?php endif; ?>

                    <!-- BOTÃO MENU MOBILE (HAMBURGER) -->
                    <button id="mobile-menu-btn" type="button" class="md:hidden inline-flex items-center justify-center p-2 rounded-xl text-stone-600 hover:text-stone-900 hover:bg-stone-100 focus:outline-none focus:ring-2 focus:ring-amber-500" aria-label="Abrir menu principal">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>

            </div>
        </div>

        <!-- MENU MOBILE EXPANSÍVEL -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-stone-200 bg-white px-4 pt-3 pb-4 space-y-2 shadow-lg">
            <a href="/" class="block px-3 py-2 rounded-lg text-base font-semibold text-stone-700 hover:text-amber-600 hover:bg-amber-50">Início</a>
            <a href="/receitas" class="block px-3 py-2 rounded-lg text-base font-semibold text-stone-700 hover:text-amber-600 hover:bg-amber-50">Catálogo de Receitas</a>
            <a href="/#categorias" class="block px-3 py-2 rounded-lg text-base font-semibold text-stone-700 hover:text-amber-600 hover:bg-amber-50">Categorias</a>
            <?php if ($isLoggedIn): ?>
                <div class="pt-2 border-t border-stone-100 space-y-1">
                    <a href="/favoritos" class="block px-3 py-2 rounded-lg text-sm font-medium text-stone-700 hover:text-amber-600 hover:bg-stone-50">⭐ Minhas Favoritas</a>
                    <a href="/historico" class="block px-3 py-2 rounded-lg text-sm font-medium text-stone-700 hover:text-amber-600 hover:bg-stone-50">🕒 Histórico Recente</a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var btn = document.getElementById('mobile-menu-btn');
            var menu = document.getElementById('mobile-menu');
            if (btn && menu) {
                btn.addEventListener('click', function() {
                    menu.classList.toggle('hidden');
                });
            }
        });
    </script>

    <!-- CONTAINER DE MENSAGENS FLASH (NOTIFICAÇÕES DO SISTEMA) -->
    <?php if (!empty($flashes)): ?>
        <div class="max-w-4xl mx-auto px-4 mt-4 w-full space-y-2">
            <?php foreach ($flashes as $type => $messages): ?>
                <?php foreach ($messages as $msg): ?>
                    <?php if ($type === 'success'): ?>
                        <div class="flex items-center gap-3 p-4 text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm text-sm font-medium">
                            <span class="text-xl">✅</span>
                            <span><?= htmlspecialchars($msg) ?></span>
                        </div>
                    <?php elseif ($type === 'error'): ?>
                        <div class="flex items-center gap-3 p-4 text-rose-800 bg-rose-50 border border-rose-200 rounded-xl shadow-sm text-sm font-medium">
                            <span class="text-xl">⚠️</span>
                            <span><?= htmlspecialchars($msg) ?></span>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-3 p-4 text-amber-800 bg-amber-50 border border-amber-200 rounded-xl shadow-sm text-sm font-medium">
                            <span class="text-xl">ℹ️</span>
                            <span><?= htmlspecialchars($msg) ?></span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="flex-grow">
