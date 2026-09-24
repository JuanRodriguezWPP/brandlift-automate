<?php
/**
 * Script de despliegue automático para Cloudways
 * No usa shell_exec, exec, putenv ni ninguna función bloqueada.
 * Hace todo con PHP puro y PDO.
 */

// Seguridad: requiere la clave de la BD como parámetro
$dbPassword = $_GET['pwd'] ?? '';
if (empty($dbPassword)) {
    die("<h3>⛔ Seguridad:</h3>Agrega tu clave a la URL. Ejemplo: <b>setup.php?pwd=TU_CONTRASEÑA_DB</b>");
}

$dbName = 'zcemumdaeb';
$dbUser = 'zcemumdaeb';
$dbHost = 'localhost';

echo "<pre style='background:#111; color:#0f0; padding:20px; font-size:14px; line-height:1.8; max-width:900px; margin:40px auto; border-radius:12px;'>";
echo "╔══════════════════════════════════════════╗\n";
echo "║  DESPLIEGUE AUTOMÁTICO — BRANDLIFT       ║\n";
echo "╚══════════════════════════════════════════╝\n\n";

// =============================================
// PASO 1: Crear el archivo .env
// =============================================
echo "▶ PASO 1: Configurando archivo .env...\n";

$appKey = 'base64:' . base64_encode(random_bytes(32));

$envContent = "APP_NAME=\"Brandlift Automate\"
APP_ENV=production
APP_KEY={$appKey}
APP_DEBUG=false
APP_URL=http://phpstack-404581-6369702.cloudwaysapps.com

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST={$dbHost}
DB_PORT=3306
DB_DATABASE={$dbName}
DB_USERNAME={$dbUser}
DB_PASSWORD={$dbPassword}

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=xcslatam@gmail.com
MAIL_PASSWORD=\"vtqc mkfz zpgj pugn\"
MAIL_FROM_ADDRESS=\"xcslatam@gmail.com\"
MAIL_FROM_NAME=\"Brandlift Automate\"

CM360_CREDENTIALS_PATH=storage/app/cm360-credentials.json
";

$envPath = __DIR__ . '/../.env';
file_put_contents($envPath, $envContent);
echo "  ✅ Archivo .env creado con APP_KEY generada.\n\n";

// =============================================
// PASO 2: Conectar a la Base de Datos
// =============================================
echo "▶ PASO 2: Conectando a la Base de Datos...\n";

try {
    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "  ✅ Conexión exitosa a MySQL ({$dbName}).\n\n";
} catch (PDOException $e) {
    die("  ❌ Error de conexión: " . $e->getMessage() . "\n</pre>");
}

// =============================================
// PASO 3: Crear todas las tablas
// =============================================
echo "▶ PASO 3: Creando tablas en la Base de Datos...\n";

$queries = [
    // --- users ---
    "CREATE TABLE IF NOT EXISTS `users` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
        `role` VARCHAR(255) NOT NULL DEFAULT 'mercado',
        `market` VARCHAR(255) NULL DEFAULT NULL,
        `password` VARCHAR(255) NULL DEFAULT NULL,
        `remember_token` VARCHAR(100) NULL DEFAULT NULL,
        `created_at` TIMESTAMP NULL DEFAULT NULL,
        `updated_at` TIMESTAMP NULL DEFAULT NULL,
        UNIQUE KEY `users_email_unique` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // --- password_reset_tokens ---
    "CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
        `email` VARCHAR(255) NOT NULL,
        `token` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP NULL DEFAULT NULL,
        PRIMARY KEY (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // --- sessions ---
    "CREATE TABLE IF NOT EXISTS `sessions` (
        `id` VARCHAR(255) NOT NULL,
        `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
        `ip_address` VARCHAR(45) NULL DEFAULT NULL,
        `user_agent` TEXT NULL DEFAULT NULL,
        `payload` LONGTEXT NOT NULL,
        `last_activity` INT NOT NULL,
        PRIMARY KEY (`id`),
        KEY `sessions_user_id_index` (`user_id`),
        KEY `sessions_last_activity_index` (`last_activity`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // --- cache ---
    "CREATE TABLE IF NOT EXISTS `cache` (
        `key` VARCHAR(255) NOT NULL,
        `value` MEDIUMTEXT NOT NULL,
        `expiration` BIGINT NOT NULL,
        PRIMARY KEY (`key`),
        KEY `cache_expiration_index` (`expiration`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // --- cache_locks ---
    "CREATE TABLE IF NOT EXISTS `cache_locks` (
        `key` VARCHAR(255) NOT NULL,
        `owner` VARCHAR(255) NOT NULL,
        `expiration` BIGINT NOT NULL,
        PRIMARY KEY (`key`),
        KEY `cache_locks_expiration_index` (`expiration`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // --- jobs ---
    "CREATE TABLE IF NOT EXISTS `jobs` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `queue` VARCHAR(255) NOT NULL,
        `payload` LONGTEXT NOT NULL,
        `attempts` SMALLINT UNSIGNED NOT NULL,
        `reserved_at` INT UNSIGNED NULL DEFAULT NULL,
        `available_at` INT UNSIGNED NOT NULL,
        `created_at` INT UNSIGNED NOT NULL,
        KEY `jobs_queue_index` (`queue`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // --- job_batches ---
    "CREATE TABLE IF NOT EXISTS `job_batches` (
        `id` VARCHAR(255) NOT NULL,
        `name` VARCHAR(255) NOT NULL,
        `total_jobs` INT NOT NULL,
        `pending_jobs` INT NOT NULL,
        `failed_jobs` INT NOT NULL,
        `failed_job_ids` LONGTEXT NOT NULL,
        `options` MEDIUMTEXT NULL DEFAULT NULL,
        `cancelled_at` INT NULL DEFAULT NULL,
        `created_at` INT NOT NULL,
        `finished_at` INT NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // --- failed_jobs ---
    "CREATE TABLE IF NOT EXISTS `failed_jobs` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `uuid` VARCHAR(255) NOT NULL,
        `connection` VARCHAR(255) NOT NULL,
        `queue` VARCHAR(255) NOT NULL,
        `payload` LONGTEXT NOT NULL,
        `exception` LONGTEXT NOT NULL,
        `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // --- brandlift_studies ---
    "CREATE TABLE IF NOT EXISTS `brandlift_studies` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `market` VARCHAR(50) NOT NULL,
        `campaign_name` VARCHAR(255) NOT NULL,
        `client_name` VARCHAR(255) NULL DEFAULT NULL,
        `audiences` JSON NULL DEFAULT NULL,
        `dps_tags` JSON NULL DEFAULT NULL,
        `question_count` INT NOT NULL DEFAULT 1,
        `creative_width` INT NOT NULL DEFAULT 300,
        `creative_height` INT NOT NULL DEFAULT 250,
        `sheet_id` VARCHAR(255) NULL DEFAULT NULL,
        `cm360_campaign_id` VARCHAR(255) NULL DEFAULT NULL,
        `cm360_tags` LONGTEXT NULL DEFAULT NULL,
        `cm360_profile_id` VARCHAR(255) NULL DEFAULT NULL,
        `cm360_advertiser_id` VARCHAR(255) NULL DEFAULT NULL,
        `cm360_pushed` TINYINT(1) NOT NULL DEFAULT 0,
        `cm360_pushed_at` TIMESTAMP NULL DEFAULT NULL,
        `status` VARCHAR(20) NOT NULL DEFAULT 'created',
        `created_by` VARCHAR(255) NULL DEFAULT NULL,
        `created_at` TIMESTAMP NULL DEFAULT NULL,
        `updated_at` TIMESTAMP NULL DEFAULT NULL,
        KEY `brandlift_studies_market_index` (`market`),
        KEY `brandlift_studies_status_index` (`status`),
        KEY `brandlift_studies_created_at_index` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // --- brandlift_questions ---
    "CREATE TABLE IF NOT EXISTS `brandlift_questions` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `brandlift_study_id` BIGINT UNSIGNED NOT NULL,
        `question_number` INT NOT NULL,
        `question_text` TEXT NOT NULL,
        `answers` JSON NOT NULL,
        `creative_html` LONGTEXT NULL DEFAULT NULL,
        `created_at` TIMESTAMP NULL DEFAULT NULL,
        `updated_at` TIMESTAMP NULL DEFAULT NULL,
        KEY `bq_study_qn_index` (`brandlift_study_id`, `question_number`),
        CONSTRAINT `bq_study_fk` FOREIGN KEY (`brandlift_study_id`) REFERENCES `brandlift_studies` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // --- brandlift_creatives ---
    "CREATE TABLE IF NOT EXISTS `brandlift_creatives` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `brandlift_study_id` BIGINT UNSIGNED NOT NULL,
        `question_number` INT NOT NULL,
        `variant_key` VARCHAR(255) NULL DEFAULT NULL,
        `cm360_creative_id` VARCHAR(255) NULL DEFAULT NULL,
        `cm360_asset_id` VARCHAR(255) NULL DEFAULT NULL,
        `creative_html` LONGTEXT NULL DEFAULT NULL,
        `created_at` TIMESTAMP NULL DEFAULT NULL,
        `updated_at` TIMESTAMP NULL DEFAULT NULL,
        CONSTRAINT `bc_study_fk` FOREIGN KEY (`brandlift_study_id`) REFERENCES `brandlift_studies` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // --- migrations (Laravel tracking table) ---
    "CREATE TABLE IF NOT EXISTS `migrations` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `migration` VARCHAR(255) NOT NULL,
        `batch` INT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

$tableCount = 0;
foreach ($queries as $sql) {
    try {
        $pdo->exec($sql);
        // Extract table name for display
        preg_match('/`(\w+)`/', $sql, $m);
        echo "  ✅ Tabla '{$m[1]}' creada.\n";
        $tableCount++;
    } catch (PDOException $e) {
        preg_match('/`(\w+)`/', $sql, $m);
        echo "  ⚠️  Tabla '{$m[1]}': " . $e->getMessage() . "\n";
    }
}

echo "\n  Total: {$tableCount} tablas procesadas.\n\n";

// =============================================
// PASO 4: Registrar migraciones en Laravel
// =============================================
echo "▶ PASO 4: Registrando migraciones en Laravel...\n";

$migrationFiles = [
    '0001_01_01_000000_create_users_table',
    '0001_01_01_000001_create_cache_table',
    '0001_01_01_000002_create_jobs_table',
    '2026_09_16_000001_create_brandlift_studies_table',
    '2026_09_16_000002_create_brandlift_questions_table',
    '2026_09_18_143645_add_cm360_fields_to_studies',
    '2026_09_18_143647_create_brandlift_creatives_table',
    '2026_09_21_223141_add_role_and_nullable_password_to_users_table',
    '2026_09_24_162349_add_details_to_brandlift_studies',
    '2026_09_24_163118_add_market_to_users_table',
    '2026_09_24_172456_add_cm360_tags_to_brandlift_studies',
];

// Clear existing migration records first
$pdo->exec("DELETE FROM `migrations`");

$stmt = $pdo->prepare("INSERT INTO `migrations` (`migration`, `batch`) VALUES (?, 1)");
foreach ($migrationFiles as $migration) {
    $stmt->execute([$migration]);
}
echo "  ✅ " . count($migrationFiles) . " migraciones registradas.\n\n";

// =============================================
// PASO 5: Crear usuario Admin por defecto
// =============================================
echo "▶ PASO 5: Creando usuario Admin...\n";

$existingAdmin = $pdo->query("SELECT COUNT(*) as c FROM `users` WHERE `role` = 'admin'")->fetch();
if ($existingAdmin['c'] == 0) {
    $stmt = $pdo->prepare("INSERT INTO `users` (`name`, `email`, `role`, `market`, `password`, `created_at`, `updated_at`) VALUES (?, ?, 'admin', NULL, NULL, NOW(), NOW())");
    $stmt->execute(['Admin', 'juan.rodriguezv@wppmedia.com']);
    echo "  ✅ Usuario Admin creado (juan.rodriguezv@wppmedia.com).\n\n";
} else {
    echo "  ℹ️  Ya existe un usuario Admin, saltando.\n\n";
}

// =============================================
// PASO 6: Crear carpetas necesarias
// =============================================
echo "▶ PASO 6: Verificando carpetas de almacenamiento...\n";

$dirs = [
    __DIR__ . '/../storage/framework/cache',
    __DIR__ . '/../storage/framework/sessions',
    __DIR__ . '/../storage/framework/views',
    __DIR__ . '/../storage/logs',
    __DIR__ . '/../storage/app/temp',
    __DIR__ . '/../bootstrap/cache',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
}
echo "  ✅ Carpetas de almacenamiento verificadas.\n\n";

// =============================================
// RESULTADO FINAL
// =============================================
echo "╔══════════════════════════════════════════╗\n";
echo "║        🎉  ¡TODO LISTO!  🎉              ║\n";
echo "╚══════════════════════════════════════════╝\n\n";
echo "Ahora falta UN SOLO paso en Cloudways:\n";
echo "  1. Ve a 'Application Settings' en el menú izquierdo.\n";
echo "  2. Cambia el 'Webroot' a: public_html/LATAM/public\n";
echo "  3. Guarda y listo.\n\n";
echo "⚠️  IMPORTANTE: Borra este archivo (setup.php) después\n";
echo "   de terminar por seguridad.\n";
echo "</pre>";
