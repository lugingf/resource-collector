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
 * При каждом выполнении полностью заново пересоздает структуру связки host - tag
 * Все правила применяются в порядке приоритета от низшего до наивысшего
 *
 */
class TagUpdater
{
    const TEMP_TABLE = "host2tag_temporary";
    const TEMP_TABLE_2 = "host2tag_temporary_2";

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
            DB::statement("CREATE TABLE " . static::TEMP_TABLE . " LIKE " . Host2TagLinker::TABLE);
            foreach ($rules as $rule) {
                $ruleStrategy = RuleStrategy::get($rule->getType(), $rule->getBody());
                $hosts = $ruleStrategy->getHosts();
                $this->logger->info(
                    "Applying " . $rule->getName() . " (priority ". $rule->getPriority() . ") on " . count($hosts) . " hosts"
                );
                /* @var Tag $tag */
                $tag = (new Rule2TagLinker())->getRuleTag($rule);
                $linker = new Host2TagLinker();
                $linker->setTable(static::TEMP_TABLE);
                $skippedHosts = $linker->linkHosts($hosts, $rule, $tag);
                $this->logger->info(
                    "Finished apply " . $rule->getName() . ". " . count($skippedHosts) . " skipped: \n"
                    . implode("\n", array_map(function ($a){return implode(" ", $a);}, $skippedHosts))
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
            . Host2TagLinker::TABLE . " TO " . static::TEMP_TABLE_2 . ", "
            . static::TEMP_TABLE . " TO " . Host2TagLinker::TABLE . ";"
        );
        DB::statement("DROP TABLE " . static::TEMP_TABLE_2 . ";");
    }
}