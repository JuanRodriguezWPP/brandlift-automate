<?php

use Google\Client;
use Google\Service\Drive;

require __DIR__.'/vendor/autoload.php';
$client = new Client;
$client->setAuthConfig(__DIR__.'/storage/app/api_services_automate_brandlift.json');
$client->addScope(Drive::DRIVE);
$service = new Drive($client);
$about = $service->about->get(['fields' => 'storageQuota, user']);
print_r($about->getStorageQuota());
