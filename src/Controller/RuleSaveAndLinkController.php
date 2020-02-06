<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\Controller;

use Psr\Log\LoggerInterface;
use RMS\ResourceCollector\TagRules\Host2TagLinker;
use RMS\ResourceCollector\TagRules\Rule2TagLinker;
use RMS\ResourceCollector\Model\Tag;
use RMS\ResourceCollector\Model\TagRule;

class RuleSaveAndLinkController extends TagRuleCheckController
{
    private $ruleName;
    private $rulePriority;
    private $ruleComment;
    private $tagName;
    private $tagValue;

    public function __construct(LoggerInterface $logger,array $ruleData)
    {
        parent::__construct($logger, $ruleData);
        $this->ruleName = trim($ruleData['ruleName']);
        $this->rulePriority = trim($ruleData['rulePriority']);
        $this->ruleComment = trim($ruleData['ruleComment']);
        $this->tagName = trim($ruleData['tagName']);
        $this->tagValue = trim($ruleData['tagValue']);
    }

    public function process(): array
    {
        $tagParameters = [
            Tag::FIELD_NAME => $this->tagName,
            Tag::FIELD_VALUE => $this->tagValue,
        ];
        $tag = Tag::firstOrCreate($tagParameters);

        $ruleParams = [
            TagRule::FIELD_NAME => $this->ruleName,
            TagRule::FIELD_TYPE => $this->ruleType,
            TagRule::FIELD_BODY => $this->ruleBody,
            TagRule::FIELD_PRIORITY => $this->rulePriority,
            TagRule::FIELD_COMMENT => $this->ruleComment,
        ];
        $tagRule = TagRule::firstOrCreate($ruleParams);

        (new Rule2TagLinker())->linkExclusively($tagRule, $tag);
        $instancesList = $this->getHostNameList();
        $skippedInstances = (new Host2TagLinker())->linkHosts($instancesList, $tagRule, $tag);

        return $skippedInstances;
    }
}