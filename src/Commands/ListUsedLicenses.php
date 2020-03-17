<?php

namespace LicenseChecker\Commands;

use LicenseChecker\Composer\LicenseParser;
use LicenseChecker\Composer\LicenseRetriever;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ListUsedLicenses extends Command
{
    protected static $defaultName = 'licenses:used';

    /**
     * @var LicenseParser
     */
    private $licenseParser;

    public function __construct(
        LicenseParser $licenseParser
    ) {
        parent::__construct();
        $this->licenseParser = $licenseParser;
    }

    protected function configure(): void
    {
        $this->setDescription('List used licenses of composer dependencies');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $usedLicenses = $this->licenseParser->parseLicenses();
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
