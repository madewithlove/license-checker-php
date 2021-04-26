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
    public function writeConfiguration(array $allowedLicenses, string $fileName = self::DEFAULT_CONFIG_FILE_NAME): void
    {
        if ($this->configurationExists($fileName)) {
            throw new ConfigurationExists();
        }
        $yaml = Yaml::dump($allowedLicenses);
        file_put_contents($this->getConfigurationFilePath($fileName), $yaml);
    }

    private function configurationExists(string $fileName): bool
    {
        return file_exists($this->getConfigurationFilePath($fileName));
    }

    private function getConfigurationFilePath(string $fileName): string
    {
        return $this->workingDirectory . '/' . $fileName;
    }
}
