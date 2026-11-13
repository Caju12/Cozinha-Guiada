<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: views/layouts/footer.php
// DESCRIÇÃO: Rodapé padronizado
// ==============================================================================
?>
    </main>

    <!-- RODAPÉ -->
    <footer class="bg-stone-900 text-stone-300 mt-20 border-t border-stone-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- IDENTIDADE -->
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-2xl">🍳</span>
                        <span class="text-xl font-bold text-white">Cozinha<span class="text-amber-500">Guiada</span></span>
                    </div>
                    <p class="text-sm text-stone-400 leading-relaxed">
                        Plataforma culinária acessível, pensada para iniciantes e pessoas com restrições alimentares. Aprenda a cozinhar com modo passo a passo guiado e substituições inteligentes.
                    </p>
                </div>

                <!-- COMPATIBILIDADES OBRIGATÓRIAS -->
                <div>
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Culinária Inclusiva</h3>
                    <ul class="space-y-2 text-sm text-stone-400">
                        <li>🌱 Receitas Vegetarianas e Veganas</li>
                        <li>🥛 Opções Sem Lactose</li>
                        <li>🌾 Opções Sem Glúten</li>
                        <li>⏱️ Modo Guiado com Tela Sempre Ativa</li>
                    </ul>
                </div>

                <!-- INFORMAÇÕES DE DESENVOLVIMENTO -->
                <div>
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Projeto</h3>
                    <p class="text-xs text-stone-400 mb-2">
                        Desenvolvimento de Sistemas &copy; <?= date('Y') ?>
                    </p>
                    <p class="text-xs text-stone-500">
                        Construído com PHP 8, MySQL/MariaDB, Arquitetura MVC e foco em segurança e acessibilidade.
                    </p>
                </div>

            </div>

            <div class="pt-8 mt-8 border-t border-stone-800 text-center text-xs text-stone-500">
                Cozinha Guiada — Praticidade, segurança e inclusão em cada etapa da receita.
            </div>
        </div>
    </footer>

</body>
</html>
