<?php
declare(strict_types=1);


namespace RMS\ResourceCollector\TagRules;

use Illuminate\Database\Capsule\Manager as DB;
use Psr\Log\LoggerInterface;
use RMS\ResourceCollector\Model\Tag;
use RMS\ResourceCollector\Model\TagRule;

/**
 * Class TagUpdater
 * @package RMS\ResourceCollector\TagRules\CronHandlers
 *
 * Выполняется метод process(), по расписанию
 * При каждом выполнении полностью заново пересоздает структуру связки unit - tag
 * Все правила применяются в порядке приоритета от низшего до наивысшего
 *
 */
class TagUpdater
{
    const TEMP_TABLE = "unit2tag_temporary";
    const TEMP_TABLE_2 = "unit2tag_temporary_2";

    private $logger;
    private $raven;

    public function __construct(LoggerInterface $logger, \Raven_Client $raven)
    {
        $this->logger = $logger;
        $this->raven = $raven;
    }

    public function process(): void
    {
        try {
            /* @var TagRule[] $rules */
            $rulesIterator = TagRule::all();
            $rules = [];
            foreach ($rulesIterator as $rule) {
                $rules[] = $rule;
            }

            usort(
                $rules,
                function ($a, $b) {
                    /* @var TagRule $a */
                    /* @var TagRule $b */
                    return $a->getPriority() - $b->getPriority();
                }
            );

            DB::statement("DROP TABLE IF EXISTS " . static::TEMP_TABLE);
            DB::statement("CREATE TABLE " . static::TEMP_TABLE . " LIKE " . Unit2TagLinker::TABLE);
            foreach ($rules as $rule) {
                $ruleStrategy = RuleStrategy::get($rule->getType(), $rule->getBody());
                $units = $ruleStrategy->getUnits();
                $this->logger->info(
                    "Applying " . $rule->getName() . " (priority ". $rule->getPriority() . ") on " . count($units) . " units"
                );
                /* @var Tag $tag */
                $tag = (new Rule2TagLinker())->getRuleTag($rule);
                $linker = new Unit2TagLinker();
                $linker->setTable(static::TEMP_TABLE);
                $skippedUnits = $linker->linkUnits($units, $rule, $tag);
                $this->logger->info(
                    "Finished apply " . $rule->getName() . ". " . count($skippedUnits) . " skipped: \n"
                    . implode("\n", array_map(function ($a){return implode(" ", $a);}, $skippedUnits))
                );
            }
            $this->replaceTables();
        } catch (\Throwable $e) {
            $this->raven->captureException($e);
            $this->logger->error("Exception caught:\n" . $e);
            return;
        }
    }

    public function replaceTables()
    {
        DB::statement(
            "RENAME TABLE "
            . Unit2TagLinker::TABLE . " TO " . static::TEMP_TABLE_2 . ", "
            . static::TEMP_TABLE . " TO " . Unit2TagLinker::TABLE . ";"
        );
        DB::statement("DROP TABLE " . static::TEMP_TABLE_2 . ";");
    }
}