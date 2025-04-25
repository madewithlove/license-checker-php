<?php

declare(strict_types=1);

namespace LicenseChecker\Commands;

use LicenseChecker\Configuration\AllowedLicensesParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Exception\ParseException;

final class ListAllowedLicenses extends Command
{
    private const string NAME = 'allowed';

    public function __construct(
        private readonly AllowedLicensesParser $allowedLicensesParser
    ) {
        parent::__construct(self::NAME);
    }

    protected function configure(): void
    {
        $this->setDescription('List used licenses of composer dependencies');
        $this->addOption(
            'filename',
            'f',
            InputOption::VALUE_OPTIONAL,
            'Optional filename to be used instead of the default'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            /** @var string|null $fileName */
            $fileName = is_string($input->getOption('filename')) ? $input->getOption('filename') : null;
            $allowedLicenses = $this->allowedLicensesParser->getAllowedLicenses($fileName);
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
