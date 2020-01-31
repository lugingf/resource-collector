<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\Middleware;

use Psr\Http\Message\UriInterface;
use RM\OpenApi\Specification;

class OpenApiUriFormatter implements
    \TutuRu\MetricsMiddleware\RequestUriFormatterInterface, \Tutu\OpenTracingMiddleware\RequestUriFormatterInterface
{
    private $specification;


    public function __construct(Specification $specification)
    {
        $this->specification = $specification;
    }

    public function format(UriInterface $uri): string
    {
        return $this->specification->getPathPatternByPath($uri->getPath()) ?? $uri->getPath();
    }
}
