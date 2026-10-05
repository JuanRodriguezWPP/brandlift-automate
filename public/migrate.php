<?php
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);
$response = $kernel->handle(
    $request = Request::capture()
);

echo "<h1>Ejecutando Migraciones...</h1>";
try {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    $output = \Illuminate\Support\Facades\Artisan::output();
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    echo "<h2>✅ Migraciones completadas con éxito.</h2>";
} catch (\Exception $e) {
    echo "<h2>❌ Error al migrar:</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
