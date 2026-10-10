<?php

// The launcher explicitly selects .runtime/local.sqlite; never use the production DB here.
$root = dirname(__DIR__);
if (getenv('DB_CONNECTION') !== 'sqlite'
    || str_replace('\\', '/', getenv('DB_DATABASE') ?: '') !== str_replace('\\', '/', $root.'/.runtime/local.sqlite')) {
    throw new RuntimeException('Local startup requires the dedicated SQLite database.');
}

require $root.'/api/vendor/autoload.php';
$app = require $root.'/api/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
foreach (['config:clear', 'migrate'] as $command) {
    $exit = $kernel->call($command, $command === 'migrate' ? ['--force' => true] : []);
    echo $kernel->output();
    if ($exit !== 0) { exit($exit); }
}
if (Illuminate\Support\Facades\DB::table('users')->count() === 0) {
    $exit = $kernel->call('db:seed', ['--force' => true]);
    echo $kernel->output();
    if ($exit !== 0) { exit($exit); }
}
echo "Local database ready.\n";
