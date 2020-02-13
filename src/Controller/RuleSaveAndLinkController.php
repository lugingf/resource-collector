<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\Controller;

use RMS\ResourceCollector\TagRules\Unit2TagLinker;
use RMS\ResourceCollector\TagRules\Rule2TagLinker;
use RMS\ResourceCollector\Model\Tag;
use RMS\ResourceCollector\Model\TagRule;
use Slim\Http\Request;
use Slim\Http\Response;

class RuleSaveAndLinkController extends AbstractController
{
    private const PARAM_BODY = "ruleBody";
    private const PARAM_TYPE = "ruleType";
    private const PARAM_NAME = "ruleName";
    private const PARAM_PRIORITY = "rulePriority";
    private const PARAM_COMMENT = "ruleComment";
    private const PARAM_TAG_NAME = "tagName";
    private const PARAM_TAG_VALUE = "tagValue";

    public function customProcess(Request $request, Response $response, array $args): Response
    {
        $ruleData = $this->getParameters($request);

        $ruleBody = trim($ruleData[self::PARAM_BODY]);
        $ruleType = trim($ruleData[self::PARAM_TYPE]);
        $ruleName = trim($ruleData[self::PARAM_NAME]);
        $rulePriority = trim($ruleData[self::PARAM_PRIORITY]);
        $ruleComment = trim($ruleData[self::PARAM_COMMENT]);
        $tagName = trim($ruleData[self::PARAM_TAG_NAME]);
        $tagValue = trim($ruleData[self::PARAM_TAG_VALUE]);

        $tagParameters = [
            Tag::FIELD_NAME => $tagName,
            Tag::FIELD_VALUE => $tagValue,
        ];
        $tag = Tag::firstOrCreate($tagParameters);

        $ruleParams = [
            TagRule::FIELD_NAME => $ruleName,
        ];
        $tagRule = TagRule::firstOrNew($ruleParams);
        $tagRule->{TagRule::FIELD_TYPE} = $ruleType;
        $tagRule->{TagRule::FIELD_BODY} = $ruleBody;
        $tagRule->{TagRule::FIELD_PRIORITY} = $rulePriority;
        $tagRule->{TagRule::FIELD_COMMENT} = $ruleComment;
        $tagRule->save();

        // @todo в di
        (new Rule2TagLinker())->linkExclusively($tagRule, $tag);
        $instancesList = $this->getUnitNameList($ruleType, $ruleBody);
        $skippedInstances = (new Unit2TagLinker())->linkUnits($instancesList, $tagRule, $tag);

        return $response->withJson($skippedInstances);
    }

    function getRequiredParameters(): array
    {
        return [
            self::PARAM_BODY,
            self::PARAM_TYPE,
            self::PARAM_NAME,
            self::PARAM_PRIORITY,
            self::PARAM_COMMENT,
            self::PARAM_TAG_NAME,
            self::PARAM_TAG_VALUE
        ];
    }
}