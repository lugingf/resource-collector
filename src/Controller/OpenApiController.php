<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RM\OpenApi\Specification;

class OpenApiController
{
    private $specification;

    public function __construct(Specification $specification)
    {
        $this->specification = $specification;
    }


    public function getOpenApiJson(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $jsonResponse = $response->withHeader('Content-Type', 'application/json;charset=utf-8');
        $jsonResponse->getBody()->write($this->specification->getSpecificationContent());
        return $jsonResponse;
    }
}
