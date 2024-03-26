<?php

declare(strict_types=1);

namespace LicenseChecker\Commands;

use LicenseChecker\Commands\Output\DependencyCheck;
use LicenseChecker\Commands\Output\TableRenderer;
use LicenseChecker\Composer\DependencyTree;
use LicenseChecker\Composer\UsedLicensesParser;
use LicenseChecker\Configuration\AllowedLicensesParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Yaml\Exception\ParseException;

class CheckLicenses extends Command
{
    protected static $defaultName = 'check';

    public function __construct(
        private readonly UsedLicensesParser $usedLicensesParser,
        private readonly AllowedLicensesParser $allowedLicensesParser,
        private readonly DependencyTree $dependencyTree,
        private readonly TableRenderer $tableRenderer
    ) {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this->setDescription('Check licenses of composer dependencies');
        $this->addOption('no-dev', null, InputOption::VALUE_NONE, 'Do not include dev dependencies');
        $this->addOption(
            'filename',
            'f',
            InputOption::VALUE_OPTIONAL,
            'Optional filename to be used instead of the default'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $usedLicenses = $this->usedLicensesParser->parseLicenses((bool)$input->getOption('no-dev'));
        } catch (ProcessFailedException $e) {
            $output->writeln($e->getMessage());
            return 1;
        }

        try {
            /** @var string|null $fileName */
            $fileName = is_string($input->getOption('filename')) ? $input->getOption('filename') : null;
            $allowedLicenses = $this->allowedLicensesParser->getAllowedLicenses($fileName);
        } catch (ParseException $e) {
            $output->writeln($e->getMessage());
            return 1;
        }

        $notAllowedLicenses = array_diff($usedLicenses, $allowedLicenses);
        $dependencies = $this->dependencyTree->getDependencies((bool)$input->getOption('no-dev'));

        $dependencyChecks = [];
        foreach ($dependencies as $dependency) {
            $dependencyCheck = new DependencyCheck($dependency);
            foreach ($notAllowedLicenses as $notAllowedLicense) {
                $packagesUsingThisLicense = $this->usedLicensesParser->getPackagesWithLicense($notAllowedLicense, (bool)$input->getOption('no-dev'));
                foreach ($packagesUsingThisLicense as $packageUsingThisLicense) {
                    if ($dependency->hasDependency($packageUsingThisLicense) || $dependency->is($packageUsingThisLicense)) {
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
