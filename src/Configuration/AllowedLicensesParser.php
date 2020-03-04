<?php

namespace LicenseChecker\Configuration;

use Symfony\Component\Yaml\Yaml;

class AllowedLicensesParser
{
    private const DEFAULT_PATH = __DIR__ . '/.allowed-licenses';

    /**
     * @param string $pathToConfigurationFile
     * @return string[]
     */
    public function getAllowedLicenses(string $pathToConfigurationFile = self::DEFAULT_PATH): array
    {
        $value = Yaml::parseFile($pathToConfigurationFile);
        return explode(' ', $value);
    }
}
