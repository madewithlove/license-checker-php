<?php

namespace LicenseChecker\Commands;

use LicenseChecker\Composer\LicenseParser;
use LicenseChecker\Composer\LicenseRetriever;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CheckLicenses extends Command
{
    protected static $defaultName = 'licenses:check';

    /**
     * @var LicenseRetriever
     */
    private $licenseRetriever;

    /**
     * @var LicenseParser
     */
    private $licenseParser;

    public function __construct(LicenseRetriever $licenseRetriever, LicenseParser $licenseParser)
    {
        parent::__construct();
        $this->licenseRetriever = $licenseRetriever;
        $this->licenseParser = $licenseParser;
    }

    protected function configure()
    {
        $this->setDescription('Check licenses of composer dependencies');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $licenseJson = $this->licenseRetriever->getComposerLicenses(__DIR__ . '/../../');
            $usedLicenses = $this->licenseParser->parseLicenses($licenseJson);
            $output->writeln('Used licenses:');
            $output->writeln('--------------');
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
