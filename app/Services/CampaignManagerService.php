<?php

namespace App\Services;

use Google\Client;
use Google\Service\Dfareporting;
use Google\Service\Dfareporting\Ad;
use Google\Service\Dfareporting\Campaign;
use Google\Service\Dfareporting\CampaignCreativeAssociation;
use Google\Service\Dfareporting\ClickTag;
use Google\Service\Dfareporting\ClickThroughUrl;
use Google\Service\Dfareporting\Creative;
use Google\Service\Dfareporting\CreativeAsset;
use Google\Service\Dfareporting\CreativeAssetId;
use Google\Service\Dfareporting\CreativeAssetMetadata;
use Google\Service\Dfareporting\CreativeAssignment;
use Google\Service\Dfareporting\CreativeClickThroughUrl;
use Google\Service\Dfareporting\CreativeRotation;
use Google\Service\Dfareporting\DeliverySchedule;
use Google\Service\Dfareporting\LandingPage;
use Google\Service\Dfareporting\Placement;
use Google\Service\Dfareporting\PlacementAssignment;
use Google\Service\Dfareporting\PricingSchedule;
use Google\Service\Dfareporting\Size;
use Illuminate\Support\Facades\Log;

/**
 * CampaignManagerService
 *
 * Handles all interactions with the Campaign Manager 360 (DFA Reporting) API v5
 * using the official Google API PHP Client Services.
 *
 * Full trafficking flow:
 * 1. Create Campaign (optional)
 * 2. Upload Creative Asset (ZIP)
 * 3. Create Creative
 * 4. Create Placement
 * 5. Create Ad (links Creative to Placement)
 * 6. Generate Ad Tags
 *
 * Required OAuth Scope: https://www.googleapis.com/auth/dfatrafficking
 */
class CampaignManagerService
{
    private Dfareporting $service;

    public function __construct()
    {
        $client = new Client;

        $credentialsPath = config('services.cm360.credentials_path');
        if ($credentialsPath && file_exists(base_path($credentialsPath))) {
            $client->setAuthConfig(base_path($credentialsPath));
            $client->addScope('https://www.googleapis.com/auth/dfatrafficking');
        } else {
            // Fallback for manually provided access token if needed
            $accessToken = config('services.cm360.access_token');
            if ($accessToken) {
                $client->setAccessToken($accessToken);
            }
        }

        $this->service = new Dfareporting($client);
    }

    /**
     * Full trafficking flow: Upload creatives, create placements, ads, and generate tags.
     *
     * @param  string  $profileId  User profile ID
     * @param  string  $advertiserId  Advertiser ID
     * @param  string  $siteId  Site ID for placements
     * @param  string  $campaignName  New Campaign Name (optional)
     * @param  string  $creativeName  Base name for the creatives
     * @param  array  $creatives  Array of creative data [{question_number, html, width, height}]
     * @return array Response with success status, creative IDs and ad tags
     */
    public function uploadCreatives(
        string $profileId,
        string $advertiserId,
        string $siteId,
        string $campaignName,
        string $creativeName,
        array $creatives,
        string $market = '',
        string $clientName = '',
        ?string $backupImageBase64 = null
    ): array {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        $results = [];

        // Resolver sitio adecuado para el subaccount del anunciante
        $siteId = $this->resolveSiteForAdvertiser($profileId, $advertiserId, $siteId);

        // Crear campaña: si se proporcionó nombre, buscar existente o crear nueva; si no, es obligatorio tener una
        $campaignId = null;
        if (! empty($campaignName)) {
            try {
                $year = date('Y');
                $month = date('m');
                $clientSafe = str_replace(' ', '_', $clientName);
                $cm360CampaignName = "{$year}_{$month}_WMSCSLATAM_{$market}_{$clientSafe}_{$campaignName}_brandlift_b";

                $campaignResponse = $this->findOrCreateCampaign($profileId, $advertiserId, $cm360CampaignName);
                $campaignId = $campaignResponse->getId();
                Log::info('Target CM360 Campaign ready', ['id' => $campaignId, 'name' => $cm360CampaignName]);
            } catch (\Exception $e) {
                Log::error('Failed to get or create campaign', ['error' => $e->getMessage()]);

                return [
                    'success' => false,
                    'message' => 'Error al configurar la campaña en CM360: '.$e->getMessage(),
                    'results' => [],
                ];
            }
        } else {
            // campaignId es obligatorio para Placements y Ads en CM360
            return [
                'success' => false,
                'message' => 'El nombre de la campaña es obligatorio. CM360 requiere un Campaign ID para crear Placements y Ads.',
                'results' => [],
            ];
        }

        // Handle Default Backup Image
        $defaultCreativeId = null;
        $w = $creatives[0]['width'] ?? 300;
        $h = $creatives[0]['height'] ?? 250;

        try {
            $tempDir = storage_path('app/temp');
            if (! is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $backupPath = "{$tempDir}/default_backup_".time().'.jpg';

            if (! empty($backupImageBase64)) {
                // Use the frontend-captured image
                $imgData = explode(',', $backupImageBase64);
                $base64 = count($imgData) > 1 ? $imgData[1] : $imgData[0];
                $decoded = base64_decode($base64);
                file_put_contents($backupPath, $decoded);
                Log::info('Using frontend-captured backup image', ['size' => strlen($decoded)]);
            } else {
                // Generate a branded fallback image using GD
                Log::info('Frontend backup image was null, generating server-side fallback');
                $img = imagecreatetruecolor($w, $h);

                // Gradient background (dark blue)
                $bgTop = imagecolorallocate($img, 10, 22, 40);      // #0a1628
                $bgBottom = imagecolorallocate($img, 13, 43, 94);    // #0d2b5e
                for ($y = 0; $y < $h; $y++) {
                    $ratio = $y / max($h - 1, 1);
                    $r = (int) (10 + (13 - 10) * $ratio);
                    $g = (int) (22 + (43 - 22) * $ratio);
                    $b = (int) (40 + (94 - 40) * $ratio);
                    $lineColor = imagecolorallocate($img, $r, $g, $b);
                    imageline($img, 0, $y, $w, $y, $lineColor);
                }

                // "WPP Media" branding text
                $white = imagecolorallocate($img, 255, 255, 255);
                $gray = imagecolorallocate($img, 180, 180, 200);

                // Campaign name (centered)
                $campaignLabel = mb_strimwidth($campaignName, 0, 35, '...');
                $labelWidth = imagefontwidth(3) * strlen($campaignLabel);
                imagestring($img, 3, ($w - $labelWidth) / 2, $h / 2 - 20, $campaignLabel, $white);

                // "WPP Media" at the bottom-right
                $brandText = 'WPP Media';
                $brandWidth = imagefontwidth(2) * strlen($brandText);
                imagestring($img, 2, $w - $brandWidth - 14, $h - 22, $brandText, $gray);

                imagejpeg($img, $backupPath, 90);
                imagedestroy($img);
                Log::info('Generated server-side backup image', ['path' => $backupPath]);
            }

            // Upload image asset (HTML_IMAGE is the correct CM360 asset type for images in DISPLAY creatives)
            $assetResponse = $this->uploadCreativeAsset($profileId, $advertiserId, $backupPath, 'backup_default_'.time().'.jpg', 'HTML_IMAGE');
            Log::info('Backup Image Asset uploaded', ['asset' => $assetResponse->getAssetIdentifier()->getName()]);

            // Create Default Creative (DISPLAY_IMAGE type)
            $year = date('Y');
            $month = date('m');
            $clientSafe = str_replace(' ', '_', $clientName);
            $baseName = "{$year}_{$month}_WMSCSLATAM_{$market}_{$clientSafe}_{$campaignName}_brandlift_b";

            $defaultCreativeResponse = $this->createCreative(
                $profileId,
                $advertiserId,
                "{$baseName}_DefaultBackup_{$w}x{$h}",
                $assetResponse->getAssetIdentifier()->getName(),
                $w,
                $h,
                'IMAGE'
            );
            $defaultCreativeId = $defaultCreativeResponse->getId();
            Log::info('Backup Default Creative created', ['id' => $defaultCreativeId]);

            // Associate with campaign
            $association = new CampaignCreativeAssociation;
            $association->setCreativeId($defaultCreativeId);
            $this->service->campaignCreativeAssociations->insert($profileId, $campaignId, $association);
            Log::info('Backup creative associated with campaign', ['campaignId' => $campaignId]);

            // Cleanup
            if (file_exists($backupPath)) {
                unlink($backupPath);
            }
        } catch (\Exception $e) {
            Log::error('Failed to create default backup creative', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        foreach ($creatives as $creativeData) {
            $questionNum = $creativeData['question_number'];
            $html = $creativeData['html'];

            // Inject clickTag into the HTML to pass CM360 validation
            // CM360 requires: 1) clickTag variable declaration, 2) a click handler using window.open
            $clickTagScript = '<script type="text/javascript">'.
                'var clickTag = "https://www.wppmedia.com/es";'.
                'function handleClick(){window.open(clickTag,"_blank");}'.
                '</script>';

            if (stripos($html, '<head>') !== false) {
                $html = str_ireplace('<head>', '<head>'.$clickTagScript, $html);
            } else {
                $html = $clickTagScript.$html;
            }

            // Add onclick handler to the body if not present
            if (stripos($html, 'onclick') === false && stripos($html, '<body') !== false) {
                $html = preg_replace('/<body([^>]*)>/i', '<body$1 onclick="handleClick()">', $html, 1);
            }

            $width = $creativeData['width'];
            $height = $creativeData['height'];
            $variantKey = $creativeData['variant_key'] ?? null;

            $year = date('Y');
            $month = date('m');
            $clientSafe = str_replace(' ', '_', $clientName);

            // Format: aaaa_mm_WMSCSLATAM_Mercado_cliente_campaña_dps_tag_brandlift_b
            if ($variantKey) {
                // variantKey en el frontend es {dps}_{group}_{tagType} que corresponde a dps_tag
                $name = "{$year}_{$month}_WMSCSLATAM_{$market}_{$clientSafe}_{$campaignName}_{$variantKey}_brandlift_b";
            } else {
                $name = "{$year}_{$month}_WMSCSLATAM_{$market}_{$clientSafe}_{$campaignName}_Q{$questionNum}_brandlift_b";
            }
            // Agregamos las dimensiones al final para evitar colisiones en CM360
            $name = "{$name}_{$width}x{$height}";

            $zipPath = null;
            $currentStep = '';

            try {
                // Step 1: Create a .zip file containing the HTML as index.html
                $currentStep = 'Crear archivo ZIP';
                $zipPath = $this->createZipFromHtml($html, $name);
                Log::info("CM360 Q{$questionNum}: ZIP created", ['path' => $zipPath]);

                // Step 2: Upload the zip as a creative asset
                $currentStep = 'Subir asset creativo';
                $assetResponse = $this->uploadCreativeAsset(
                    $profileId,
                    $advertiserId,
                    $zipPath,
                    "{$name}.zip"
                );
                Log::info("CM360 Q{$questionNum}: Asset uploaded", ['asset' => $assetResponse->getAssetIdentifier()->getName()]);

                // Step 3: Create the creative resource
                $currentStep = 'Crear creativo';
                $creativeResponse = $this->createCreative(
                    $profileId,
                    $advertiserId,
                    $name,
                    $assetResponse->getAssetIdentifier()->getName(),
                    $width,
                    $height
                );

                $creativeId = $creativeResponse->getId();
                Log::info("CM360 Q{$questionNum}: Creative created", ['id' => $creativeId]);

                // Step 3.5: Associate Creative with Campaign (Required to add to Ad)
                $currentStep = 'Asociar creativo con campaña';
                $association = new CampaignCreativeAssociation;
                $association->setCreativeId($creativeId);
                $this->service->campaignCreativeAssociations->insert($profileId, $campaignId, $association);
                Log::info("CM360 Q{$questionNum}: Creative associated with campaign", ['campaignId' => $campaignId]);

                // Step 4: Create Placement
                $currentStep = 'Crear placement';
                $placementResponse = $this->createPlacement(
                    $profileId,
                    $advertiserId,
                    $campaignId,
                    $siteId,
                    $name,
                    $width,
                    $height
                );
                $placementId = $placementResponse->getId();

                // Step 4.5: Ensure this Placement has an active Default Ad assigned immediately
                $currentStep = 'Garantizar Default Ad activo para el placement';
                $this->ensureDefaultAdForPlacement(
                    $profileId,
                    $advertiserId,
                    $campaignId,
                    $placementId,
                    $defaultCreativeId,
                    $name,
                    $width,
                    $height
                );

                // Step 5: Create Ad (links Creative to Placement)
                $currentStep = 'Crear ad';
                $adResponse = $this->createAd(
                    $profileId,
                    $advertiserId,
                    $campaignId,
                    $creativeId,
                    $placementId,
                    $name,
                    $width,
                    $height
                );
                Log::info("CM360 Q{$questionNum}: Ad created", ['id' => $adResponse->getId()]);

                // Step 6: Generate Ad Tags
                $currentStep = 'Generar ad tags';
                $adTags = $this->generateAdTags($profileId, $campaignId, $placementId);
                Log::info("CM360 Q{$questionNum}: Ad tags generated", ['count' => count($adTags)]);

                $results[] = [
                    'question_number' => $questionNum,
                    'variant_key' => $variantKey,
                    'html' => $html,
                    'creative_id' => $creativeId,
                    'asset_id' => $assetResponse->getAssetIdentifier()->getName(),
                    'creative_name' => $name,
                    'campaign_id' => $campaignId,
                    'placement_id' => $placementId,
                    'ad_id' => $adResponse->getId(),
                    'ad_tags' => $adTags,
                    'status' => 'success',
                ];
            } catch (\Exception $e) {
                Log::error("CM360 upload failed for Q{$questionNum} at step: {$currentStep}", [
                    'error' => $e->getMessage(),
                    'step' => $currentStep,
                    'creative_name' => $name,
                    'trace' => $e->getTraceAsString(),
                ]);

                $results[] = [
                    'question_number' => $questionNum,
                    'creative_id' => null,
                    'creative_name' => $name,
                    'status' => 'error',
                    'error' => "Error en '{$currentStep}': ".$e->getMessage(),
                ];
            } finally {
                // Always cleanup temp zip file
                if ($zipPath && file_exists($zipPath)) {
                    unlink($zipPath);
                }
            }
        }

        // Activate and verify all Default Ads across the entire campaign
        $this->activateDefaultAds($profileId, $campaignId);

        $allSuccess = collect($results)->every(fn ($r) => $r['status'] === 'success');
        $failedCount = collect($results)->where('status', 'error')->count();
        $totalCount = count($results);

        // Build detailed message
        if ($allSuccess) {
            $message = 'Creativos subidos y tags generados exitosamente';
        } else {
            $errorDetails = collect($results)
                ->where('status', 'error')
                ->map(fn ($r) => "Q{$r['question_number']}: {$r['error']}")
                ->implode(' | ');
            $message = "{$failedCount} de {$totalCount} creativos fallaron al subirse a CM360. Detalle: {$errorDetails}";
        }

        return [
            'success' => $allSuccess,
            'message' => $message,
            'results' => $results,
        ];
    }

    /**
     * Ensure that a placement has an active Default Ad assigned with a valid backup creative.
     */
    private function ensureDefaultAdForPlacement(
        string $profileId,
        string $advertiserId,
        string $campaignId,
        string $placementId,
        ?string $defaultCreativeId,
        string $placementName,
        int $width,
        int $height
    ): void {
        try {
            // 1. List all default ads for this campaign
            $adsResponse = $this->service->ads->listAds($profileId, [
                'campaignIds' => [$campaignId],
                'type' => 'AD_SERVING_DEFAULT_AD',
            ]);

            $defaultAds = $adsResponse->getAds() ?? [];
            $matchingDefaultAd = null;

            foreach ($defaultAds as $ad) {
                $adSize = $ad->getSize();
                if ($adSize && $adSize->getWidth() == $width && $adSize->getHeight() == $height) {
                    $matchingDefaultAd = $ad;
                    break;
                }
            }

            if ($matchingDefaultAd) {
                // Ensure this placement is in placementAssignments
                $assignments = $matchingDefaultAd->getPlacementAssignments() ?? [];
                $hasPlacement = false;
                foreach ($assignments as $pa) {
                    if ((string) $pa->getPlacementId() === (string) $placementId) {
                        $pa->setActive(true);
                        $hasPlacement = true;
                        break;
                    }
                }

                if (! $hasPlacement) {
                    $newPa = new PlacementAssignment;
                    $newPa->setPlacementId($placementId);
                    $newPa->setActive(true);
                    $assignments[] = $newPa;
                    $matchingDefaultAd->setPlacementAssignments($assignments);
                }

                // Ensure active and not archived
                $matchingDefaultAd->setActive(true);
                $matchingDefaultAd->setArchived(false);

                // Ensure creative assignment has the backup creative if provided
                if ($defaultCreativeId) {
                    $rot = $matchingDefaultAd->getCreativeRotation();
                    if (! $rot) {
                        $rot = new CreativeRotation;
                    }
                    $crAssignments = $rot->getCreativeAssignments() ?? [];
                    if (empty($crAssignments)) {
                        $ca = new CreativeAssignment;
                        $ca->setCreativeId($defaultCreativeId);
                        $ca->setActive(true);
                        $clickThrough = new ClickThroughUrl;
                        $clickThrough->setDefaultLandingPage(true);
                        $ca->setClickThroughUrl($clickThrough);
                        $rot->setCreativeAssignments([$ca]);
                        $matchingDefaultAd->setCreativeRotation($rot);
                    } else {
                        foreach ($crAssignments as $ca) {
                            $ca->setActive(true);
                            if (! $ca->getCreativeId()) {
                                $ca->setCreativeId($defaultCreativeId);
                            }
                        }
                    }
                }

                $this->service->ads->update($profileId, $matchingDefaultAd);
                Log::info("Default ad {$matchingDefaultAd->getId()} updated and assigned to placement {$placementId}");
            } elseif ($defaultCreativeId) {
                // If no default ad exists for this size in the campaign, create one explicitly
                $ad = new Ad;
                $ad->setName("{$width}x{$height} Default Web Ad");
                $ad->setAdvertiserId($advertiserId);
                $ad->setCampaignId($campaignId);
                $ad->setType('AD_SERVING_DEFAULT_AD');
                $ad->setActive(true);
                $ad->setArchived(false);
                $ad->setStartTime(gmdate('Y-m-d\TH:i:s.000\Z'));
                $ad->setEndTime(date('Y-m-d', strtotime('+5 years')).'T23:59:59Z');

                $size = new Size;
                $size->setWidth($width);
                $size->setHeight($height);
                $ad->setSize($size);

                $pa = new PlacementAssignment;
                $pa->setPlacementId($placementId);
                $pa->setActive(true);
                $ad->setPlacementAssignments([$pa]);

                $ca = new CreativeAssignment;
                $ca->setCreativeId($defaultCreativeId);
                $ca->setActive(true);
                $clickThrough = new ClickThroughUrl;
                $clickThrough->setDefaultLandingPage(true);
                $ca->setClickThroughUrl($clickThrough);

                $rot = new CreativeRotation;
                $rot->setCreativeAssignments([$ca]);
                $ad->setCreativeRotation($rot);

                $createdDefaultAd = $this->service->ads->insert($profileId, $ad);
                Log::info("Created new Default Ad {$createdDefaultAd->getId()} for placement {$placementId}");
            }
        } catch (\Exception $e) {
            Log::error("Error ensuring default ad for placement {$placementId}: ".$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Activate all default ads for the campaign and ensure all placements in the campaign are assigned.
     */
    private function activateDefaultAds(string $profileId, string $campaignId): void
    {
        try {
            $adsResponse = $this->service->ads->listAds($profileId, [
                'campaignIds' => [$campaignId],
                'type' => 'AD_SERVING_DEFAULT_AD',
            ]);

            $ads = $adsResponse->getAds() ?? [];

            // Get all placements for this campaign
            $placementsResponse = $this->service->placements->listPlacements($profileId, [
                'campaignIds' => [$campaignId],
            ]);
            $allPlacements = $placementsResponse->getPlacements() ?? [];

            foreach ($ads as $ad) {
                $modified = false;
                if (! $ad->getActive()) {
                    $ad->setActive(true);
                    $modified = true;
                }

                $adSize = $ad->getSize();
                $assignments = $ad->getPlacementAssignments() ?? [];
                $assignedPlacementIds = collect($assignments)->pluck('placementId')->map(fn ($id) => (string) $id)->toArray();

                // Find placements of matching size that are not yet assigned to this default ad
                foreach ($allPlacements as $pl) {
                    $plSize = $pl->getSize();
                    if ($adSize && $plSize && $plSize->getWidth() == $adSize->getWidth() && $plSize->getHeight() == $adSize->getHeight()) {
                        if (! in_array((string) $pl->getId(), $assignedPlacementIds)) {
                            $newPa = new PlacementAssignment;
                            $newPa->setPlacementId($pl->getId());
                            $newPa->setActive(true);
                            $assignments[] = $newPa;
                            $assignedPlacementIds[] = (string) $pl->getId();
                            $modified = true;
                        }
                    }
                }

                if ($modified) {
                    $ad->setPlacementAssignments($assignments);
                    $this->service->ads->update($profileId, $ad);
                    Log::info("Activated/Updated default ad {$ad->getId()} with ".count($assignments).' placement assignments');
                }
            }
        } catch (\Exception $e) {
            Log::warning("Could not activate default ads for campaign {$campaignId}: ".$e->getMessage());
        }
    }

    /**
     * Find or create a Campaign in CM360
     */
    private function findOrCreateCampaign(string $profileId, string $advertiserId, string $campaignName): Campaign
    {
        // 1. Search if campaign with exact name already exists
        try {
            $existing = $this->findCampaignByName($profileId, $advertiserId, $campaignName);
            if ($existing) {
                Log::info('Reusing existing CM360 Campaign', ['id' => $existing->getId(), 'name' => $campaignName]);

                return $existing;
            }
        } catch (\Exception $e) {
            Log::warning('Could not search for existing campaign: '.$e->getMessage());
        }

        // 2. Try creating the campaign
        try {
            return $this->createCampaign($profileId, $advertiserId, $campaignName);
        } catch (\Exception $e) {
            // If already exists (400 not unique), try to search and retrieve it
            if (str_contains($e->getMessage(), 'not unique') || str_contains($e->getMessage(), '7005')) {
                $existing = $this->findCampaignByName($profileId, $advertiserId, $campaignName);
                if ($existing) {
                    Log::info('Recovered existing CM360 Campaign after collision', ['id' => $existing->getId()]);

                    return $existing;
                }
            }
            throw $e;
        }
    }

    /**
     * Find a campaign by name for a given advertiser
     */
    private function findCampaignByName(string $profileId, string $advertiserId, string $campaignName): ?Campaign
    {
        $response = $this->service->campaigns->listCampaigns($profileId, [
            'advertiserIds' => [$advertiserId],
            'searchString' => $campaignName,
            'maxResults' => 10,
        ]);

        $campaigns = $response->getCampaigns();
        if (! empty($campaigns)) {
            foreach ($campaigns as $camp) {
                if (trim($camp->getName()) === trim($campaignName)) {
                    return $camp;
                }
            }
        }

        return null;
    }

    /**
     * Create a new Campaign in CM360
     */
    private function createCampaign(string $profileId, string $advertiserId, string $campaignName): Campaign
    {
        // 1. Create Default Landing Page with unique timestamped suffix
        $landingPage = new LandingPage;
        $landingPage->setAdvertiserId($advertiserId);
        $landingPage->setName("Default URL - {$campaignName} - ".time());
        $landingPage->setUrl('https://www.wppmedia.com/es');

        $lpResponse = $this->service->advertiserLandingPages->insert($profileId, $landingPage);

        // 2. Create Campaign
        $campaign = new Campaign;
        $campaign->setName($campaignName);
        $campaign->setAdvertiserId($advertiserId);
        $campaign->setStartDate(date('Y-m-d'));
        $campaign->setEndDate(date('Y-m-d', strtotime('+1 month')));
        $campaign->setDefaultLandingPageId($lpResponse->getId());
        $campaign->setEuPoliticalAdsDeclaration('DOES_NOT_CONTAIN_EU_POLITICAL_ADS');

        return $this->service->campaigns->insert($profileId, $campaign);
    }

    /**
     * Create a Placement in CM360
     */
    private function createPlacement(
        string $profileId,
        string $advertiserId,
        ?string $campaignId,
        string $siteId,
        string $name,
        int $width,
        int $height
    ): Placement {
        $resolvedSiteId = $this->resolveSiteForAdvertiser($profileId, $advertiserId, $siteId);

        $placement = new Placement;
        $placement->setName("PL_{$name}");
        $placement->setAdvertiserId($advertiserId);
        if ($campaignId) {
            $placement->setCampaignId($campaignId);
        }
        $placement->setSiteId($resolvedSiteId);
        $placement->setCompatibility('DISPLAY');
        $placement->setPaymentSource('PLACEMENT_AGENCY_PAID');
        $placement->setTagFormats(['PLACEMENT_TAG_STANDARD', 'PLACEMENT_TAG_IFRAME_JAVASCRIPT']);

        $size = new Size;
        $size->setWidth($width);
        $size->setHeight($height);
        $placement->setSize($size);

        $pricingSchedule = new PricingSchedule;
        $pricingSchedule->setPricingType('PRICING_TYPE_CPM');
        $pricingSchedule->setStartDate(date('Y-m-d'));
        $pricingSchedule->setEndDate(date('Y-m-d', strtotime('+1 month')));
        $placement->setPricingSchedule($pricingSchedule);

        return $this->service->placements->insert($profileId, $placement);
    }

    /**
     * Create an Ad in CM360 (links Creative to Placement)
     */
    private function createAd(
        string $profileId,
        string $advertiserId,
        ?string $campaignId,
        string $creativeId,
        string $placementId,
        string $name,
        int $width,
        int $height,
        string $adType = 'AD_SERVING_STANDARD_AD'
    ): Ad {

        $ad = new Ad;
        $ad->setName("AD_{$name}");
        $ad->setAdvertiserId($advertiserId);
        if ($campaignId) {
            $ad->setCampaignId($campaignId);
        }
        $ad->setType($adType);
        $ad->setActive(true);
        $ad->setStartTime(gmdate('Y-m-d\TH:i:s.000\Z'));
        $ad->setEndTime(date('Y-m-d', strtotime('+1 month')).'T23:59:59Z');

        // Delivery Schedule (Priority) - Not applicable for default ads
        if ($adType !== 'AD_SERVING_DEFAULT_AD') {
            $deliverySchedule = new DeliverySchedule;
            $deliverySchedule->setPriority('AD_PRIORITY_01');
            $deliverySchedule->setImpressionRatio('1');
            $ad->setDeliverySchedule($deliverySchedule);
        }

        // Size
        $size = new Size;
        $size->setWidth($width);
        $size->setHeight($height);
        $ad->setSize($size);

        // Placement Assignment
        $placementAssignment = new PlacementAssignment;
        $placementAssignment->setPlacementId($placementId);
        $placementAssignment->setActive(true);
        $ad->setPlacementAssignments([$placementAssignment]);

        // Creative Rotation
        $creativeAssignment = new CreativeAssignment;
        $creativeAssignment->setCreativeId($creativeId);
        $creativeAssignment->setActive(true);

        $clickThroughUrl = new ClickThroughUrl;
        $clickThroughUrl->setDefaultLandingPage(true);
        $creativeAssignment->setClickThroughUrl($clickThroughUrl);

        $creativeRotation = new CreativeRotation;
        $creativeRotation->setCreativeAssignments([$creativeAssignment]);
        $creativeRotation->setType('CREATIVE_ROTATION_TYPE_RANDOM');
        $creativeRotation->setWeightCalculationStrategy('WEIGHT_STRATEGY_EQUAL');
        $ad->setCreativeRotation($creativeRotation);

        return $this->service->ads->insert($profileId, $ad);
    }

    /**
     * Generate Ad Tags for a placement
     */
    private function generateAdTags(string $profileId, string $campaignId, string $placementId): array
    {
        $response = $this->service->placements->generatetags($profileId, [
            'campaignId' => $campaignId,
            'placementIds' => [$placementId],
            'tagFormats' => ['PLACEMENT_TAG_IFRAME_JAVASCRIPT'],
        ]);

        $tags = [];
        $placementTags = $response->getPlacementTags();
        if (! empty($placementTags)) {
            foreach ($placementTags as $pt) {
                $tagDatas = $pt->getTagDatas();
                if (! empty($tagDatas)) {
                    foreach ($tagDatas as $td) {
                        $tags[] = [
                            'format' => $td->getFormat(),
                            'impression_tag' => $td->getImpressionTag(),
                            'click_tag' => $td->getClickTag(),
                        ];
                    }
                }
            }
        }

        return $tags;
    }

    /**
     * Generate placement tags for multiple placements (public method for Excel export).
     *
     * @param  string  $profileId  CM360 profile ID
     * @param  string  $campaignId  CM360 campaign ID
     * @param  array  $placementIds  Array of placement IDs
     * @return array Array of tag data per placement
     */
    public function generatePlacementTags(string $profileId, string $campaignId, array $placementIds): array
    {
        $response = $this->service->placements->generatetags($profileId, [
            'campaignId' => $campaignId,
            'placementIds' => $placementIds,
            'tagFormats' => ['PLACEMENT_TAG_IFRAME_JAVASCRIPT'],
        ]);

        $allTags = [];
        $placementTags = $response->getPlacementTags();
        if (! empty($placementTags)) {
            foreach ($placementTags as $pt) {
                $placementId = $pt->getPlacementId();
                $tagDatas = $pt->getTagDatas();
                if (! empty($tagDatas)) {
                    foreach ($tagDatas as $td) {
                        $allTags[] = [
                            'placement_id' => $placementId,
                            'format' => $td->getFormat(),
                            'impression_tag' => $td->getImpressionTag(),
                            'click_tag' => $td->getClickTag(),
                        ];
                    }
                }
            }
        }

        return $allTags;
    }

    /**
     * Create a zip file containing the HTML as index.html.
     *
     * @param  string  $html  The HTML content
     * @param  string  $baseName  Base name for the temp file
     * @return string Path to the created zip file
     */
    private function createZipFromHtml(string $html, string $baseName): string
    {
        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = "{$tempDir}/{$baseName}.zip";

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Could not create zip file: {$zipPath}");
        }

        $zip->addFromString('index.html', $html);
        $zip->close();

        return $zipPath;
    }

    /**
     * Upload a creative asset (zip file) to CM360 using the SDK.
     */
    private function uploadCreativeAsset(
        string $profileId,
        string $advertiserId,
        string $filePath,
        string $fileName,
        string $type = 'HTML',
        string $mimeType = 'application/zip'
    ): CreativeAssetMetadata {

        $metadata = new CreativeAssetMetadata;

        $assetId = new CreativeAssetId;
        $assetId->setName($fileName);
        $assetId->setType($type);

        $metadata->setAssetIdentifier($assetId);

        // If it's an image, determine proper mimetype
        if ($type === 'IMAGE' || $type === 'HTML_IMAGE') {
            $mimeType = mime_content_type($filePath) ?: 'image/jpeg';
        }

        return $this->service->creativeAssets->insert($profileId, $advertiserId, $metadata, [
            'data' => file_get_contents($filePath),
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
        ]);
    }

    /**
     * Create a creative resource in CM360 using the SDK.
     */
    private function createCreative(
        string $profileId,
        string $advertiserId,
        string $name,
        string $assetName,
        int $width,
        int $height,
        string $creativeType = 'DISPLAY'
    ): Creative {

        $creative = new Creative;
        $creative->setAdvertiserId($advertiserId);
        $creative->setName($name);
        // CM360 API v5 uses 'DISPLAY' for both HTML and image creatives
        $creative->setType('DISPLAY');
        $creative->setActive(true);

        // Determine asset type: HTML_IMAGE for backup images, HTML for HTML creatives
        $isImage = ($creativeType === 'IMAGE' || $creativeType === 'HTML_IMAGE');

        $asset = new CreativeAsset;

        $assetId = new CreativeAssetId;
        $assetId->setName($assetName);
        $assetId->setType($isImage ? 'HTML_IMAGE' : 'HTML');

        $asset->setAssetIdentifier($assetId);
        $asset->setRole('PRIMARY');

        $creative->setCreativeAssets([$asset]);

        $size = new Size;
        $size->setWidth($width);
        $size->setHeight($height);
        $creative->setSize($size);

        // Only add clickTags for HTML creatives, NOT for image creatives
        // CM360 API error 8221: "You cannot specify click tags for a Display creative with a primary image."
        if (! $isImage) {
            $clickTag = new ClickTag;
            $clickTag->setName('clickTag');
            $clickTag->setEventName('clickTag');

            $clickThrough = new CreativeClickThroughUrl;
            $clickThrough->setCustomClickThroughUrl('https://www.wppmedia.com/es');
            $clickTag->setClickThroughUrl($clickThrough);

            $creative->setClickTags([$clickTag]);
        }

        return $this->service->creatives->insert($profileId, $creative);
    }

    /**
     * Get all user profiles associated with the authenticated user
     *
     * @return array CM360 API response with user profiles
     */
    public function getUserProfiles(): array
    {
        $profilesResponse = $this->service->userProfiles->listUserProfiles();

        // Convert to array format to match previous output structure
        return json_decode(json_encode($profilesResponse), true);
    }

    /**
     * Get all campaigns for a profile
     *
     * @param  string  $profileId  User profile ID
     * @return array CM360 API response with campaigns
     */
    public function getCampaigns(string $profileId): array
    {
        $campaignsResponse = $this->service->campaigns->listCampaigns($profileId);

        // Convert to array format to match previous output structure
        return json_decode(json_encode($campaignsResponse), true);
    }

    /**
     * Get all advertisers for a profile
     *
     * @param  string  $profileId  User profile ID
     * @return array CM360 API response with advertisers
     */
    public function getAdvertisers(string $profileId): array
    {
        $advertisersResponse = $this->service->advertisers->listAdvertisers($profileId);

        return json_decode(json_encode($advertisersResponse), true);
    }

    /**
     * Get all sites for a profile
     *
     * @param  string  $profileId  User profile ID
     * @return array CM360 API response with sites
     */
    public function getSites(string $profileId): array
    {
        $sitesResponse = $this->service->sites->listSites($profileId);

        return json_decode(json_encode($sitesResponse), true);
    }

    /**
     * Update an existing creative's HTML asset in CM360.
     *
     * @return Creative
     */
    public function updateCreativeHtml(string $profileId, string $advertiserId, string $creativeId, string $newHtml, string $creativeName)
    {
        // 1. Create a ZIP with the new HTML
        $zipPath = $this->createZipFromHtml($newHtml, $creativeName);

        // 2. Upload the ZIP as a new asset
        $assetResponse = $this->uploadCreativeAsset(
            $profileId,
            $advertiserId,
            $zipPath,
            "{$creativeName}_".time().'.zip'
        );

        $newAssetIdentifier = $assetResponse->getAssetIdentifier();

        // 3. Get the existing creative
        $creative = $this->service->creatives->get($profileId, $creativeId);

        // 4. Update the primary HTML asset in the creative
        $assets = $creative->getCreativeAssets();
        $updatedAssets = [];
        $assetReplaced = false;

        foreach ($assets as $asset) {
            // Find the primary HTML asset
            if ($asset->getRole() === 'PRIMARY' && $asset->getAssetIdentifier()->getType() === 'HTML') {
                $newAsset = new CreativeAsset;
                $newAsset->setAssetIdentifier($newAssetIdentifier);
                $newAsset->setRole('PRIMARY');
                $updatedAssets[] = $newAsset;
                $assetReplaced = true;
            } else {
                $updatedAssets[] = $asset;
            }
        }

        if (! $assetReplaced) {
            // If we couldn't find a primary HTML asset, just append it
            $newAsset = new CreativeAsset;
            $newAsset->setAssetIdentifier($newAssetIdentifier);
            $newAsset->setRole('PRIMARY');
            $updatedAssets[] = $newAsset;
        }

        $creative->setCreativeAssets($updatedAssets);

        // 5. Update the creative via API
        return $this->service->creatives->patch($profileId, $creativeId, $creative);
    }

    /**
     * Activate or Deactivate all Ads and Campaign in CM360.
     */
    public function toggleCampaignStatus(string $profileId, string $campaignId, bool $active): array
    {
        $updatedAds = 0;
        $errors = [];

        // 1. Update Ads status
        try {
            $adsResponse = $this->service->ads->listAds($profileId, [
                'campaignIds' => [$campaignId],
            ]);

            $ads = $adsResponse->getAds() ?? [];
            foreach ($ads as $ad) {
                try {
                    $ad->setActive($active);

                    $assignments = $ad->getPlacementAssignments();
                    if (! empty($assignments)) {
                        foreach ($assignments as $pa) {
                            $pa->setActive($active);
                        }
                        $ad->setPlacementAssignments($assignments);
                    }

                    $this->service->ads->update($profileId, $ad);
                    $updatedAds++;
                } catch (\Exception $ae) {
                    $errors[] = "Error al actualizar Ad {$ad->getId()}: ".$ae->getMessage();
                    Log::warning("Could not toggle ad {$ad->getId()} in CM360: ".$ae->getMessage());
                }
            }
        } catch (\Exception $e) {
            $errors[] = 'Error al listar Ads de la campaña: '.$e->getMessage();
            Log::error("Could not list ads for campaign {$campaignId}: ".$e->getMessage());
        }

        // 2. Update Campaign archived status
        try {
            $campaign = $this->service->campaigns->get($profileId, $campaignId);
            if ($campaign) {
                $campaign->setArchived(! $active);
                $this->service->campaigns->update($profileId, $campaign);
            }
        } catch (\Exception $ce) {
            Log::warning("Could not update campaign {$campaignId} archived status in CM360: ".$ce->getMessage());
        }

        return [
            'success' => count($errors) === 0,
            'active' => $active,
            'updated_ads' => $updatedAds,
            'errors' => $errors,
        ];
    }

    /**
     * Resolve the most appropriate Site ID for an advertiser, respecting its subaccount permissions.
     */
    public function resolveSiteForAdvertiser(string $profileId, string $advertiserId, ?string $preferredSiteId): string
    {
        try {
            $advertiser = $this->service->advertisers->get($profileId, $advertiserId);
            $subaccountId = $advertiser->getSubaccountId();

            if (! empty($subaccountId)) {
                // Get all sites available in this subaccount
                $subSitesResponse = $this->service->sites->listSites($profileId, [
                    'subaccountId' => (string) $subaccountId,
                ]);
                $subSites = $subSitesResponse->getSites() ?? [];

                if (! empty($subSites)) {
                    // If preferredSiteId belongs to this subaccount, use it
                    foreach ($subSites as $s) {
                        if ((string) $s->getId() === (string) $preferredSiteId) {
                            return (string) $preferredSiteId;
                        }
                    }

                    // Otherwise, find an Xaxis or best-matching site in this subaccount
                    $bestSite = collect($subSites)->first(function ($s) {
                        return stripos($s->getName(), 'xaxis') !== false;
                    }) ?? $subSites[0];

                    Log::info("Resolved subaccount site for advertiser {$advertiserId} (subaccount {$subaccountId})", [
                        'requested_site' => $preferredSiteId,
                        'resolved_site' => $bestSite->getId(),
                        'site_name' => $bestSite->getName(),
                    ]);

                    return (string) $bestSite->getId();
                }
            }
        } catch (\Exception $e) {
            Log::warning("Could not resolve subaccount site for advertiser {$advertiserId}: ".$e->getMessage());
        }

        return (string) ($preferredSiteId ?: '4946484');
    }
}
