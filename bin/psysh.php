#!/usr/bin/env php
<?php
declare(strict_types=1);

use RMS\ResourceCollector\Application;

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../src/lib.php";

loadEnvironment(true);
$app = new Application();
$container = $app->getContainer();

eval(\Psy\sh());
