<?php

use RMS\ResourceCollector\Application;

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../src/lib.php";

try {
    loadEnvironment(true);
    $app = new Application();
    $container = $app->getContainer();

    // TODO: other bootstraps
} catch (\Throwable $e) {
    criticalException($e);
    exit(1);
}
