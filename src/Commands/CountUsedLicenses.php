<?php

namespace LicenseChecker\Commands;

use LicenseChecker\Composer\UsedLicensesParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CountUsedLicenses extends Command
{
    protected static $defaultName = 'count';

    public function __construct(
        private UsedLicensesParser $usedLicensesParser
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Count number of dependencies for each license');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $rows = [];
        try {
            $usedLicenses = $this->usedLicensesParser->countPackagesByLicense();
            foreach ($usedLicenses as $usedLicense => $numberOfPackages) {
                $rows[] = [$usedLicense, $numberOfPackages];
            }
        } catch (ProcessFailedException $e) {
            $output->writeln($e->getMessage());
            return 1;
        }

        $io->table(
            ['License', 'Number of dependencies'],
            $rows
        );

        return 0;
    }
}
