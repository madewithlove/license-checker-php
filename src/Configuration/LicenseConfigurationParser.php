<?php

declare(strict_types=1);

namespace LicenseChecker\Configuration;

use Symfony\Component\Yaml\Yaml;

final class LicenseConfigurationParser
{
    private const string DEFAULT_CONFIG_FILE_NAME = '.license-checker.yml';

    public function __construct(
        private readonly string $workingDirectory,
    ) {
    }

    public function parse(?string $fileName = null): LicenseConfiguration
    {
        $path = $this->getConfigurationFilePath($fileName ?? self::DEFAULT_CONFIG_FILE_NAME);
        $data = Yaml::parseFile($path);

        if (!is_array($data)) {
            throw new InvalidConfiguration('Invalid configuration file: expected a YAML mapping.');
        }

        if (!array_is_list($data)) {
            return $this->parseStructuredConfig($data);
        }

        throw new InvalidConfiguration(
            'It looks like you are using the old configuration format (a plain list). '
            . 'Run "license-checker migrate-config" to upgrade to the new format.'
        );
    }

    public function writeConfiguration(LicenseConfiguration $config, ?string $fileName = null): void
    {
        $fileName = $fileName ?? self::DEFAULT_CONFIG_FILE_NAME;

        if ($this->configurationExists($fileName)) {
            throw new ConfigurationExists();
        }

        $yaml = Yaml::dump([$config->mode->value => $config->licenses]);
        file_put_contents($this->getConfigurationFilePath($fileName), $yaml);
    }

    /**
     * @param array<mixed> $data
     */
    private function parseStructuredConfig(array $data): LicenseConfiguration
    {
        $hasAllowed = array_key_exists('allowed', $data);
        $hasDenied = array_key_exists('denied', $data);

        if ($hasAllowed && $hasDenied) {
            throw new InvalidConfiguration(
                'Configuration must have either "allowed" or "denied", not both.'
            );
        }

        if (!$hasAllowed && !$hasDenied) {
            throw new InvalidConfiguration(
                'Configuration must have an "allowed" or "denied" key.'
            );
        }

        if ($hasAllowed) {
            /** @var list<string> $licenses */
            $licenses = $data['allowed'];

            return LicenseConfiguration::allowed($licenses);
        }

        /** @var list<string> $licenses */
        $licenses = $data['denied'];

        return LicenseConfiguration::denied($licenses);
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
