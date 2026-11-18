<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: public/index.php
// DESCRIÇÃO: Front Controller e Roteador da aplicação
// ==============================================================================

require_once dirname(__DIR__) . '/config/constants.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/Utils/Session.php';

// Inicia a sessão segura global
Session::start();

// Carrega os controladores necessários
require_once SRC_PATH . '/Controllers/HomeController.php';
require_once SRC_PATH . '/Controllers/AuthController.php';
require_once SRC_PATH . '/Controllers/ProfileController.php';
require_once SRC_PATH . '/Controllers/RecipeController.php';
require_once SRC_PATH . '/Controllers/InteractionController.php';
require_once SRC_PATH . '/Controllers/CommentController.php';
require_once SRC_PATH . '/Controllers/ProducerController.php';
require_once SRC_PATH . '/Controllers/AdminController.php';
require_once SRC_PATH . '/Controllers/AdminModerationController.php';
require_once SRC_PATH . '/Controllers/AdminCategoryController.php';

// Resolve a rota atual (compatível com servidor embutido e XAMPP/Apache com subpastas)
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptDir  = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));

if ($scriptDir !== '/' && str_starts_with($requestUri, $scriptDir)) {
    $uriPath = substr($requestUri, strlen($scriptDir));
} else {
    $uriPath = $requestUri;
}

$route = '/' . trim($uriPath, '/');
if ($route === '/index.php' || empty($route)) {
    $route = '/';
}

// Fallback por parâmetro de query (?route=...) se mod_rewrite estiver desativado no Apache
if (isset($_GET['route'])) {
    $route = '/' . trim($_GET['route'], '/');
}

$method = $_SERVER['REQUEST_METHOD'];

// Roteamento
try {
    switch ($route) {
        case '/':
            (new HomeController())->index();
            break;

        case '/login':
            $controller = new AuthController();
            if ($method === 'POST') {
                $controller->login();
            } else {
                $controller->showLogin();
            }
            break;

        case '/cadastro':
            $controller = new AuthController();
            if ($method === 'POST') {
                $controller->register();
            } else {
                $controller->showRegister();
            }
            break;

        case '/logout':
            (new AuthController())->logout();
            break;

        case '/perfil':
            $controller = new ProfileController();
            if ($method === 'POST') {
                $controller->updateProfile();
            } else {
                $controller->showProfile();
            }
            break;

        case '/perfil/tornar-produtor':
            if ($method === 'POST') {
                (new ProfileController())->becomeProducer();
            } else {
                header('Location: /perfil');
                exit;
            }
            break;

        case '/receitas':
            (new RecipeController())->index();
            break;

        case '/receita':
            (new RecipeController())->show();
            break;

        case '/receita/modo-guiado':
            (new RecipeController())->guidedMode();
            break;

        case '/produtor':
            (new RecipeController())->producerProfile();
            break;

        case '/favoritos':
            (new InteractionController())->listFavorites();
            break;

        case '/favoritos/toggle':
            if ($method === 'POST') {
                (new InteractionController())->toggleFavorite();
            } else {
                header('Location: /favoritos');
                exit;
            }
            break;

        case '/historico':
            (new InteractionController())->listHistory();
            break;

        case '/historico/limpar':
            if ($method === 'POST') {
                (new InteractionController())->clearHistory();
            } else {
                header('Location: /historico');
                exit;
            }
            break;

        case '/receita/avaliar':
            if ($method === 'POST') {
                (new InteractionController())->rateRecipe();
            } else {
                header('Location: /receitas');
                exit;
            }
            break;

        case '/denuncia/enviar':
            if ($method === 'POST') {
                (new InteractionController())->submitReport();
            } else {
                header('Location: /receitas');
                exit;
            }
            break;

        case '/comentario/criar':
            if ($method === 'POST') {
                (new CommentController())->create();
            } else {
                header('Location: /receitas');
                exit;
            }
            break;

        case '/comentario/editar':
            if ($method === 'POST') {
                (new CommentController())->update();
            } else {
                header('Location: /receitas');
                exit;
            }
            break;

        case '/comentario/excluir':
            if ($method === 'POST') {
                (new CommentController())->delete();
            } else {
                header('Location: /receitas');
                exit;
            }
            break;

        case '/produtor/painel':
            (new ProducerController())->dashboard();
            break;

        case '/produtor/receita/nova':
            $controller = new ProducerController();
            if ($method === 'POST') {
                $controller->store();
            } else {
                $controller->createForm();
            }
            break;

        case '/produtor/receita/editar':
            $controller = new ProducerController();
            if ($method === 'POST') {
                $controller->update();
            } else {
                $controller->editForm();
            }
            break;

        case '/produtor/receita/visibilidade':
            if ($method === 'POST') {
                (new ProducerController())->toggleVisibility();
            } else {
                header('Location: /produtor/painel');
                exit;
            }
            break;

        case '/produtor/receita/excluir':
            if ($method === 'POST') {
                (new ProducerController())->delete();
            } else {
                header('Location: /produtor/painel');
                exit;
            }
            break;

        case '/admin':
            (new AdminController())->dashboard();
            break;

        case '/admin/usuarios':
            (new AdminController())->usersList();
            break;

        case '/admin/usuarios/status':
            if ($method === 'POST') {
                (new AdminController())->toggleUserStatus();
            } else {
                header('Location: /admin/usuarios');
                exit;
            }
            break;

        case '/admin/usuarios/perfil':
            if ($method === 'POST') {
                (new AdminController())->changeUserRole();
            } else {
                header('Location: /admin/usuarios');
                exit;
            }
            break;

        case '/admin/denuncias':
            (new AdminModerationController())->reportsList();
            break;

        case '/admin/denuncias/resolver':
            if ($method === 'POST') {
                (new AdminModerationController())->resolveReport();
            } else {
                header('Location: /admin/denuncias');
                exit;
            }
            break;

        case '/admin/categorias':
            (new AdminCategoryController())->index();
            break;

        case '/admin/categorias/criar':
            if ($method === 'POST') {
                (new AdminCategoryController())->store();
            } else {
                header('Location: /admin/categorias');
                exit;
            }
            break;

        case '/admin/categorias/editar':
            if ($method === 'POST') {
                (new AdminCategoryController())->update();
            } else {
                header('Location: /admin/categorias');
                exit;
            }
            break;

        case '/admin/categorias/excluir':
            if ($method === 'POST') {
                (new AdminCategoryController())->delete();
            } else {
                header('Location: /admin/categorias');
                exit;
            }
            break;

        case '/health':
        case '/api/health':
            header('Content-Type: application/json; charset=utf-8');
            $health = [
                'app' => 'Cozinha Guiada',
                'status' => 'ok',
                'timestamp' => date('c'),
                'php_version' => PHP_VERSION,
                'environment' => env('APP_ENV', 'production'),
                'database' => [
                    'connected' => false,
                    'driver' => null,
                    'recipes_count' => null,
                    'error' => null
                ],
                'env_detected' => [
                    'has_database_url' => !empty(env('DATABASE_URL') ?: env('POSTGRES_URL') ?: env('SUPABASE_DB_URL')),
                    'has_db_host'      => !empty(env('DB_HOST')),
                    'has_db_user'      => !empty(env('DB_USER')),
                    'has_db_pass'      => !empty(env('DB_PASS')),
                    'db_port'          => env('DB_PORT', 5432),
                    'db_name'          => env('DB_NAME', 'postgres'),
                    'app_debug'        => (env('APP_DEBUG') === 'true' || env('APP_DEBUG') === true)
                ]
            ];
            try {
                $db = Database::getConnection();
                $health['database']['connected'] = true;
                $health['database']['driver'] = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
                $stmt = $db->query("SELECT count(*) FROM receitas");
                $health['database']['recipes_count'] = (int)$stmt->fetchColumn();
            } catch (Throwable $e) {
                $health['status'] = 'error';
                $health['database']['connected'] = false;
                $health['database']['error'] = $e->getMessage();
                http_response_code(500);
            }
            echo json_encode($health, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;

        default:
            // Rota não encontrada (404)
            http_response_code(404);
            require_once VIEWS_PATH . '/layouts/header.php';
            echo "
                <div class='max-w-xl mx-auto my-16 bg-white p-8 rounded-2xl shadow-sm text-center border border-stone-200'>
                    <div class='text-6xl mb-4'>🍳🔍</div>
                    <h1 class='text-2xl font-bold text-stone-800 mb-2'>Página não encontrada</h1>
                    <p class='text-stone-600 mb-6'>A receita ou rota solicitada não existe ou foi movida.</p>
                    <a href='/' class='inline-block bg-amber-600 hover:bg-amber-700 text-white font-medium px-6 py-2.5 rounded-xl transition-all shadow'>Voltar à Página Inicial</a>
                </div>
            ";
            require_once VIEWS_PATH . '/layouts/footer.php';
            break;
    }
} catch (Throwable $e) {
    error_log("Erro na aplicação: " . $e->getMessage());
    http_response_code(500);
    require_once VIEWS_PATH . '/layouts/header.php';

    $debugBox = '';
    $isDev = (env('APP_DEBUG') === 'true' || env('APP_DEBUG') === true || env('APP_DEBUG') === '1' || env('APP_ENV') === 'development' || !empty($_GET['debug']));
    if ($isDev) {
        $errorDetail = htmlspecialchars($e->getMessage());
        $debugBox = "
            <div class='mt-6 p-4 bg-rose-100/90 border border-rose-300 rounded-xl text-left text-xs text-rose-950 font-mono'>
                <div class='font-bold uppercase tracking-wider text-rose-900 mb-1.5'>🔍 Detalhes Técnicos (Modo Debug Ativo):</div>
                <div class='break-all whitespace-pre-wrap leading-relaxed'>{$errorDetail}</div>
            </div>
        ";
    }

    echo "
        <div class='max-w-xl mx-auto my-16 bg-rose-50 p-8 rounded-2xl border border-rose-200 text-center shadow-sm'>
            <div class='text-5xl mb-4'>⚠️</div>
            <h1 class='text-2xl font-bold text-rose-800 mb-2'>Ops! Algo deu errado</h1>
            <p class='text-rose-700 mb-2 leading-relaxed'>Ocorreu um problema ao processar sua requisição. Por favor, tente novamente em instantes.</p>
            {$debugBox}
            <div class='mt-6'>
                <a href='/' class='inline-block bg-rose-600 hover:bg-rose-700 text-white font-medium px-6 py-2.5 rounded-xl transition-all shadow'>Voltar ao Início</a>
            </div>
        </div>
    ";
    require_once VIEWS_PATH . '/layouts/footer.php';
}
