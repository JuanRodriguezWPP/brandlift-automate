<?php

$content = file_get_contents('routes/web.php');
$routeCode = "Route::post('/api/brandlift/update-creatives', [BrandliftController::class, 'updateCreatives']);\n";
if (strpos($content, 'update-creatives') === false) {
    $content = str_replace(
        "Route::post('/api/brandlift/store-tags', [BrandliftController::class, 'storeTags']);",
        "Route::post('/api/brandlift/store-tags', [BrandliftController::class, 'storeTags']);\n    ".$routeCode,
        $content
    );
    file_put_contents('routes/web.php', $content);
}
echo 'Done';
