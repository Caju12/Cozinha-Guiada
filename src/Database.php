<?php

define('APP_ROOT', dirname(__DIR__));

if (file_exists(APP_ROOT . '/.env')) {
    $lines = file(APP_ROOT . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }
        [$key, $value] = array_pad(explode('=', $trimmed, 2), 2, '');
        $key = trim($key);
        $value = trim($value);
        if ($key !== '') {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

$_ENV['APP_ENV'] = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'local';
$_ENV['DB_DRIVER'] = $_ENV['DB_DRIVER'] ?? getenv('DB_DRIVER') ?: 'sqlite';
$_ENV['DB_HOST'] = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
$_ENV['DB_PORT'] = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
$_ENV['DB_NAME'] = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'cozinha_guiada';
$_ENV['DB_USER'] = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$_ENV['DB_PASS'] = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';
$_ENV['APP_URL'] = $_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'http://localhost:8000';
$_ENV['SESSION_NAME'] = $_ENV['SESSION_NAME'] ?? getenv('SESSION_NAME') ?: 'cozinha_guiada';
