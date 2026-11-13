<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: src/Models/Recipe.php
// DESCRIÇÃO: Consultas otimizadas e manipulação de receitas, filtros e detalhes
// ATENDE AOS REQUISITOS: RF01, RF02, RF03, RF08, RF09, RF10, RF11, RF12, RF13,
//                        RF14, RF15, RF16, RF17, RF53, RNF04, RNF11
// ==============================================================================

require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/constants.php';

class Recipe {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Busca receitas públicas ativas aplicando múltiplos filtros combinados (RF01, RF02, RF03, RF08, RF10, RF11).
     *
     * @param array $filters Parâmetros de filtro:
     *   - 'q': termo textual livre (título ou descrição)
     *   - 'categoria_id': ID da categoria
     *   - 'compatibilidades': array de IDs de compatibilidade (relação AND estrita - RF11)
     *   - 'ingredientes': array de IDs de ingredientes (busca por despensa - RF08)
     * @return array Lista de receitas correspondentes
     */
    public function search(array $filters = []): array {
        $sql = "
            SELECT 
                r.id, 
                r.titulo, 
                r.descricao, 
                r.tempo_preparo_minutos, 
                r.rendimento, 
                r.temperatura, 
                r.intensidade_fogo, 
                r.ponto_esperado, 
                r.criado_em,
                u.id AS autor_id, 
                u.nome AS autor_nome,
                COALESCE(AVG(a.nota), 0) AS media_avaliacao,
                COUNT(DISTINCT a.usuario_id) AS total_avaliacoes
            FROM receitas r
            JOIN usuarios u ON r.usuario_id = u.id
            LEFT JOIN avaliacoes a ON a.receita_id = r.id
            WHERE r.visibilidade = 'publica' 
              AND r.status_moderacao = 'ativo'
        ";

        $params = [];

        // 1. Busca textual livre por título ou descrição (RF02)
        if (!empty($filters['q'])) {
            $sql .= " AND (r.titulo LIKE ? OR r.descricao LIKE ?)";
            $searchTerm = '%' . trim($filters['q']) . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // 2. Filtro por Categoria (RF03)
        if (!empty($filters['categoria_id'])) {
            $sql .= " AND r.id IN (
                SELECT rc.receita_id 
                FROM receita_categorias rc 
                WHERE rc.categoria_id = ?
            )";
            $params[] = (int)$filters['categoria_id'];
        }

        // 3. Filtro de Compatibilidades Alimentares com relação "E" (AND estrito - RF10, RF11)
        // A receita DEVE atender a TODAS as compatibilidades selecionadas simultaneamente
        if (!empty($filters['compatibilidades']) && is_array($filters['compatibilidades'])) {
            $compIds = array_filter(array_map('intval', $filters['compatibilidades']));
            $compCount = count($compIds);

            if ($compCount > 0) {
                $placeholders = implode(',', array_fill(0, $compCount, '?'));
                $sql .= " AND r.id IN (
                    SELECT rc.receita_id 
                    FROM receita_compatibilidades rc 
                    WHERE rc.compatibilidade_id IN ({$placeholders}) 
                    GROUP BY rc.receita_id 
                    HAVING COUNT(DISTINCT rc.compatibilidade_id) = ?
                )";
                foreach ($compIds as $cId) {
                    $params[] = $cId;
                }
                $params[] = $compCount;
            }
        }

        // 4. Busca por Ingredientes Disponíveis na Despensa (RF08 / CA08)
        if (!empty($filters['ingredientes']) && is_array($filters['ingredientes'])) {
            $ingIds = array_filter(array_map('intval', $filters['ingredientes']));
            $ingCount = count($ingIds);

            if ($ingCount > 0) {
                $placeholders = implode(',', array_fill(0, $ingCount, '?'));
                $sql .= " AND r.id IN (
                    SELECT ri.receita_id 
                    FROM receita_ingredientes ri 
                    WHERE ri.ingrediente_id IN ({$placeholders})
                )";
                foreach ($ingIds as $iId) {
                    $params[] = $iId;
                }
            }
        }

        $sql .= " GROUP BY r.id ORDER BY r.criado_em DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $recipes = $stmt->fetchAll();

        // Anexa as categorias e compatibilidades resumidas a cada receita para exibição em badges
        foreach ($recipes as &$rec) {
            $rec['categorias'] = $this->getRecipeCategories($rec['id']);
            $rec['compatibilidades'] = $this->getRecipeCompatibilities($rec['id']);
        }

        return $recipes;
    }

    /**
     * Retorna todos os dados detalhados de uma receita específica (RF12 a RF17)
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT 
                r.*, 
                u.id AS autor_id, 
                u.nome AS autor_nome, 
                u.bio AS autor_bio,
                COALESCE(AVG(a.nota), 0) AS media_avaliacao,
                COUNT(DISTINCT a.usuario_id) AS total_avaliacoes
            FROM receitas r
            JOIN usuarios u ON r.usuario_id = u.id
            LEFT JOIN avaliacoes a ON a.receita_id = r.id
            WHERE r.id = ?
            GROUP BY r.id
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $recipe = $stmt->fetch();

        if (!$recipe) {
            return null;
        }

        // Carrega relações estruturadas
        $recipe['categorias'] = $this->getRecipeCategories($id);
        $recipe['compatibilidades'] = $this->getRecipeCompatibilities($id);
        $recipe['utensilios'] = $this->getRecipeUtensils($id);
        $recipe['ingredientes'] = $this->getRecipeIngredientsWithSubstitutions($id);
        $recipe['etapas'] = $this->getRecipeSteps($id);
        $recipe['imagens'] = $this->getRecipeImages($id);

        return $recipe;
    }

    /**
     * Retorna receitas públicas de um produtor específico (RF53, CA53)
     */
    public function findByProducer(int $producerId): array {
        $stmt = $this->db->prepare("
            SELECT 
                r.*, 
                u.nome AS autor_nome,
                COALESCE(AVG(a.nota), 0) AS media_avaliacao,
                COUNT(DISTINCT a.usuario_id) AS total_avaliacoes
            FROM receitas r
            JOIN usuarios u ON r.usuario_id = u.id
            LEFT JOIN avaliacoes a ON a.receita_id = r.id
            WHERE r.usuario_id = ? 
              AND r.visibilidade = 'publica' 
              AND r.status_moderacao = 'ativo'
            GROUP BY r.id
            ORDER BY r.criado_em DESC
        ");
        $stmt->execute([$producerId]);
        $recipes = $stmt->fetchAll();

        foreach ($recipes as &$rec) {
            $rec['categorias'] = $this->getRecipeCategories($rec['id']);
            $rec['compatibilidades'] = $this->getRecipeCompatibilities($rec['id']);
        }

        return $recipes;
    }

    /**
     * Busca dados públicos do produtor (RF53)
     */
    public function getProducerProfile(int $producerId): ?array {
        $stmt = $this->db->prepare("
            SELECT id, nome, email, bio, perfil, criado_em 
            FROM usuarios 
            WHERE id = ? AND ativo = 1 
            LIMIT 1
        ");
        $stmt->execute([$producerId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Retorna todas as categorias cadastradas
     */
    public function getAllCategories(): array {
        $stmt = $this->db->query("SELECT id, nome, slug FROM categorias ORDER BY nome ASC");
        return $stmt->fetchAll();
    }

    /**
     * Retorna todas as compatibilidades cadastradas (RNF19)
     */
    public function getAllCompatibilities(): array {
        $stmt = $this->db->query("SELECT id, nome, slug, descricao FROM compatibilidades_alimentares ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    /**
     * Retorna todos os ingredientes do catálogo para busca por despensa (RF08)
     */
    public function getAllIngredients(): array {
        $stmt = $this->db->query("SELECT id, nome FROM ingredientes ORDER BY nome ASC");
        return $stmt->fetchAll();
    }

    // --------------------------------------------------------------------------
    // MÉTODOS AUXILIARES DE RELACIONAMENTO
    // --------------------------------------------------------------------------

    public function getRecipeCategories(int $recipeId): array {
        $stmt = $this->db->prepare("
            SELECT c.id, c.nome, c.slug 
            FROM categorias c
            JOIN receita_categorias rc ON rc.categoria_id = c.id
            WHERE rc.receita_id = ?
        ");
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }

    public function getRecipeCompatibilities(int $recipeId): array {
        $stmt = $this->db->prepare("
            SELECT comp.id, comp.nome, comp.slug, comp.descricao
            FROM compatibilidades_alimentares comp
            JOIN receita_compatibilidades rc ON rc.compatibilidade_id = comp.id
            WHERE rc.receita_id = ?
        ");
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }

    public function getRecipeUtensils(int $recipeId): array {
        $stmt = $this->db->prepare("
            SELECT u.id, u.nome
            FROM utensilios u
            JOIN receita_utensilios ru ON ru.utensilio_id = u.id
            WHERE ru.receita_id = ?
        ");
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }

    public function getRecipeIngredientsWithSubstitutions(int $recipeId): array {
        $stmt = $this->db->prepare("
            SELECT 
                ri.id AS receita_ingrediente_id,
                ri.ingrediente_id,
                i.nome AS ingrediente_nome,
                ri.quantidade,
                ri.unidade,
                ri.observacao
            FROM receita_ingredientes ri
            JOIN ingredientes i ON ri.ingrediente_id = i.id
            WHERE ri.receita_id = ?
            ORDER BY ri.id ASC
        ");
        $stmt->execute([$recipeId]);
        $ingredients = $stmt->fetchAll();

        // Para cada ingrediente, busca alternativas cadastradas (RF13, RF43, RF44, RF45)
        $stmtSubst = $this->db->prepare("
            SELECT 
                s.id, 
                s.texto_substituto, 
                s.proporcao, 
                s.observacao,
                i_sub.nome AS ingrediente_substituto_nome
            FROM substituicoes_ingrediente s
            LEFT JOIN ingredientes i_sub ON s.ingrediente_substituto_id = i_sub.id
            WHERE s.receita_ingrediente_id = ?
        ");

        foreach ($ingredients as &$ing) {
            $stmtSubst->execute([$ing['receita_ingrediente_id']]);
            $ing['substituicoes'] = $stmtSubst->fetchAll();
        }

        return $ingredients;
    }

    public function getRecipeSteps(int $recipeId): array {
        $stmt = $this->db->prepare("
            SELECT id, ordem, descricao 
            FROM etapas_receita 
            WHERE receita_id = ? 
            ORDER BY ordem ASC
        ");
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }

    public function getRecipeImages(int $recipeId): array {
        $stmt = $this->db->prepare("
            SELECT id, caminho, ordem 
            FROM imagens_receita 
            WHERE receita_id = ? 
            ORDER BY ordem ASC
        ");
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }

    public function getRecipeComments(int $recipeId): array {
        $stmt = $this->db->prepare("
            SELECT 
                c.id, 
                c.usuario_id, 
                c.texto, 
                c.status, 
                c.criado_em,
                u.nome AS autor_nome,
                u.perfil AS autor_perfil
            FROM comentarios c
            JOIN usuarios u ON c.usuario_id = u.id
            WHERE c.receita_id = ? AND c.status = 'ativo'
            ORDER BY c.criado_em DESC
        ");
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }

    public function isFavoritedByUser(int $recipeId, int $userId): bool {
        if ($userId <= 0) return false;
        $stmt = $this->db->prepare("SELECT 1 FROM favoritos WHERE receita_id = ? AND usuario_id = ? LIMIT 1");
        $stmt->execute([$recipeId, $userId]);
        return (bool)$stmt->fetch();
    }

    public function getUserRating(int $recipeId, int $userId): ?int {
        if ($userId <= 0) return null;
        $stmt = $this->db->prepare("SELECT nota FROM avaliacoes WHERE receita_id = ? AND usuario_id = ? LIMIT 1");
        $stmt->execute([$recipeId, $userId]);
        $nota = $stmt->fetchColumn();
        return $nota !== false ? (int)$nota : null;
    }
}
