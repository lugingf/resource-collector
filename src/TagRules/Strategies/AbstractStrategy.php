<?php
declare(strict_types=1);


namespace RMS\ResourceCollector\TagRules\Strategies;

abstract class AbstractStrategy
{
    /**
     * @var string
     */
    protected $ruleBody;

    public function __construct(string $ruleBody)
    {
        $this->ruleBody = $ruleBody;
    }

    abstract public function getUnits(): array;
}