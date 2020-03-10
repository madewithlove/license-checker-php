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
     * @var LicenseRetriever
     */
    private $licenseRetriever;

    /**
     * @var LicenseParser
     */
    private $licenseParser;

    public function __construct(
        LicenseRetriever $licenseRetriever,
        LicenseParser $licenseParser
    ) {
        parent::__construct();
        $this->licenseRetriever = $licenseRetriever;
        $this->licenseParser = $licenseParser;
    }

    protected function configure()
    {
        $this->setDescription('List used licenses of composer dependencies');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $licenseJson = $this->licenseRetriever->getComposerLicenses(getcwd());
            $usedLicenses = $this->licenseParser->parseLicenses($licenseJson);
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
