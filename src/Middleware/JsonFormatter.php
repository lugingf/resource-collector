<?php
declare(strict_types = 1);

namespace RMS\ResourceCollector\Middleware;

use Middlewares\ErrorFormatter\AbstractFormatter;
use Throwable;

class JsonFormatter extends AbstractFormatter
{
    protected $contentTypes = [
        'application/json',
    ];

    protected function format(Throwable $error): string
    {
        $json = [
            'type' => get_class($error),
            'description' => $error->getMessage(),
            'code' => $error->getCode(),
        ];

        return (string) json_encode(['error' => $json]);
    }
}
