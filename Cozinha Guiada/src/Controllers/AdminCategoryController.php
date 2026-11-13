<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: src/Controllers/AdminCategoryController.php
// DESCRIÇÃO: Gestão de categorias da plataforma (CRUD - RF51, CA51)
// ==============================================================================

require_once CONFIG_PATH . '/constants.php';
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/Middleware/AuthMiddleware.php';
require_once SRC_PATH . '/Utils/Session.php';

class AdminCategoryController {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Listagem de categorias com contador de receitas associadas (RF51)
     */
    public function index(): void {
        AuthMiddleware::requireAdmin();

        $stmt = $this->db->query("
            SELECT c.*, COUNT(rc.receita_id) AS total_receitas
            FROM categorias c
            LEFT JOIN receita_categorias rc ON rc.categoria_id = c.id
            GROUP BY c.id
            ORDER BY c.nome ASC
        ");
        $categories = $stmt->fetchAll();

        require VIEWS_PATH . '/admin/categories.php';
    }

    /**
     * Criação de nova categoria (RF51)
     */
    public function store(): void {
        AuthMiddleware::requireAdmin();

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token de segurança inválido.');
            header('Location: /admin/categorias');
            exit;
        }

        $nome = trim($_POST['nome'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');

        if (empty($nome)) {
            Session::flash('error', 'O nome da categoria é obrigatório.');
            header('Location: /admin/categorias');
            exit;
        }

        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nome), '-'));
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO categorias (nome, slug, descricao) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $slug, $descricao]);
            Session::flash('success', "Categoria '{$nome}' criada com sucesso!");
        } catch (PDOException $e) {
            Session::flash('error', 'Erro ao criar categoria: verifique se o nome ou slug já existem.');
        }

        header('Location: /admin/categorias');
        exit;
    }

    /**
     * Atualização de categoria existente (RF51)
     */
    public function update(): void {
        AuthMiddleware::requireAdmin();

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token de segurança inválido.');
            header('Location: /admin/categorias');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');

        if ($id <= 0 || empty($nome)) {
            Session::flash('error', 'Dados inválidos.');
            header('Location: /admin/categorias');
            exit;
        }

        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nome), '-'));
        }

        try {
            $stmt = $this->db->prepare("UPDATE categorias SET nome = ?, slug = ?, descricao = ? WHERE id = ?");
            $stmt->execute([$nome, $slug, $descricao, $id]);
            Session::flash('success', 'Categoria atualizada com sucesso!');
        } catch (PDOException $e) {
            Session::flash('error', 'Erro ao atualizar categoria: verifique se o slug já está em uso.');
        }

        header('Location: /admin/categorias');
        exit;
    }

    /**
     * Exclusão de categoria (RF51)
     */
    public function delete(): void {
        AuthMiddleware::requireAdmin();

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token de segurança inválido.');
            header('Location: /admin/categorias');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: /admin/categorias');
            exit;
        }

        $stmt = $this->db->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', 'Categoria excluída com sucesso.');
        header('Location: /admin/categorias');
        exit;
    }
}
