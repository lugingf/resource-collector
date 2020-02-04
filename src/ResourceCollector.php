<?php
declare(strict_types=1);

namespace RMS\ResourceCollector;

use RMS\ResourceCollector\Model\Item;
use RMS\ResourceCollector\Model\Unit;

class ResourceCollector
{
    private $dataProviderClient;
    private $raven;

    public function __construct(DataProviderClient $dataProviderClient, \Raven_Client $raven)
    {
        $this->dataProviderClient = $dataProviderClient;
        $this->raven = $raven;
    }

    public function collectResources(?array $sourceNames = null)
    {
        $sources = $this->getSourcesFromConfig($sourceNames);

        if ($sources == []) {
            throw new \Exception('No resource targets registered');
        }

        $resources = [];
        try {
            foreach ($sources as $sourceName => $sourceTarget) {
                $resources = $this->dataProviderClient->getResources($sourceTarget);
                // готовим bulk insert
                $itemData = [];
                foreach ($resources['resources'] as $resource) {
                    $unitData = (object)$resource;

                    /* @var $unit Unit */
                    $unit = Unit::firstOrNew(
                        [
                            'name' => $unitData->name,
                            'type' => $unitData->type
                        ]
                    );
                    $unit->source = $sourceName;
                    $unit->properties = json_encode($unitData->properties);
                    $unit->save();

                    foreach ($unitData->items as $sourceItem) {
                        $itemData[] = [
                            'type' => $sourceItem['type'],
                            'unit_id' => $unit->id,
                            'amount' => $sourceItem['amount'],
                            'properties' => json_encode($sourceItem['properties'])

                        ];
                    }
                    // Items не уникальные у нас, поэтому просто грохнем старые и заведем новые
                    Item::where('unit_id', '=', $unit->id)->delete();
                    $tags = $unitData->tags;
                }
                Item::insert($itemData);
                // @fixme
                var_dump($sourceName);
            }
        } catch (\Throwable $e) {
            // @fixme
            var_dump('caught ' . $e);
            $this->raven->captureException($e);
        }
    }

    private function getSourcesFromConfig(?array $sourceNames = null): array
    {
        if (is_null($sourceNames)) {
            return $this->getAllSources();
        }

        $sources = [];
        foreach ($sourceNames as $sourceName) {
             $sourceTarget = EnvConfig::getValue(EnvConfig::RESOURCE_SOURCE_MASK . strtoupper($sourceName));
             if ($sourceTarget) {
                 $sources[$sourceName] = $sourceTarget;
             }
        }

        return $sources;
    }

    private function getAllSources()
    {
        $sources = [];
        $configSources = EnvConfig::getValues(EnvConfig::RESOURCE_SOURCE_MASK);
        foreach ($configSources as $sourceName => $sourceTarget) {
            $sourceShortName = strtolower(str_replace(EnvConfig::RESOURCE_SOURCE_MASK, "", $sourceName));
            $sources[$sourceShortName] = $sourceTarget;
        }
        return $sources;
    }
}