<?php

declare(strict_types=1);

namespace LicenseChecker\Configuration;

use Symfony\Component\Yaml\Yaml;

class AllowedLicensesParser
{
    private const DEFAULT_CONFIG_FILE_NAME = '.allowed-licenses';

    public function __construct(
        private string $workingDirectory
    ) {
    }

    /**
     * @return list<string>
     */
    public function getAllowedLicenses(
        string $fileName = self::DEFAULT_CONFIG_FILE_NAME
    ): array {
        /** @var list<string> $allowedLicenses */
        $allowedLicenses = Yaml::parseFile($this->getConfigurationFilePath($fileName));
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

    private function getConfigurationFilePath(string $fileName = self::DEFAULT_CONFIG_FILE_NAME): string
    {
        return $this->workingDirectory . '/' . $fileName;
    }
}
