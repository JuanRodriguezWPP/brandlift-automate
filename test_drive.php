<?php

use Google\Client;
use Google\Service\Drive;

require __DIR__.'/vendor/autoload.php';
$client = new Client;
$client->setAuthConfig(__DIR__.'/storage/app/api_services_automate_brandlift.json');
$client->addScope(Drive::DRIVE);
$service = new Drive($client);
$files = $service->files->listFiles(['q' => "'me' in owners"]);
echo 'Total files owned by bot: '.count($files->getFiles())."\n";
foreach ($files->getFiles() as $file) {
    echo $file->getName().' ('.$file->getId().")\n";
}
