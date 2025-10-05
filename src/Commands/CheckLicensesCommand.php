<?php

declare(strict_types=1);

namespace LicenseChecker\Commands;

use LicenseChecker\Output\JsonOutputFormatter;
use LicenseChecker\Output\OutputFormatterInterface;
use LicenseChecker\Output\TextOutputFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class CheckLicensesCommand extends Command
{
    protected static $defaultName = 'check';

    protected function configure(): void
    {
        $this
            ->setName('check') // Add this line explicitly
            ->setDescription('Verify allowed licenses for composer dependencies')
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format (text|json)', 'text');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $licenses = [
            'laravel/framework' => 'MIT',
            'phpunit/phpunit' => 'BSD-3-Clause',
        ];

        $format = $input->getOption('format');

        /** @var OutputFormatterInterface $formatter */
        $formatter = match ($format) {
            'json' => new JsonOutputFormatter(),
            default => new TextOutputFormatter(),
        };

        $output->writeln($formatter->format($licenses));

        return Command::SUCCESS;
    }
}
