<?php

declare(strict_types=1);

namespace LicenseChecker\Commands;

use LicenseChecker\Configuration\InvalidConfiguration;
use LicenseChecker\Configuration\LicenseConfigMode;
use LicenseChecker\Configuration\LicenseConfigurationParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Exception\ParseException;

final class ListConfiguredLicenses extends Command
{
    private const string NAME = 'list-config';

    public function __construct(
        private readonly LicenseConfigurationParser $configParser,
    ) {
        parent::__construct(self::NAME);
    }

    protected function configure(): void
    {
        $this->setDescription('List configured licenses (allowed or denied)');
        $this->addOption(
            'filename',
            'f',
            InputOption::VALUE_OPTIONAL,
            'Optional filename to be used instead of the default'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            /** @var string|null $fileName */
            $fileName = is_string($input->getOption('filename')) ? $input->getOption('filename') : null;
            $config = $this->configParser->parse($fileName);
        } catch (ParseException | InvalidConfiguration $e) {
            $output->writeln($e->getMessage());
            return 1;
        }

        $header = match ($config->mode) {
            LicenseConfigMode::Allowed => 'Allowed licenses:',
            LicenseConfigMode::Denied => 'Denied licenses:',
        };

        $io->section($header);

        foreach ($config->licenses as $license) {
            $output->writeln($license);
        }

        return 0;
    }
}
