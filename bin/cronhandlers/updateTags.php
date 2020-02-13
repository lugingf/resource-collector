<?php
declare(strict_types=1);

use RMS\ResourceCollector\Application;
use RMS\ResourceCollector\TagRules\TagUpdater;

require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/../../src/lib.php";

try {
    loadEnvironment(false);
    $app = new Application();
    $container = $app->getContainer();

    $updater = $container->get(TagUpdater::class);
    $updater->process();
} catch (\Throwable $e) {
    criticalException($e);
    $container->get(\Raven_Client::class)->captureException($e);
    exit(1);
}