<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Sheets;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleWorkspaceService
{
    protected $client;
    protected $driveService;

    // Constants from Implementation Plan
    const FOLDER_2026_ID = '1OIeg01OAC5SsBW4i5XQqtGUCrs6eAj_p';
    const TEMPLATE_SHEET_ID = '1RySKN476EmsnP--y9i8IP_PmkcoYHShDMNvackUh_Pc';

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setApplicationName('Brandlift Automation');
        // Configure Scopes needed for Drive and Sheets
        $this->client->setScopes([
            Drive::DRIVE,
            Sheets::SPREADSHEETS
        ]);
        
        $credentialsPath = storage_path('app/oauth_client_credentials.json');
        
        if (!file_exists($credentialsPath)) {
            throw new Exception('Google OAuth Client JSON no encontrado en storage/app/oauth_client_credentials.json');
        }
        
        $this->client->setAuthConfig($credentialsPath);
        $this->client->setAccessType('offline');

        $tokenPath = storage_path('app/google_refresh_token.json');
        
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $this->client->setAccessToken($accessToken);
        } else {
            throw new Exception('Token de Google no generado. Debes correr "php artisan google:auth" primero.');
        }

        // Auto-refresh token if expired
        if ($this->client->isAccessTokenExpired()) {
            if ($this->client->getRefreshToken()) {
                $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));
            } else {
                throw new Exception("El token de Google ha expirado y no tiene Refresh Token. Por favor, borra google_refresh_token.json y vuelve a correr php artisan google:auth");
            }
        }

        $this->driveService = new Drive($this->client);
    }

    /**
     * Busca o crea la carpeta del mercado (ej. "MEX") dentro de la carpeta "2026"
     * 
     * @param string $marketName
     * @return string Folder ID
     */
    public function findOrCreateMarketFolder($marketName)
    {
        // 1. Buscar si ya existe la carpeta del mercado
        $query = sprintf(
            "mimeType='application/vnd.google-apps.folder' and name='%s' and '%s' in parents and trashed=false",
            $marketName,
            self::FOLDER_2026_ID
        );
        
        $results = $this->driveService->files->listFiles([
            'q' => $query,
            'spaces' => 'drive',
            'fields' => 'files(id, name)',
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true
        ]);
        
        $files = $results->getFiles();
        
        if (count($files) > 0) {
            // Ya existe
            return $files[0]->getId();
        }
        
        // 2. Si no existe, crearla
        $folderMetadata = new DriveFile([
            'name' => $marketName,
            'parents' => [self::FOLDER_2026_ID],
            'mimeType' => 'application/vnd.google-apps.folder'
        ]);
        
        $folder = $this->driveService->files->create($folderMetadata, [
            'fields' => 'id',
            'supportsAllDrives' => true
        ]);
        
        Log::info("Created new Market Folder '{$marketName}' with ID: " . $folder->getId());
        
        return $folder->getId();
    }

    /**
     * Duplica el Template de Brandlift y lo guarda en la carpeta del mercado
     * 
     * @param string $campaignName
     * @param string $marketFolderId
     * @return string ID del nuevo Sheet creado
     */
    public function duplicateTemplate($newFileName, $marketFolderId)
    {
        $copyMetadata = new DriveFile([
            'name' => $newFileName,
            'parents' => [$marketFolderId]
        ]);
        
        try {
            $copiedFile = $this->driveService->files->copy(self::TEMPLATE_SHEET_ID, $copyMetadata, [
                'fields' => 'id',
                'supportsAllDrives' => true
            ]);
            
            Log::info("Duplicated template to '{$newFileName}' with ID: " . $copiedFile->getId());
            
            return $copiedFile->getId();
        } catch (Exception $e) {
            Log::error('Error al duplicar Template Sheet: ' . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Appends a row of data to the specified Google Sheet.
     */
    public function appendRowToSheet(string $sheetId, array $values)
    {
        try {
            $sheetsService = new Sheets($this->client);
            $body = new \Google\Service\Sheets\ValueRange([
                'values' => [$values]
            ]);
            $params = [
                'valueInputOption' => 'USER_ENTERED'
            ];
            
            $sheetsService->spreadsheets_values->append($sheetId, 'Respuestas!A:A', $body, $params);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to append row to Google Sheet', [
                'sheet_id' => $sheetId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
