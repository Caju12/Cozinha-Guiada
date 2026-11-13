<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Database.php';

use App\Database;

session_name($_ENV['SESSION_NAME'] ?? 'cozinha_guiada');
session_start();

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = Database::connect();

    if (isset($_POST['register'])) {
        $nome = trim((string) ($_POST['nome'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $senha = (string) ($_POST['senha'] ?? '');

        if ($nome === '' || $email === '' || $senha === '') {
            $error = 'Preencha nome, e-mail e senha.';
        } else {
            $exists = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $exists->execute([':email' => $email]);

            if ($exists->fetch()) {
                $error = 'E-mail já cadastrado.';
            } else {
                $hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (nome, email, senha_hash, perfil) VALUES (:nome, :email, :senha_hash, :perfil)');
                $stmt->execute([
                    ':nome' => $nome,
                    ':email' => $email,
                    ':senha_hash' => $hash,
                    ':perfil' => 'usuario',
                ]);
                $success = 'Cadastro realizado com sucesso! Faça login.';
            }
        }
    }

    if (isset($_POST['login'])) {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $senha = (string) ($_POST['senha'] ?? '');

        $stmt = $pdo->prepare('SELECT id, nome, email, senha_hash, perfil FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($senha, $user['senha_hash'])) {
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_nome'] = $user['nome'];
            $_SESSION['user_perfil'] = $user['perfil'];
            header('Location: /');
            exit;
        }

        $error = 'Credenciais inválidas.';
    }

    if (isset($_POST['add_recipe'])) {
        if (empty($_SESSION['user_id'])) {
            $error = 'Você precisa estar logado para adicionar uma receita.';
        } else {
            $titulo = trim((string) ($_POST['titulo'] ?? ''));
            $descricao = trim((string) ($_POST['descricao'] ?? ''));
            $categoria_id = (int) ($_POST['categoria_id'] ?? 0);
            $tempo = (int) ($_POST['tempo_preparo'] ?? 0);
            $dificuldade = in_array($_POST['dificuldade'] ?? '', ['facil', 'medio', 'dificil'], true) ? $_POST['dificuldade'] : 'facil';

            if ($titulo === '' || $descricao === '') {
                $error = 'Título e descrição são obrigatórios.';
            } else {
                $insert = $pdo->prepare('INSERT INTO recipes (user_id, titulo, descricao, categoria_id, tempo_preparo, dificuldade) VALUES (:user_id, :titulo, :descricao, :categoria_id, :tempo_preparo, :dificuldade)');
                $insert->execute([
                    ':user_id' => $_SESSION['user_id'],
                    ':titulo' => $titulo,
                    ':descricao' => $descricao,
                    ':categoria_id' => $categoria_id ?: null,
                    ':tempo_preparo' => $tempo,
                    ':dificuldade' => $dificuldade,
                ]);
                $success = 'Receita cadastrada com sucesso!';
            }
        }
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: /');
    exit;
}

$recipes = $pdo = Database::connect();
$recipeStmt = $pdo->prepare('SELECT r.*, c.nome AS categoria, u.nome AS autor FROM recipes r LEFT JOIN categories c ON c.id = r.categoria_id LEFT JOIN users u ON u.id = r.user_id ORDER BY r.created_at DESC LIMIT 15');
$recipeStmt->execute();
$recipes = $recipeStmt->fetchAll(PDO::FETCH_ASSOC);

$categoryStmt = $pdo->prepare('SELECT * FROM categories ORDER BY nome ASC');
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

?><!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cozinha Guiada</title>
    <style>
        :root { --bg:#f8f5f0; --panel:#fff; --accent:#d96a2b; --text:#2a2a2a; --muted:#5d5d5d; --border:#e7d9cb; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: Arial, sans-serif; background:var(--bg); color:var(--text); }
        .container { max-width:1100px; margin:0 auto; padding:24px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; background:#fff; border:1px solid var(--border); padding:16px 20px; border-radius:12px; }
        .brand { font-size:1.5rem; font-weight:bold; color:var(--accent); }
        .nav { display:flex; gap:12px; align-items:center; }
        .btn { display:inline-block; text-decoration:none; background:var(--accent); color:#fff; padding:10px 16px; border-radius:10px; border:0; cursor:pointer; }
        .ghost { background:#fff; color:var(--text); border:1px solid var(--border); }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-top:24px; }
        .panel { background:#fff; border:1px solid var(--border); border-radius:12px; padding:20px; }
        form { display:flex; flex-direction:column; gap:10px; }
        input, select, textarea, button { width:100%; padding:10px 12px; border-radius:8px; border:1px solid var(--border); }
        .flash { padding:12px 16px; border-radius:10px; margin-top:16px; }
        .flash.error { background:#ffe5e5; color:#7a1d1d; border:1px solid #f1b4b4; }
        .flash.success { background:#e8f9ee; color:#195b3d; border:1px solid #b1dec0; }
        .recipes { margin-top:24px; display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:20px; }
        .recipe { border:1px solid var(--border); border-radius:12px; background:#fff; padding:16px; }
        .recipe h3 { margin-top:0; }
        .meta { color:var(--muted); font-size:0.9rem; }
        .badge { display:inline-block; background:#fbe9df; color:#8a4b26; border-radius:999px; padding:5px 10px; font-size:0.8rem; }
        @media (max-width: 768px) { .grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="topbar">
            <div class="brand">🍳 Cozinha Guiada</div>
            <div class="nav">
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <span>Olá, <?php echo htmlspecialchars($_SESSION['user_nome']); ?></span>
                    <a class="btn ghost" href="/?logout=1">Sair</a>
                <?php else: ?>
                    <a class="btn ghost" href="#login">Entrar</a>
                    <a class="btn" href="#cadastro">Cadastrar</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="flash error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="flash success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="grid">
            <div class="panel" id="login">
                <h2>Login</h2>
                <form method="post">
                    <input type="email" name="email" placeholder="E-mail" required>
                    <input type="password" name="senha" placeholder="Senha" required>
                    <button class="btn" type="submit" name="login">Entrar</button>
                </form>
            </div>

            <div class="panel" id="cadastro">
                <h2>Cadastro</h2>
                <form method="post">
                    <input type="text" name="nome" placeholder="Seu nome" required>
                    <input type="email" name="email" placeholder="E-mail" required>
                    <input type="password" name="senha" placeholder="Senha" required>
                    <button class="btn" type="submit" name="register">Criar conta</button>
                </form>
            </div>
        </div>

        <?php if (!empty($_SESSION['user_id'])): ?>
            <div class="panel" style="margin-top:24px;">
                <h2>Adicionar receita</h2>
                <form method="post">
                    <input type="text" name="titulo" placeholder="Título da receita" required>
                    <textarea name="descricao" placeholder="Descrição da receita" rows="4" required></textarea>
                    <select name="categoria_id">
                        <option value="">Selecione a categoria</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo (int) $category['id']; ?>"><?php echo htmlspecialchars($category['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="tempo_preparo" min="1" placeholder="Tempo de preparo em minutos" value="30">
                    <select name="dificuldade">
                        <option value="facil">Fácil</option>
                        <option value="medio">Médio</option>
                        <option value="dificil">Difícil</option>
                    </select>
                    <button class="btn" type="submit" name="add_recipe">Salvar receita</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="panel" style="margin-top:24px;">
            <h2>Receitas</h2>
            <div class="recipes">
                <?php foreach ($recipes as $recipe): ?>
                    <article class="recipe">
                        <span class="badge"><?php echo htmlspecialchars($recipe['categoria'] ?? 'Sem categoria'); ?></span>
                        <h3><?php echo htmlspecialchars($recipe['titulo']); ?></h3>
                        <p class="meta"><?php echo htmlspecialchars($recipe['descricao']); ?></p>
                        <div class="meta">Autor: <?php echo htmlspecialchars($recipe['autor'] ?? 'Usuário'); ?> | Tempo: <?php echo (int) $recipe['tempo_preparo']; ?> min</div>
                        <div class="meta">Dificuldade: <?php echo htmlspecialchars($recipe['dificuldade']); ?></div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
