<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\Controller;

use RMS\ResourceCollector\Model\Tag;
use RMS\ResourceCollector\Model\TagRule;
use Slim\Http\Request;
use Slim\Http\Response;

class TagRuleNameSuggestController extends AbstractController
{
    private const PARAM_RULE_NAME_PART = 'ruleNamePart';

    public function customProcess(Request $request, Response $responce, array $args): Response
    {
        $params = $this->getParameters($request);
        $result = [];
        $tags = $this->getRuleNameListByNamePart($params[self::PARAM_RULE_NAME_PART]);
        /* @var Tag $tag*/
        foreach ($tags as $tag) {
            $result['ruleNames'][] = ['name' => $tag];
        }
        return $responce->withJson($result);
    }


    private function getRuleNameListByNamePart(string $namePart): array
    {
        $rules = [];
        $rulesData = TagRule::where(TagRule::FIELD_NAME, "LIKE", "%$namePart%")->cursor();
        foreach ($rulesData as $rule) {
            $rules[] = $rule->{TagRule::FIELD_NAME};
        }
        return $rules;
    }

    function getRequiredParameters(): array
    {
        return [self::PARAM_RULE_NAME_PART];
    }
}