<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * https://kubernetes.io/docs/tasks/configure-pod-container/configure-liveness-readiness-probes/
 * https://docs.openshift.com/container-platform/3.11/dev_guide/application_health.html
 */
class KubernetesController
{
    /**
     * liveness probe
     */
    public function healthz(RequestInterface $request, ResponseInterface $response, array $args)
    {
        return $response->withStatus(200);
    }


    /**
     * readiness probe
     */
    public function readyz(RequestInterface $request, ResponseInterface $response, array $args)
    {
        return $response->withStatus(200);
    }
}
