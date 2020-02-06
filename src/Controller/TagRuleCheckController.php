<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\Controller;

use Psr\Log\LoggerInterface;
use RMS\ResourceCollector\TagRules\Host2TagLinker;
use RMS\ResourceCollector\Model\Tag;
use RMS\ResourceCollector\Model\TagRule;
use Slim\Http\Request;
use Slim\Http\Response;

class TagRuleCheckController extends AbstractController
{
    private const PARAM_BODY = "ruleBody";
    private const PARAM_TYPE = "ruleType";

    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function customProcess(Request $request, Response $responce, array $args): Response
    {
        $ruleData = $this->getParameters($request);
        return $responce->withJson($this->getInstancesList($ruleData));
    }

    protected function getInstancesList(array $ruleData): array
    {
        $hosts = $this->getHostNameList($ruleData['ruleType'], $ruleData['ruleBody']);
        $result = [];
        foreach ($hosts as $hostName) {
            $tags = $this->getInstanceTags($hostName);
            $result[] = [
                'instanceName' => $hostName,
                'tags' => $tags
            ];
        }
        array_multisort(array_column($result, 'instanceName'), SORT_ASC, SORT_NATURAL, $result);
        return $result;
    }

    private function getInstanceTags(string $instanceName): array
    {
        $result = [];
        $tagLinks = (new Host2TagLinker())->getHostTags($instanceName);
        foreach ($tagLinks as $link) {
            $tagId = $link->tag_id;
            /* @var Tag $tag */
            $tag = Tag::where('id', "=", $tagId)->first();
            /* @var TagRule $rule */
            // @todo при реализации удалений правил - учесть возврат null
            $rule = TagRule::where('id', "=", $link->rule_id)->first();
            $result[] = [
                'name' => $tag->getName(),
                'value' => $tag->getValue(),
                'ruleName' => $rule->getName(),
                'ruleComment' => $rule->getComment(),
                'rulePriority' => $rule->getPriority()
            ];
        }
        return $result;
    }

    function getRequiredParameters(): array
    {
        return [self::PARAM_TYPE, self::PARAM_BODY];
    }
}