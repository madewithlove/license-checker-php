<?php

namespace LicenseChecker\Commands;

use LicenseChecker\Composer\UsedLicensesParser;
use LicenseChecker\Composer\UsedLicensesRetriever;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ListUsedLicenses extends Command
{
    protected static $defaultName = 'used';

    public function __construct(
        private UsedLicensesParser $usedLicensesParser
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('List used licenses of composer dependencies');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $usedLicenses = $this->usedLicensesParser->parseLicenses(false);
            foreach ($usedLicenses as $usedLicense) {
                $output->writeln($usedLicense);
            }
        } catch (ProcessFailedException $e) {
            $output->writeln($e->getMessage());
            return 1;
        }

        return 0;
    }
}
