<?php

use RMS\ResourceCollector\Application;

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../src/lib.php";

try {
    loadEnvironment(true);
    $app = new Application();
    $container = $app->getContainer();

    exec('vendor/bin/phinx migrate -e production -c '. __DIR__ . '/../phinx.php', $output, $returnCode);
    if ($returnCode !== 0) {
        throw new \Exception('migration was failed: ' . implode("\n", $output));
    }
} catch (\Throwable $e) {
    criticalException($e);
    exit(1);
}
