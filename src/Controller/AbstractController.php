<?php
declare(strict_types=1);


namespace RMS\ResourceCollector\Controller;

use RMS\ResourceCollector\TagRules\RuleStrategy;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class AbstractController
{
    abstract function customProcess(Request $request, Response $response, array $args): Response;
    abstract function getRequiredParameters(): array;

    /* @throws \Exception */
    public function process(Request $request, Response $response, array $args): Response
    {
        $this->checkRequiredParameters($request);

        $response = $this->customProcess($request, $response, $args);
        return $response->withHeader('Content-Type', 'application/json;charset=utf-8');
    }

    protected function getParameters(Request $request): array
    {
        return $request->getParsedBody() ?? [];
    }

    /* @throws \Exception */
    protected function checkRequiredParameters(Request $request): void
    {
        $parameters = $this->getParameters($request);
        foreach ($this->getRequiredParameters() as $parameter) {
            if (!isset($parameters[$parameter])) {
                throw new \Exception("Missing required parameter $parameter");
            }
        }
    }

    /* @throws \Exception */
    protected function getUnitNameList(string $ruleType, string $ruleBody): array
    {
        $rule = RuleStrategy::get($ruleType, $ruleBody);
        $units = $rule->getUnits();
        return $units;
    }
}