<?php

namespace LicenseChecker\Commands;

use LicenseChecker\Commands\Output\DependencyCheck;
use LicenseChecker\Commands\Output\TableRenderer;
use LicenseChecker\Composer\DependencyTree;
use LicenseChecker\Composer\LicenseParser;
use LicenseChecker\Composer\LicenseRetriever;
use LicenseChecker\Configuration\AllowedLicensesParser;
use LicenseChecker\Dependency;
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

    /**
     * @var DependencyTree
     */
    private $dependencyTree;

    /**
     * @var TableRenderer
     */
    private $tableRenderer;

    public function __construct(
        LicenseRetriever $licenseRetriever,
        LicenseParser $licenseParser,
        AllowedLicensesParser $allowedLicensesParser,
        DependencyTree $dependencyTree,
        TableRenderer $tableRenderer
    ) {
        parent::__construct();
        $this->licenseRetriever = $licenseRetriever;
        $this->licenseParser = $licenseParser;
        $this->allowedLicensesParser = $allowedLicensesParser;
        $this->dependencyTree = $dependencyTree;
        $this->tableRenderer = $tableRenderer;
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
        $dependencies = $this->dependencyTree->getDependencies();

        $dependencyChecks = [];
        foreach ($dependencies as $dependency) {
            $dependencyCheck = new DependencyCheck($dependency->getName());
            foreach ($notAllowedLicenses as $notAllowedLicense) {
                $packagesUsingThisLicense = $this->licenseParser->getPackagesWithLicense($licenseJson, $notAllowedLicense);
                foreach ($packagesUsingThisLicense as $packageUsingThisLicense) {
                    if ($dependency->hasDependency($packageUsingThisLicense) || $dependency->getName() === $packageUsingThisLicense) {
                        $dependencyCheck = $dependencyCheck->addFailedDependency($packageUsingThisLicense, $notAllowedLicense);
                    }
                }
            }
            $dependencyChecks[] = $dependencyCheck;
        }

        $this->tableRenderer->renderDependencyChecks($dependencyChecks, $io);

        return empty($notAllowedLicenses) ? 0 : 1;
    }
}
