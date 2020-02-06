<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\Controller;

use RMS\ResourceCollector\TagRules\Rule2TagLinker;
use RMS\ResourceCollector\Model\Tag;
use RMS\ResourceCollector\Model\TagRule;
use Slim\Http\Request;
use Slim\Http\Response;

class GetRuleController extends AbstractController
{
    public function customProcess(Request $request, Response $response, array $args): Response
    {
        $params = $this->getParameters($request);
        $ruleName = $params['ruleName'];
        if (is_null($ruleName)) {
            return $response->withStatus(400, "No ruleName specified");
        }

        /* @var TagRule $rule */
        $rule = TagRule::where('name', "=", trim($ruleName))->first();
        if (!$rule) {
            return $response->withStatus(400,"Rule with specific name is not saved yet");
        }
        /* @var Tag $tag */
        $tag = (new Rule2TagLinker())->getRuleTag($rule);

        return $response->withStatus(200)->withJson([
            'ruleBody' => $rule->getBody(),
            'ruleComment' => $rule->getComment(),
            'rulePriority' => $rule->getPriority(),
            'tagName' => $tag->getName(),
            'tagValue' => $tag->getValue()
        ]);
    }

    function getRequiredParameters(): array
    {
       return ["ruleName"];
    }
}