<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(\App\Services\CampaignManagerService::class);
$reflection = new ReflectionClass($service);
$property = $reflection->getProperty('service');
$property->setAccessible(true);
$client = $property->getValue($service);

$profiles = $client->userProfiles->listUserProfiles();
$profileId = $profiles->getItems()[0]->getProfileId();

$campaignId = '36815569'; // The new campaign from the screenshot

$ads = $client->ads->listAds($profileId, [
    'campaignIds' => [$campaignId],
    'type' => 'AD_SERVING_DEFAULT_AD'
]);

echo "Found " . count($ads->getAds()) . " default ads in campaign.\n";

foreach ($ads->getAds() as $ad) {
    echo "Ad ID: " . $ad->getId() . " Name: " . $ad->getName() . "\n";
    echo "  Is Active: " . ($ad->getActive() ? 'Yes' : 'No') . "\n";
}
echo "Done\n";
