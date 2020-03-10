<?php

namespace LicenseChecker\Configuration;

use Symfony\Component\Yaml\Yaml;

class AllowedLicensesParser
{
    private const CONFIG_FILE_NAME = '.allowed-licenses';

    /**
     * @param string $pathToConfigurationFile
     * @return string[]
     */
    public function getAllowedLicenses(string $pathToConfigurationFile): array
    {
        $allowedLicenses = Yaml::parseFile($pathToConfigurationFile . '/' . self::CONFIG_FILE_NAME);
        sort($allowedLicenses);

        return $allowedLicenses;
    }
}
