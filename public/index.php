<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Database.php';

use App\Database;
use PDO;
use Throwable;

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
session_name(env_value('SESSION_NAME', 'cozinha_guiada'));
session_set_cookie_params(['httponly' => true, 'secure' => $isHttps, 'samesite' => 'Lax', 'path' => '/']);
session_start();

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$error = null;
$success = null;
$pdo = null;

try {
    $pdo = Database::connect();
} catch (Throwable $exception) {
    http_response_code(500);
    $error = env_value('APP_ENV', 'local') === 'production'
        ? 'Não foi possível conectar ao banco. Verifique a configuração do serviço.'
        : 'Erro no banco: ' . $exception->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo instanceof PDO) {
    if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        $error = 'Formulário expirado. Atualize a página e tente novamente.';
    } elseif (isset($_POST['register'])) {
        $nome = trim((string) ($_POST['nome'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $senha = (string) ($_POST['senha'] ?? '');
        if ($nome === '' || mb_strlen($nome) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 8) {
            $error = 'Informe nome, e-mail válido e senha com pelo menos 8 caracteres.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO users (nome, email, senha_hash) VALUES (:nome, :email, :hash)');
                $stmt->execute([':nome' => $nome, ':email' => $email, ':hash' => password_hash($senha, PASSWORD_DEFAULT)]);
                $success = 'Cadastro realizado. Agora entre com seu e-mail e senha.';
            } catch (Throwable) {
                $error = 'Não foi possível cadastrar. Verifique se o e-mail já está em uso.';
            }
        }
    } elseif (isset($_POST['login'])) {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $stmt = $pdo->prepare('SELECT id, nome, senha_hash FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        if ($user && password_verify((string) ($_POST['senha'] ?? ''), $user['senha_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_nome'] = $user['nome'];
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            header('Location: /');
            exit;
        }
        $error = 'E-mail ou senha incorretos.';
    } elseif (isset($_POST['add_recipe']) && !empty($_SESSION['user_id'])) {
        $title = trim((string) ($_POST['titulo'] ?? ''));
        $description = trim((string) ($_POST['descricao'] ?? ''));
        $categoryId = (int) ($_POST['categoria_id'] ?? 0);
        $minutes = filter_var($_POST['tempo_preparo'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1440]]);
        $difficulty = (string) ($_POST['dificuldade'] ?? 'facil');
        if ($title === '' || mb_strlen($title) > 150 || $description === '' || !in_array($difficulty, ['facil', 'medio', 'dificil'], true) || $minutes === false) {
            $error = 'Confira o título, a descrição, o tempo e a dificuldade informados.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO recipes (user_id, titulo, descricao, categoria_id, tempo_preparo, dificuldade) VALUES (:user, :title, :description, :category, :minutes, :difficulty)');
            $stmt->execute([':user' => $_SESSION['user_id'], ':title' => $title, ':description' => $description, ':category' => $categoryId ?: null, ':minutes' => $minutes, ':difficulty' => $difficulty]);
            $success = 'Receita cadastrada.';
        }
    }
}

if (isset($_GET['logout'])) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: /');
    exit;
}

$recipes = $categories = [];
if ($pdo instanceof PDO) {
    $recipes = $pdo->query('SELECT r.*, c.nome AS categoria, u.nome AS autor FROM recipes r LEFT JOIN categories c ON c.id = r.categoria_id JOIN users u ON u.id = r.user_id ORDER BY r.id DESC LIMIT 50')->fetchAll();
    $categories = $pdo->query('SELECT id, nome FROM categories ORDER BY nome')->fetchAll();
}
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Cozinha Guiada</title>
<style>
:root{font-family:system-ui,sans-serif;color:#30251f;background:#faf6f0}*{box-sizing:border-box}body{margin:0}.wrap{max-width:1000px;margin:auto;padding:24px}.bar,.card{background:white;border:1px solid #eadfd4;border-radius:14px;padding:20px}.bar{display:flex;justify-content:space-between;align-items:center}.brand{font-size:1.5rem;font-weight:700;color:#bd5425}.grid,.recipes{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px;margin-top:20px}input,textarea,select,button{width:100%;font:inherit;padding:10px;border:1px solid #d8c9bc;border-radius:8px}form{display:grid;gap:10px}.btn{background:#bd5425;color:white;border:0;cursor:pointer;text-decoration:none;padding:9px 14px;border-radius:8px;display:inline-block}.flash{padding:12px;border-radius:8px;margin-top:16px;background:#fff0ed}.success{background:#e8f7ed}.muted{color:#665b54}.nav{display:flex;gap:12px;align-items:center}@media(max-width:600px){.bar{align-items:flex-start;gap:12px;flex-direction:column}}
</style>
</head><body><main class="wrap">
<header class="bar"><div class="brand">🍳 Cozinha Guiada</div><nav class="nav"><?php if (!empty($_SESSION['user_id'])): ?><span>Olá, <?= h($_SESSION['user_nome']) ?></span><a class="btn" href="/?logout=1">Sair</a><?php else: ?><a href="#login">Entrar</a><a class="btn" href="#cadastro">Cadastrar</a><?php endif; ?></nav></header>
<?php if ($error): ?><p class="flash"><?= h($error) ?></p><?php endif; ?><?php if ($success): ?><p class="flash success"><?= h($success) ?></p><?php endif; ?>
<?php if (empty($_SESSION['user_id'])): ?><section class="grid"><div class="card" id="login"><h2>Entrar</h2><form method="post"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>"><input type="email" name="email" placeholder="E-mail" required><input type="password" name="senha" placeholder="Senha" required><button class="btn" name="login">Entrar</button></form></div><div class="card" id="cadastro"><h2>Criar conta</h2><form method="post"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>"><input name="nome" maxlength="120" placeholder="Nome" required><input type="email" name="email" placeholder="E-mail" required><input type="password" name="senha" minlength="8" placeholder="Senha (mínimo 8 caracteres)" required><button class="btn" name="register">Cadastrar</button></form></div></section><?php else: ?><section class="card" style="margin-top:20px"><h2>Compartilhe uma receita</h2><form method="post"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>"><input name="titulo" maxlength="150" placeholder="Título" required><textarea name="descricao" placeholder="Descreva o preparo e os ingredientes" required></textarea><select name="categoria_id"><option value="0">Sem categoria</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>"><?= h($category['nome']) ?></option><?php endforeach; ?></select><input type="number" name="tempo_preparo" min="1" max="1440" value="30" required><select name="dificuldade"><option value="facil">Fácil</option><option value="medio">Média</option><option value="dificil">Difícil</option></select><button class="btn" name="add_recipe">Salvar receita</button></form></section><?php endif; ?>
<section><h2>Receitas cadastradas</h2><div class="recipes"><?php foreach ($recipes as $recipe): ?><article class="card"><span class="muted"><?= h($recipe['categoria'] ?? 'Sem categoria') ?></span><h3><?= h($recipe['titulo']) ?></h3><p><?= nl2br(h($recipe['descricao'])) ?></p><small class="muted">Por <?= h($recipe['autor']) ?> · <?= (int) $recipe['tempo_preparo'] ?> min · <?= h($recipe['dificuldade']) ?></small></article><?php endforeach; ?><?php if (!$recipes): ?><p class="card">Ainda não há receitas. Cadastre-se para adicionar a primeira.</p><?php endif; ?></div></section>
</main></body></html>
