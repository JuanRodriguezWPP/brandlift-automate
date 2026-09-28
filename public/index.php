<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
if (strpos($_SERVER['REQUEST_URI'] ?? '', 'brandlift/store') !== false && strpos($_SERVER['REQUEST_URI'] ?? '', 'store-tags') === false) {
    file_put_contents(__DIR__.'/../storage/logs/raw_requests.log', date('Y-m-d H:i:s') . " - " . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN') . " " . ($_SERVER['REQUEST_URI'] ?? 'UNKNOWN') . "\n", FILE_APPEND);
}

$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
