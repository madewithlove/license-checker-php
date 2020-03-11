<?php

namespace LicenseChecker\Commands;

use LicenseChecker\Composer\LicenseParser;
use LicenseChecker\Composer\LicenseRetriever;
use LicenseChecker\Configuration\AllowedLicensesParser;
use LicenseChecker\Configuration\ConfigurationExists;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;

class GenerateConfig extends Command
{
    protected static $defaultName = 'config:generate';

    /**
     * @var AllowedLicensesParser
     */
    private $allowedLicensesParser;

    /**
     * @var LicenseRetriever
     */
    private $licenseRetriever;

    /**
     * @var LicenseParser
     */
    private $licenseParser;

    public function __construct(
        AllowedLicensesParser $allowedLicensesParser,
        LicenseRetriever $licenseRetriever,
        LicenseParser $licenseParser
    ) {
        parent::__construct();
        $this->allowedLicensesParser = $allowedLicensesParser;
        $this->licenseRetriever = $licenseRetriever;
        $this->licenseParser = $licenseParser;
    }

    protected function configure(): void
    {
        $this->setDescription('Generates allowed licenses config based on used licenses');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $licenseJson = $this->licenseRetriever->getComposerLicenses(getcwd());
            $usedLicenses = $this->licenseParser->parseLicenses($licenseJson);
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
