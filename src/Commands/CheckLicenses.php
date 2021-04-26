<?php

namespace LicenseChecker\Commands;

use LicenseChecker\Commands\Output\DependencyCheck;
use LicenseChecker\Commands\Output\TableRenderer;
use LicenseChecker\Composer\DependencyTree;
use LicenseChecker\Composer\UsedLicensesParser;
use LicenseChecker\Configuration\AllowedLicensesParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Yaml\Exception\ParseException;

class CheckLicenses extends Command
{
    protected static $defaultName = 'check';

    public function __construct(
        private UsedLicensesParser $usedLicensesParser,
        private AllowedLicensesParser $allowedLicensesParser,
        private DependencyTree $dependencyTree,
        private TableRenderer $tableRenderer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Check licenses of composer dependencies');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $usedLicenses = $this->usedLicensesParser->parseLicenses(false);
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
        $dependencies = $this->dependencyTree->getDependencies(false);

        $dependencyChecks = [];
        foreach ($dependencies as $dependency) {
            $dependencyCheck = new DependencyCheck($dependency->getName());
            foreach ($notAllowedLicenses as $notAllowedLicense) {
                $packagesUsingThisLicense = $this->usedLicensesParser->getPackagesWithLicense($notAllowedLicense, false);
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
