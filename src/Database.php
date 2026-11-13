<?php

declare(strict_types=1);

namespace App;

use PDO;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connect(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $driver = strtolower(env_value('DB_DRIVER', 'sqlite'));
        if ($driver === 'sqlite') {
            $database = env_value('DB_NAME', 'storage/cozinha_guiada.sqlite');
            $path = str_starts_with($database, '/') ? $database : APP_ROOT . '/' . $database;
            $directory = dirname($path);
            if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
                throw new RuntimeException('Não foi possível criar o diretório do banco SQLite.');
            }
            self::$connection = new PDO('sqlite:' . $path, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            self::$connection->exec('PRAGMA foreign_keys = ON');
            self::initializeSqlite(self::$connection);
            return self::$connection;
        }

        if ($driver === 'mysql') {
            $host = env_value('DB_HOST', '127.0.0.1');
            $port = env_value('DB_PORT', '3306');
            $name = env_value('DB_NAME', 'cozinha_guiada');
            self::$connection = new PDO(
                "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
                env_value('DB_USER', 'root'),
                env_value('DB_PASS'),
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
            return self::$connection;
        }

        throw new RuntimeException('DB_DRIVER precisa ser sqlite ou mysql.');
    }

    private static function initializeSqlite(PDO $pdo): void
    {
        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    senha_hash TEXT NOT NULL,
    perfil TEXT NOT NULL DEFAULT 'usuario',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL UNIQUE
);
CREATE TABLE IF NOT EXISTS recipes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    titulo TEXT NOT NULL,
    descricao TEXT NOT NULL,
    categoria_id INTEGER NULL,
    tempo_preparo INTEGER NOT NULL DEFAULT 0,
    dificuldade TEXT NOT NULL DEFAULT 'facil',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categories(id) ON DELETE SET NULL
);
SQL);
        $insert = $pdo->prepare('INSERT OR IGNORE INTO categories (nome) VALUES (?)');
        foreach (['Café da Manhã', 'Almoço', 'Sobremesas', 'Saladas', 'Massas'] as $category) {
            $insert->execute([$category]);
        }
    }
}
