<?php
// Este archivo lo borraremos apenas funcione para mayor seguridad.
$dbPassword = $_GET['pwd'] ?? '';

if (empty($dbPassword)) {
    die("<h3>Seguridad:</h3>Por favor, para ejecutar este archivo debes agregar tu clave a la URL. <br>Ejemplo: <b>setup.php?pwd=TU_CONTRASEÑA</b>");
}

echo "<pre style='background:#111; color:#0f0; padding:20px; font-size:14px; line-height:1.5;'>";
echo "Iniciando despliegue automático...\n\n";

// 1. Configurar el .env
echo "1. Configurando archivo .env...\n";
$envExamplePath = __DIR__ . '/../.env.example';
$envPath = __DIR__ . '/../.env';

if (file_exists($envExamplePath)) {
    $envContent = file_get_contents($envExamplePath);
    
    // Reemplazar las variables por las de Cloudways
    $envContent = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=zcemumdaeb', $envContent);
    $envContent = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME=zcemumdaeb', $envContent);
    $envContent = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD=' . $dbPassword, $envContent);
    
    // Configuraciones de Producción
    $envContent = preg_replace('/APP_ENV=.*/', 'APP_ENV=production', $envContent);
    $envContent = preg_replace('/APP_DEBUG=.*/', 'APP_DEBUG=false', $envContent);
    $envContent = preg_replace('/APP_URL=.*/', 'APP_URL=http://phpstack-404581-6369702.cloudwaysapps.com', $envContent);
    
    file_put_contents($envPath, $envContent);
    echo "¡Archivo .env configurado correctamente!\n\n";
} else {
    echo "Error: No se encontró .env.example\n\n";
}

// Subir a la carpeta principal para ejecutar comandos
chdir(__DIR__ . '/..');

// 2. Instalar Composer
echo "2. Instalando Laravel (Composer). Esto puede tardar 1 o 2 minutos...\n";
echo shell_exec('export COMPOSER_HOME=/tmp && composer install --optimize-autoloader --no-dev 2>&1');
echo "\n\n";

// 3. Generar Key
echo "3. Generando Llave de Seguridad...\n";
echo shell_exec('php artisan key:generate --force 2>&1');
echo "\n\n";

// 4. Migraciones
echo "4. Creando Tablas en la Base de Datos...\n";
echo shell_exec('php artisan migrate --force 2>&1');
echo "\n\n";

echo "<h1 style='color:lime'>¡TODO LISTO!</h1>";
echo "Ya puedes cerrar esta pestaña. No olvides ir a Cloudways y actualizar el 'Webroot' a public_html/LATAM/public como te comenté en el Paso 4.";
echo "</pre>";
