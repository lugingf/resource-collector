<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SentryMiddleware implements MiddlewareInterface
{
    private $client;

    /** @var int[] */
    private $ignoredCodes = [];

    public function __construct(\Raven_Client $client, array $ignoredCodes = [])
    {
        $this->client = $client;
        $this->ignoredCodes = $ignoredCodes;
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (\Throwable $e) {
            if (!in_array($e->getCode(), $this->ignoredCodes)) {
                $this->client->captureException($e);
            }
            throw $e;
        } finally {
            $this->client->sendUnsentErrors();
        }
    }
}
