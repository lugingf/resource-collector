<?php
declare(strict_types=1);

namespace RMS\ResourceCollector;

class EnvConfig
{
    const RESOURCE_SOURCE_MASK = 'RESOURCE_PROVIDER_TARGET_';

    public static function getValue(string $envVariableName): ?string
    {
        $configValue = getenv(strtoupper($envVariableName));
        if (false === $configValue) {
            return null;
        }
        return $configValue;
    }

    public static function getValues(string $prefix = ''): ?array
    {
        $resultValues = [];
        foreach ($_ENV as $configKey => $configValue) {
            if (preg_match("/^" . strtoupper($prefix) .".*/", $configKey)) {
                $resultValues[$configKey] = $configValue;
            }
        }

        return $resultValues;
    }
}