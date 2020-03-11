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
        return Yaml::parseFile($this->getConfigurationFilePath($pathToConfigurationFile));
    }

    /**
     * @param string[] $allowedLicenses
     */
    public function writeConfiguration(array $allowedLicenses)
    {
        if ($this->configurationExists(getcwd())) {
            throw new ConfigurationExists();
        }
        $yaml = Yaml::dump($allowedLicenses);
        file_put_contents($this->getConfigurationFilePath(), $yaml);
    }

    private function configurationExists(string $pathToConfigurationFile): bool
    {
        return file_exists($this->getConfigurationFilePath($pathToConfigurationFile));
    }

    private function getConfigurationFilePath(?string $path = null): string
    {
        if (!$path) {
            $path = getcwd();
        }

        return $path . '/' . self::CONFIG_FILE_NAME;
    }
}
