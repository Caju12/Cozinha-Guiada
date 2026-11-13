<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: config/constants.php
// DESCRIÇÃO: Definição de constantes globais, caminhos e carregador de variáveis
// ==============================================================================

// Caminhos base no sistema de arquivos
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'config');
define('SRC_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'src');
define('VIEWS_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'views');
define('PUBLIC_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'public');
define('UPLOADS_PATH', PUBLIC_PATH . DIRECTORY_SEPARATOR . 'uploads');
define('RECIPE_UPLOADS_PATH', UPLOADS_PATH . DIRECTORY_SEPARATOR . 'receitas');

// Perfis de Usuário no Sistema (RBAC)
define('ROLE_VISITANTE', 'visitante');
define('ROLE_CADASTRADO', 'cadastrado');
define('ROLE_PRODUTOR', 'produtor');
define('ROLE_ADMIN', 'administrador');

// Visibilidade de Receitas (RF40)
define('VISIBILITY_PUBLIC', 'publica');
define('VISIBILITY_PRIVATE', 'privada');

// Status de Moderação (RF49, RF50)
define('STATUS_ACTIVE', 'ativo');
define('STATUS_REMOVED', 'removido');

// Status de Denúncias (RF52)
define('REPORT_PENDING', 'pendente');
define('REPORT_ANALYZED', 'analisada');
define('REPORT_DISCARDED', 'descartada');
define('REPORT_ACTION_TAKEN', 'conteudo_removido');

/**
 * Função utilitária para carregar variáveis do arquivo .env
 * Evita a necessidade de bibliotecas externas no ambiente XAMPP.
 */
function loadEnv(string $filePath): void {
    if (!file_exists($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        // Ignora comentários e linhas vazias
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }

        if (str_contains($trimmed, '=')) {
            [$key, $value] = explode('=', $trimmed, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove aspas simples ou duplas ao redor do valor, se houver
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

// Carrega o arquivo .env da raiz do projeto
loadEnv(ROOT_PATH . DIRECTORY_SEPARATOR . '.env');

/**
 * Helper para obter variável de ambiente com valor padrão
 */
function env(string $key, mixed $default = null): mixed {
    $value = getenv($key);
    if ($value === false) {
        return $_ENV[$key] ?? $default;
    }
    return $value;
}
