<?php

namespace LicenseChecker\Commands;

use LicenseChecker\Composer\LicenseParser;
use LicenseChecker\Composer\LicenseRetriever;
use LicenseChecker\Configuration\AllowedLicensesParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Yaml\Exception\ParseException;

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

    /**
     * @var AllowedLicensesParser
     */
    private $allowedLicensesParser;

    public function __construct(
        LicenseRetriever $licenseRetriever,
        LicenseParser $licenseParser,
        AllowedLicensesParser $allowedLicensesParser
    ) {
        parent::__construct();
        $this->licenseRetriever = $licenseRetriever;
        $this->licenseParser = $licenseParser;
        $this->allowedLicensesParser = $allowedLicensesParser;
    }

    protected function configure(): void
    {
        $this->setDescription('Check licenses of composer dependencies');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $licenseJson = $this->licenseRetriever->getComposerLicenses(__DIR__ . '/../../');
            $usedLicenses = $this->licenseParser->parseLicenses($licenseJson);
        } catch (ProcessFailedException $e) {
            $output->writeln($e->getMessage());
            return 1;
        }

        try {
            $allowedLicenses = $this->allowedLicensesParser->getAllowedLicenses(getcwd());
        } catch (ParseException $e) {
            $output->writeln($e->getMessage());
            return 1;
        }

        $notAllowedLicenses = array_diff($usedLicenses, $allowedLicenses);

        if (!empty($notAllowedLicenses)) {
            foreach ($notAllowedLicenses as $notAllowedLicense) {
                $io->error('The following packages are using the ' . $notAllowedLicense . ' license which is not allowed.');
                $packagesUsingThisLicense = $this->licenseParser->getPackagesWithLicense($licenseJson, $notAllowedLicense);

                $io->table(
                    ['package name'],
                    array_map(
                        function (string $packageName): array {
                            return [$packageName];
                        },
                        $packagesUsingThisLicense
                    )
                );
            }
            return 1;
        }

        $output->writeln('All used licenses are allowed');
        return 0;
    }
}
