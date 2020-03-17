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
    protected static $defaultName = 'licenses:used';

    /**
     * @var UsedLicensesParser
     */
    private $usedLicensesParser;

    public function __construct(
        UsedLicensesParser $usedLicensesParser
    ) {
        parent::__construct();
        $this->usedLicensesParser = $usedLicensesParser;
    }

    protected function configure(): void
    {
        $this->setDescription('List used licenses of composer dependencies');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $usedLicenses = $this->usedLicensesParser->parseLicenses();
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
