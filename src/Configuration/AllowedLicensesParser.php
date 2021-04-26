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
    public function getAllowedLicenses(
        string $pathToConfigurationFile,
        string $fileName = self::DEFAULT_CONFIG_FILE_NAME
    ): array {
        /** @var list<string> $allowedLicenses */
        $allowedLicenses = Yaml::parseFile($pathToConfigurationFile . '/' . $fileName);
        sort($allowedLicenses);

        return $allowedLicenses;
    }

    /**
     * @param string[] $allowedLicenses
     */
    public function writeConfiguration(array $allowedLicenses): void
    {
        if ($this->configurationExists()) {
            throw new ConfigurationExists();
        }
        $yaml = Yaml::dump($allowedLicenses);
        file_put_contents($this->getConfigurationFilePath(), $yaml);
    }

    private function configurationExists(): bool
    {
        return file_exists($this->getConfigurationFilePath());
    }

    private function getConfigurationFilePath(): string
    {
        return getcwd() . '/' . self::DEFAULT_CONFIG_FILE_NAME;
    }
}
