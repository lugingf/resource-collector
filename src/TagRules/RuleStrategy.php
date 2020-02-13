<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\TagRules;

use RMS\ResourceCollector\TagRules\Strategies\AbstractStrategy;
use RMS\ResourceCollector\TagRules\Strategies\OriginStrategy;
use RMS\ResourceCollector\TagRules\Strategies\RegexStrategy;

class RuleStrategy
{
    /**
     * @param string $type
     * @param string $ruleBody
     * @return AbstractStrategy
     * @throws \Exception
     */
    public static function get(string $type, string $ruleBody): AbstractStrategy
    {
        switch ($type) {
            case 'regex':
                return new RegexStrategy($ruleBody);
            case 'origin':
                return new OriginStrategy($ruleBody);
            default:
                throw new \Exception('Unknown rule type was provided');
        }
    }
}