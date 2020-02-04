<?php
declare(strict_types=1);

use RMS\ResourceCollector\Application;
use RMS\ResourceCollector\ResourceCollector;

require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/../../src/lib.php";

try {
    loadEnvironment(false);
    $app = new Application();
    $container = $app->getContainer();
    $collector = $container->get(ResourceCollector::class);
    $target = getenv('UPDATE_TARGET');
    if ($target == false) {
        $target = null;
    }
    if (!is_array($target) && !is_null($target)){
        $target = [$target];
    }
    $collector->collectResources($target);
} catch (\Throwable $e) {
    criticalException($e);
    $container = $app->getContainer();
    $container->get(\Raven_Client::class)->captureException($e);
    exit(1);
}