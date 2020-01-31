<?php
declare(strict_types=1);

use Tutu\MonologExtensions\LogstashJsonFormatter;

function M()
{
    throw new \RuntimeException("No more Dispatcher");
}

function loadEnvironment(bool $report = false)
{
    putenv('START_TIME=' . microtime(true));

    $dotenv = \Dotenv\Dotenv::create(__DIR__ . '/..');
    $dotenv->safeLoad();

    if ($report) {
        printf("\nEnvironment variables\n");
        foreach ($dotenv->getEnvironmentVariableNames() as $name) {
            printf("\t%s: %s\n", $name, getenv($name));
        }
        printf("\n");
    }
}

function criticalException(\Throwable $e)
{
    $formatter = new LogstashJsonFormatter();
    $logRecord = $formatter->format(['message' => "$e", 'channel' => 'ResourceCollector', 'level_name' => 'error']);
    error_log("${logRecord}\n", 3, 'php://stdout');
}
