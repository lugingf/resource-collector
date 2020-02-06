<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\Controller;

use RMS\ResourceCollector\TagRules\Rule2TagLinker;
use RMS\ResourceCollector\Model\Tag;
use RMS\ResourceCollector\Model\TagRule;

class RuleLoadController
{
    /**
     * @var string
     */
    protected $ruleName;

    public function __construct(array $ruleData)
    {
        $this->ruleName = trim($ruleData['ruleName']);
    }

    public function process(): array
    {
        /* @var TagRule $rule */
        $rule = TagRule::where('name', "=", $this->ruleName);
        // @todo потенциально не is_null
        if (is_null($rule)) {
            throw new \Exception("Rule with specific name is not saved yet");
        }
        /* @var Tag $tag */
        $tag = (new Rule2TagLinker())->getRuleTag($rule);

        return [
            'ruleBody' => $rule->getBody(),
            'ruleComment' => $rule->getComment(),
            'rulePriority' => $rule->getPriority(),
            'tagName' => $tag->getName(),
            'tagValue' => $tag->getValue()
        ];
    }
}