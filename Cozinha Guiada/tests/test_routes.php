<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: database/test_routes.php
// DESCRIÇÃO: Simula requisições HTTP para validar o Roteador e as Views
// ==============================================================================

require_once dirname(__DIR__) . '/config/constants.php';
require_once CONFIG_PATH . '/database.php';

function testRoute(string $uri, string $expectedString): bool {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = $uri;
    $_SERVER['SCRIPT_NAME'] = '/index.php';

    // Popula $_GET para simular o ambiente web real
    $queryString = parse_url($uri, PHP_URL_QUERY);
    $_GET = [];
    if ($queryString) {
        parse_str($queryString, $_GET);
    }

    ob_start();
    require PUBLIC_PATH . '/index.php';
    $output = ob_get_clean();

    $found = str_contains($output, $expectedString);
    echo "Rota [{$uri}] -> " . ($found ? "OK (encontrou '{$expectedString}')" : "FALHA") . "\n";
    return $found;
}

echo "--- Testando Renderização das Rotas Web ---\n";
testRoute('/', 'Cozinha Guiada');
testRoute('/login', 'Bem-vindo(a) de volta!');
testRoute('/cadastro', 'Crie sua Conta Gratuita');
testRoute('/receitas', 'Catálogo de Receitas');
testRoute('/receita?id=1', 'Bolo de Cenoura');
testRoute('/receita/modo-guiado?id=1', 'Modo de Preparo Guiado');
testRoute('/produtor?id=2', 'Chef André Luiz');
testRoute('/receita?id=99999', 'Receita não encontrada');
testRoute('/rota-inexistente-404', 'Página não encontrada');

// Simula sessão de usuário autenticado para rotas protegidas
Session::set('user_id', 3);
Session::set('user_name', 'Lucas Iniciante');
Session::set('user_role', ROLE_CADASTRADO);

testRoute('/perfil', 'Meu Perfil');
testRoute('/favoritos', 'Receitas Favoritas');
testRoute('/historico', 'Histórico de Visualizações');

// Simula sessão de usuário produtor para rotas da área do produtor
Session::set('user_id', 2);
Session::set('user_name', 'Chef André Luiz');
Session::set('user_role', ROLE_PRODUTOR);

testRoute('/produtor/painel', 'Painel do Produtor');
testRoute('/produtor/receita/nova', 'Criar Nova Receita');
testRoute('/produtor/receita/editar?id=1', 'Editar Receita');

// Simula sessão de usuário administrador para rotas da área administrativa
Session::set('user_id', 1);
Session::set('user_name', 'Administrador do Sistema');
Session::set('user_role', ROLE_ADMIN);

testRoute('/admin', 'Painel Administrativo');
testRoute('/admin/usuarios', 'Gestão de Usuários');
testRoute('/admin/denuncias', 'Fila de Moderação de Denúncias');
testRoute('/admin/categorias', 'Categorias de Receitas');

Session::destroy();
