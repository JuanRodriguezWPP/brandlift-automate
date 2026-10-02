<?php

$envPath = __DIR__.'/../.env';
if (file_exists($envPath)) {
    $content = file_get_contents($envPath);
    $content = str_replace('CM360_CREDENTIALS_PAßH', 'CM360_CREDENTIALS_PATH', $content);
    file_put_contents($envPath, $content);
    echo '<h1>✅ Archivo .env corregido exitosamente.</h1>';
} else {
    echo 'El archivo .env no existe.';
}
