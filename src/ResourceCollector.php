<?php
declare(strict_types=1);

namespace RMS\ResourceCollector;

use Psr\Log\LoggerInterface;
use RMS\ResourceCollector\Model\Item;
use RMS\ResourceCollector\Model\Tag;
use RMS\ResourceCollector\Model\TagRule;
use RMS\ResourceCollector\Model\Unit;
use RMS\ResourceCollector\TagRules\Rule2TagLinker;

class ResourceCollector
{
    /* @var $dataProviderClient DataProviderClient */
    private $dataProviderClient;
    private $raven;
    private $logger;

    public function __construct(DataProviderClient $dataProviderClient, \Raven_Client $raven, LoggerInterface $logger)
    {
        $this->dataProviderClient = $dataProviderClient;
        $this->raven = $raven;
        $this->logger = $logger;
    }

    public function collectResources(?array $sourceNames = null)
    {
        $sources = $this->getSourcesFromConfig($sourceNames);

        if ($sources == []) {
            throw new \Exception('No resource targets registered');
        }

        try {
            foreach ($sources as $sourceName => $sourceTarget) {
                $resources = $this->dataProviderClient->getResources($sourceTarget);
                // @todo вот тут можно все отправить в тарификатор, получить инфу с ценами а потом все разбирать

                // готовим bulk insert
                // Если вставлять по одному - выполняется очень долго
                $itemsData = [];
                $unitsData = [];
                $rulesData = [];
                $itemsToClean = [];

                foreach ($resources['resources'] as $resource) {
                    $unitsData[] = [
                        'name' => $resource['name'],
                        'type' => $resource['type'],
                        'source' => $sourceName,
                        'properties' => json_encode((object)$resource['properties']),
                    ];

                    foreach ($resource['items'] as $resourceItem) {
                        $itemsData[] = [
                            'type' => $resourceItem['type'],
                            'unit_name' => $resource['name'],
                            'amount' => $resourceItem['amount'],
                            'properties' => json_encode((object)$resourceItem['properties'])
                        ];
                    }
                    $itemsToClean[] = $resource['name'];

                    foreach ($resource["tags"] as $tag) {
                        $ruleName = implode("_", [$sourceName, "origin", $tag['tag'], $tag['value']]);

                        $rulesData[$ruleName]['name'] = $ruleName;
                        $rulesData[$ruleName]['type'] = "origin";
                        $rulesData[$ruleName]['tag_name'] = $tag['tag'];
                        $rulesData[$ruleName]['tag_value'] = $tag['value'];
                        $rulesData[$ruleName]['hosts'][] = $resource['name'];
                    }
                }

                // @todo логирование. Сколько удалили, сколько добавили и тд
                Unit::where('source', '=', $sourceName)->delete();
                Unit::insertOrIgnore($unitsData);
                Item::whereIn('unit_name', $itemsToClean)->delete();
                Item::insert($itemsData);
                Item::cleanUnused();

                $this->saveTagRules($rulesData);
            }
        } catch (\Throwable $e) {
            $this->logger->error('Caugth: ' . $e);
            $this->raven->captureException($e);
        }
    }

    private function saveTagRules(array $rulesData): void
    {
        foreach ($rulesData as $ruleData){
            $tag = Tag::firstOrCreate(
                [
                    Tag::FIELD_NAME => $ruleData['tag_name'],
                    Tag::FIELD_VALUE => $ruleData['tag_value']
                ]
            );

            $tagRule = TagRule::firstOrNew(
                [
                    TagRule::FIELD_NAME => $ruleData['name'],
                    TagRule::FIELD_TYPE => $ruleData['type'],
                ]
            );
            $tagRule->body = json_encode($ruleData['hosts']);
            $tagRule->priority = 1;
            $tagRule->comment = 'info from cloud data';
            $tagRule->save();

            (new Rule2TagLinker())->linkExclusively($tagRule, $tag);
        }
    }

    private function getSourcesFromConfig(?array $sourceNames = null): array
    {
        if (is_null($sourceNames)) {
            return $this->getAllSources();
        }

        $sources = [];
        foreach ($sourceNames as $sourceName) {
             $sourceTarget = EnvConfig::getValue(EnvConfig::RESOURCE_SOURCE_PREFIX . strtoupper($sourceName));
             if ($sourceTarget) {
                 $sources[$sourceName] = $sourceTarget;
             }
        }

        return $sources;
    }

    private function getAllSources(): array
    {
        $sources = [];
        $configSources = EnvConfig::getValues(EnvConfig::RESOURCE_SOURCE_PREFIX);
        foreach ($configSources as $sourceName => $sourceTarget) {
            $sourceShortName = strtolower(str_replace(EnvConfig::RESOURCE_SOURCE_PREFIX, "", $sourceName));
            $sources[$sourceShortName] = $sourceTarget;
        }
        return $sources;
    }
}