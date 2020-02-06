<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\Controller;


use RMS\ResourceCollector\Model\Tag;
use RMS\ResourceCollector\Model\TagRule;

class TagRuleNameSuggestController
{
    private $ruleNamePart;

    public function __construct(string $ruleNamePart)
    {
        $this->ruleNamePart = $ruleNamePart;
    }

    public function process(): array
    {
        $result = [];
        $tags = TagRule::getRuleNameListByNamePart($this->ruleNamePart);
        /* @var Tag $tag*/
        foreach ($tags as $tag) {
            $result['ruleNames'][] = ['name' => $tag];
        }
        return $result;
    }
}