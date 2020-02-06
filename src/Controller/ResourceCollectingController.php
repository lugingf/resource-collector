<?php
declare(strict_types=1);


namespace RMS\ResourceCollector\Controller;

use Psr\Log\LoggerInterface;
use RMS\ResourceCollector\ResourceCollector;
use Slim\Http\Request;
use Slim\Http\Response;

class ResourceCollectingController
{
    private $collector;
    private $logger;

    public function __construct(ResourceCollector $resourceCollector, LoggerInterface $logger)
    {
        $this->collector = $resourceCollector;
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws \Exception
     */
    public function collect(Request $request, Response $response, array $args)
    {
        $params = $request->getParsedBody() ?? [];
        $sources =  $params["sources"];
        if(!is_null($sources) && !is_array($sources)) {
            $sources = [$sources];
        }
        $this->collector->collectResources($sources);
        return $response->withJson(["result" => "ok"]);
    }
}