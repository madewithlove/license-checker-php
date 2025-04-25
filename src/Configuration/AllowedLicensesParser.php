<?php

declare(strict_types=1);

namespace LicenseChecker\Configuration;

use Symfony\Component\Yaml\Yaml;

final class AllowedLicensesParser
{
    private const string DEFAULT_CONFIG_FILE_NAME = '.allowed-licenses';

    public function __construct(
        private readonly string $workingDirectory
    ) {
    }

    /**
     * @return list<string>
     */
    public function getAllowedLicenses(
        ?string $fileName
    ): array {
        /** @var list<string> $allowedLicenses */
        $allowedLicenses = Yaml::parseFile($this->getConfigurationFilePath($fileName ?? self::DEFAULT_CONFIG_FILE_NAME));
        sort($allowedLicenses);

        return $allowedLicenses;
    }

    /**
     * @param string[] $allowedLicenses
     */
    public function writeConfiguration(array $allowedLicenses, ?string $fileName): void
    {
        $fileName = $fileName ?? self::DEFAULT_CONFIG_FILE_NAME;
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
        if (str_starts_with($fileName, '/')) {
            return $fileName;
        }

        return $this->workingDirectory . '/' . $fileName;
    }
}
