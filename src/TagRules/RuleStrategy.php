<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\TagRules;

use RMS\ResourceCollector\TagRules\Strategy\AbstractStrategy;
use RMS\ResourceCollector\TagRules\Strategy\Origin;
use RMS\ResourceCollector\TagRules\Strategy\Regex;

class RuleStrategy
{
    /**
     * @param string $type
     * @param string $ruleBody
     * @return AbstractStrategy
     * @throws \Exception
     */
    public static function getStrategy(string $type, string $ruleBody): AbstractStrategy
    {
        switch ($type) {
            case 'regex':
                return new Regex($ruleBody);
            case 'origin':
                return new Origin($ruleBody);
            default:
                throw new \Exception('Unknown rule type was provided');
        }
    }
}