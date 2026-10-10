<?php
// Read credentials from the environment, never interpolate them into shell/PHP code.
try {
    $dsn = 'mysql:host='.(getenv('DB_HOST') ?: 'mysql').';port='.(getenv('DB_PORT') ?: '3306');
    $pdo = new PDO($dsn.';dbname='.(getenv('DB_DATABASE') ?: 'tkpro'), getenv('DB_USERNAME') ?: 'tkpro', getenv('DB_PASSWORD') ?: '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if (($argv[1] ?? 'connect') === 'users') {
        echo (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    } elseif (($argv[1] ?? '') === 'schema') {
        $pdo->query('SELECT id FROM seo_meta LIMIT 1');
        $pdo->query('SELECT id FROM permissions LIMIT 1');
        if ((int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0) {
            exit(1);
        }
    }
} catch (Throwable $e) {
    fwrite(STDERR, "Database not ready.\n");
    exit(1);
}
