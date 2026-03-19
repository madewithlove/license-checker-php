<?php

declare(strict_types=1);

namespace LicenseChecker\Commands;

use LicenseChecker\Configuration\ConfigurationExists;
use LicenseChecker\Configuration\LicenseConfiguration;
use LicenseChecker\Configuration\LicenseConfigurationParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

final class MigrateConfig extends Command
{
    private const string NAME = 'migrate-config';
    private const string DEFAULT_OLD_FILE = '.allowed-licenses';

    public function __construct(
        private readonly LicenseConfigurationParser $configParser,
        private readonly string $workingDirectory,
    ) {
        parent::__construct(self::NAME);
    }

    protected function configure(): void
    {
        $this->setDescription('Migrate old .allowed-licenses config to the new format');
        $this->addOption(
            'old-file',
            null,
            InputOption::VALUE_OPTIONAL,
            'Path to the old configuration file',
            self::DEFAULT_OLD_FILE,
        );
        $this->addOption(
            'filename',
            'f',
            InputOption::VALUE_OPTIONAL,
            'Output filename for the new configuration',
        );
        $this->addOption(
            'remove-old',
            null,
            InputOption::VALUE_NONE,
            'Remove the old configuration file after migration',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $oldFile */
        $oldFile = $input->getOption('old-file');
        $oldPath = $this->resolvePath($oldFile);

        if (!file_exists($oldPath)) {
            $io->error("Old configuration file not found: {$oldFile}");
            return 1;
        }

        try {
            $data = Yaml::parseFile($oldPath);
        } catch (ParseException $e) {
            $io->error("Failed to parse old configuration: {$e->getMessage()}");
            return 1;
        }

        if (!is_array($data) || !array_is_list($data)) {
            $io->error('The old configuration file does not contain a valid license list.');
            return 1;
        }

        /** @var list<string> $licenses */
        $licenses = $data;

        try {
            /** @var string|null $newFile */
            $newFile = is_string($input->getOption('filename')) ? $input->getOption('filename') : null;
            $this->configParser->writeConfiguration(LicenseConfiguration::allowed($licenses), $newFile);
        } catch (ConfigurationExists) {
            $io->error('The new configuration file already exists. Please remove it before migrating.');
            return 1;
        }

        $io->success('Configuration migrated successfully.');

        if ($input->getOption('remove-old')) {
            unlink($oldPath);
            $io->note("Removed old configuration file: {$oldFile}");
        }

        return 0;
    }

    private function resolvePath(string $fileName): string
    {
        if (str_starts_with($fileName, '/')) {
            return $fileName;
        }

        return $this->workingDirectory . '/' . $fileName;
    }
}
