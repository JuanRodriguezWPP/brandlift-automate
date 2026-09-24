<?php
require __DIR__.'/vendor/autoload.php';
$client = new Google\Client();
$client->setAuthConfig(__DIR__.'/storage/app/api_services_automate_brandlift.json');
$client->addScope(Google\Service\Drive::DRIVE);
$service = new Google\Service\Drive($client);
$about = $service->about->get(['fields' => 'storageQuota, user']);
print_r($about->getStorageQuota());
