<?php
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (!file_exists($logFile)) {
    die("El archivo de logs no existe.");
}
$lines = file($logFile);
$lastLines = array_slice($lines, -100);
echo "<pre style='background:#111; color:#0f0; padding:20px;'>";
foreach ($lastLines as $line) {
    echo htmlspecialchars($line);
}
echo "</pre>";
