<?php

declare(strict_types=1);

namespace LicenseChecker\Commands;

use LicenseChecker\Composer\UsedLicensesParser;
use LicenseChecker\Configuration\AllowedLicensesParser;
use LicenseChecker\Configuration\ConfigurationExists;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;

class GenerateConfig extends Command
{
    protected static $defaultName = 'generate-config';

    public function __construct(
        private AllowedLicensesParser $allowedLicensesParser,
        private UsedLicensesParser $usedLicensesParser
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Generates allowed licenses config based on used licenses')
            ->addOption('no-dev', null, InputOption::VALUE_NONE, 'Do not include dev dependencies');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $usedLicenses = $this->usedLicensesParser->parseLicenses((bool)$input->getOption('no-dev'));
        } catch (ProcessFailedException $e) {
            $io->error($e->getMessage());
            return 1;
        }

        sort($usedLicenses);

        try {
            $this->allowedLicensesParser->writeConfiguration(array_values($usedLicenses));
            $io->success('Configuration file successfully written');
        } catch (ConfigurationExists $e) {
            $io->error('The configuration file already exists. Please remove it before generating a new one.');
            return 1;
        }

        return 0;
    }
}
