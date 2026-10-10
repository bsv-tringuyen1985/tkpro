<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Bao tri (neu co file maintenance.php)...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Dang ky Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel va xu ly request...
$app = require __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
