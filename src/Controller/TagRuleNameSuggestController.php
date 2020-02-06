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
        $tags = TagRule::getRuleNameListByNamePart($params[self::PARAM_RULE_NAME_PART]);
        /* @var Tag $tag*/
        foreach ($tags as $tag) {
            $result['ruleNames'][] = ['name' => $tag];
        }
        return $responce->withJson($result);
    }

    function getRequiredParameters(): array
    {
        return [self::PARAM_RULE_NAME_PART];
    }
}