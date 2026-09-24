<?php
require __DIR__.'/vendor/autoload.php';
$client = new Google\Client();
$client->setAuthConfig(__DIR__.'/storage/app/api_services_automate_brandlift.json');
$client->addScope(Google\Service\Drive::DRIVE);
$service = new Google\Service\Drive($client);
$files = $service->files->listFiles(['q' => "'me' in owners"]);
echo "Total files owned by bot: " . count($files->getFiles()) . "\n";
foreach($files->getFiles() as $file) {
    echo $file->getName() . " (" . $file->getId() . ")\n";
}
