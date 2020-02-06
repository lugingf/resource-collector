<?php
declare(strict_types=1);


namespace RMS\ResourceCollector\TagRules\Strategy;

use RMS\ResourceCollector\Model\Unit;

class Origin extends AbstractStrategy
{
    public function getHosts(): array
    {
        $result = [];
        $instances = Unit::all();
        $ruleInstancesList = json_decode($this->ruleBody);
        /** @var Unit $instance */
        foreach ($instances as $instance) {
            $instanceName = $instance->getName();
            if (in_array($instanceName, $ruleInstancesList)) {
                $result[] = $instanceName;
            }
        }

        return $result;
    }
}