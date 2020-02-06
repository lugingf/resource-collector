<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\TagRules\Strategies;

use RMS\ResourceCollector\Model\Unit;

class RegexStrategy
{
    /**
     * @var string
     */
    protected $ruleBody;

    public function __construct(string $ruleBody)
    {
        $this->ruleBody = $ruleBody;
    }

    public function getHosts(): array
    {
        $result = [];
        $instances = Unit::all();

        /** @var Unit $instance */
        foreach ($instances as $instance) {
            $instanceName = $instance->getName();
            if (preg_match($this->getPreparedRuleBody(), $instanceName)) {
                $result[] = $instanceName;
            }
        }

        return $result;
    }

    private function getPreparedRuleBody()
    {
        return "/" . $this->ruleBody . "/";
    }
}