<?php
declare(strict_types=1);

namespace RMS\ResourceCollector;

class EnvConfig
{
    const RESOURCE_SOURCE_PREFIX = 'RESOURCE_PROVIDER_TARGET_';

    public static function getValue(string $envVariableName): ?string
    {
        $configValue = getenv(strtoupper($envVariableName));
        if ($configValue === false) {
            return null;
        }
        return $configValue;
    }

    public static function getValues(string $prefix = ''): array
    {
        $resultValues = [];
        foreach ($_ENV as $configKey => $configValue) {
            if (preg_match("/^" . strtoupper($prefix) .".*/", $configKey)) {
                $resultValues[$configKey] = $configValue;
            }
        }

        return $resultValues;
    }

    public static function getAllSources(): array
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