<?php

namespace App\Console\Commands;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Sheets;
use Illuminate\Console\Command;
use Exception;

class GoogleAuthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:auth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Autorizar Google Workspace API vía OAuth 2.0 y generar Refresh Token';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $client = new Client();
        $client->setApplicationName('Brandlift Automation');
        $client->setScopes([Drive::DRIVE, Sheets::SPREADSHEETS]);
        
        $credentialsPath = storage_path('app/oauth_client_credentials.json');
        
        if (!file_exists($credentialsPath)) {
            $this->error("No se encontró el archivo: {$credentialsPath}");
            $this->error("Asegúrate de descargar el JSON desde Google Cloud y guardarlo en esa ruta.");
            return 1;
        }

        try {
            $client->setAuthConfig($credentialsPath);
        } catch (Exception $e) {
            $this->error("El archivo JSON no es válido: " . $e->getMessage());
            return 1;
        }
        
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        
        // Use a loopback redirect URI for Desktop app credentials
        $client->setRedirectUri('http://localhost');

        $tokenPath = storage_path('app/google_refresh_token.json');

        if (file_exists($tokenPath)) {
            $this->info("Ya existe un token guardado en: {$tokenPath}");
            if (!$this->confirm('¿Deseas borrarlo y autenticar de nuevo?')) {
                return 0;
            }
            unlink($tokenPath);
        }

        $authUrl = $client->createAuthUrl();

        $this->info("\n=========================================================");
        $this->info("1. Copia la siguiente URL y pégala en tu navegador:");
        $this->line("\n" . $authUrl . "\n");
        $this->info("2. Inicia sesión con tu cuenta de Google y dale a 'Permitir'.");
        $this->info("3. El navegador te redirigirá a 'http://localhost/?code=...'");
        $this->info("   (Si te sale 'No se puede conectar' es normal).");
        $this->info("4. Copia toda la URL de la barra de direcciones y pégala aquí.");
        $this->info("=========================================================\n");

        $fullUrl = $this->ask('Pega la URL a la que fuiste redirigido');

        if (empty($fullUrl)) {
            $this->error("Cancelado.");
            return 1;
        }

        // Parse the code from the pasted URL
        $query = parse_url($fullUrl, PHP_URL_QUERY);
        parse_str($query, $params);
        $authCode = $params['code'] ?? null;

        // If they just pasted the code directly, use it
        if (!$authCode) {
            $authCode = $fullUrl;
        }

        try {
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(implode(', ', $accessToken));
            }
            
            // Save the token to a file
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            $this->info("\n✅ ¡Autenticación exitosa!");
            $this->info("Token guardado en: {$tokenPath}");
            $this->info("El sistema ahora usará tu cuenta automáticamente para las operaciones de Drive y Sheets.\n");
            
            return 0;
        } catch (Exception $e) {
            $this->error("\n❌ Error de autenticación: " . $e->getMessage());
            return 1;
        }
    }
}
