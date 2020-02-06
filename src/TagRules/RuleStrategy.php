<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\TagRules;

use RMS\ResourceCollector\TagRules\Strategies\RegexStrategy;

class RuleStrategy
{
    /**
     * @param string $type
     * @return RegexStrategy
     * @throws \Exception
     */
    public static function getStrategy(string $type, string $ruleBody)
    {
        switch ($type) {
            case 'regex':
                return new RegexStrategy($ruleBody);
            default:
                throw new \Exception('Unknown rule type was provided');
        }
    }
}