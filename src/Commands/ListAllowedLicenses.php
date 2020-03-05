<?php

namespace LicenseChecker\Commands;

use LicenseChecker\Configuration\AllowedLicensesParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Exception\ParseException;

class ListAllowedLicenses extends Command
{
    protected static $defaultName = 'licenses:allowed';

    /**
     * @var AllowedLicensesParser
     */
    private $allowedLicensesParser;

    public function __construct(AllowedLicensesParser $allowedLicensesParser)
    {
        parent::__construct();
        $this->allowedLicensesParser = $allowedLicensesParser;
    }


    protected function configure()
    {
        $this->setDescription('List used licenses of composer dependencies');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $allowedLicenses = $this->allowedLicensesParser->getAllowedLicenses();
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
