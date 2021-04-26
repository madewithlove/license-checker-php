<?php

declare(strict_types=1);

namespace LicenseChecker\Commands;

use LicenseChecker\Configuration\AllowedLicensesParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Exception\ParseException;

class ListAllowedLicenses extends Command
{
    protected static $defaultName = 'allowed';

    public function __construct(
        private AllowedLicensesParser $allowedLicensesParser
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
            $allowedLicenses = $this->allowedLicensesParser->getAllowedLicenses(getcwd());
        } catch (ParseException $e) {
            $output->writeln($e->getMessage());
            return 1;
        }

        foreach ($allowedLicenses as $allowedLicense) {
            $output->writeln($allowedLicense);
        }

        return 0;
    }
}
