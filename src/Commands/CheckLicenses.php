<?php

namespace LicenseChecker\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckLicenses extends Command
{
    protected static $defaultName = 'licenses:check';

    protected function configure()
    {
        $this->setDescription('Check licenses of composer dependencies');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('foobar');

        return 0;
    }
}
