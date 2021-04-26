<?php

declare(strict_types=1);

namespace LicenseChecker\Configuration;

use Symfony\Component\Yaml\Yaml;

class AllowedLicensesParser
{
    private const DEFAULT_CONFIG_FILE_NAME = '.allowed-licenses';

    /**
     * @return list<string>
     */
    public function getAllowedLicenses(string $pathToConfigurationFile): array
    {
        /** @var list<string> $allowedLicenses */
        $allowedLicenses = Yaml::parseFile($pathToConfigurationFile . '/' . self::DEFAULT_CONFIG_FILE_NAME);
        sort($allowedLicenses);

        return $allowedLicenses;
    }

    /**
     * @param string[] $allowedLicenses
     */
    public function writeConfiguration(array $allowedLicenses): void
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

        return $path . '/' . self::DEFAULT_CONFIG_FILE_NAME;
    }
}
