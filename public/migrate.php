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
    // Migración: agrega la columna campaign_objective a brandlift_studies
    echo "<h3>▶ Migración: 2026_10_16_103000_add_campaign_objective_to_brandlift_studies_table</h3>";
    \Illuminate\Support\Facades\Artisan::call('migrate', [
        '--path'  => 'database/migrations/2026_10_16_103000_add_campaign_objective_to_brandlift_studies_table.php',
        '--force' => true,
    ]);
    echo "<pre>" . htmlspecialchars(\Illuminate\Support\Facades\Artisan::output()) . "</pre>";

    // Última migración: agrega la columna assigned_users a brandlift_studies
    echo "<h3>▶ Última migración: 2026_10_16_111500_add_assigned_users_to_brandlift_studies_table</h3>";
    \Illuminate\Support\Facades\Artisan::call('migrate', [
        '--path'  => 'database/migrations/2026_10_16_111500_add_assigned_users_to_brandlift_studies_table.php',
        '--force' => true,
    ]);
    echo "<pre>" . htmlspecialchars(\Illuminate\Support\Facades\Artisan::output()) . "</pre>";

    // Cualquier otra migración pendiente
    echo "<h3>▶ Migraciones pendientes restantes</h3>";
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    $output = \Illuminate\Support\Facades\Artisan::output();
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    echo "<h2>✅ Migraciones completadas con éxito.</h2>";
} catch (\Exception $e) {
    echo "<h2>❌ Error al migrar:</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
