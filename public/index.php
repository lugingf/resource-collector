<?php

use RMS\ResourceCollector\Application;

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../src/lib.php";

try {
    loadEnvironment();
    $app = new Application();
    $app->configure();
    $app->run();
} catch (\Throwable $e) {
    // В этот блок попадают только исключения, которые были выброшены в процессе инициализации приложения
    // Остальное (из $app->run) попадает в обработчики, которые указаны в конфиге DI

    criticalException($e);
    http_response_code(500);

    $displayErrorDetails = (bool)getenv('DEBUG');
    if ($displayErrorDetails) {
        header('Content-Type: application/json');
        print json_encode(
            ['error' => ['message' => "{$e->getMessage()}", 'code' => $e->getCode(), 'description' => "{$e}"]]
        );
    }
}
