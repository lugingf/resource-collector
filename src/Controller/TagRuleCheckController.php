<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\Controller;

use Psr\Log\LoggerInterface;
use RMS\ResourceCollector\TagRules\Host2TagLinker;
use RMS\ResourceCollector\TagRules\RuleStrategy;
use RMS\ResourceCollector\Model\Tag;
use RMS\ResourceCollector\Model\TagRule;

class TagRuleCheckController
{
    /**
     * @var string
     */
    protected $ruleBody;
    protected $ruleType;
    protected $logger;

    public function __construct(LoggerInterface $logger, array $ruleData)
    {
        $this->logger = $logger;
        $this->ruleBody = trim($ruleData['ruleBody']);
        $this->ruleType = trim($ruleData['ruleType']);
    }

    public function process(): array
    {
        $this->logger->info('hostinfo', 'test', 'text');
        return $this->getInstancesList();
    }

    protected function getInstancesList(): array
    {
        $hosts = $this->getHostNameList();
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
            $tagId = $link['tag_id'];
            /* @var Tag $tag */
            $tag = Tag::where('id', "=", $tagId);
            /* @var TagRule $rule */
            // @todo при реализации удалений правил - учесть возврат null
            $rule = TagRule::where('id', "=", $link['rule_id']);
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

    /**
     * @return array
     * @throws \Exception
     */
    protected function getHostNameList(): array
    {
        $rule = RuleStrategy::getStrategy($this->ruleType, $this->ruleBody);
        $hosts = $rule->getHosts();
        return $hosts;
    }
}