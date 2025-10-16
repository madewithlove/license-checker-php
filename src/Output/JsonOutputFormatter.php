<?php

declare(strict_types=1);

namespace LicenseChecker\Output;

use Symfony\Component\Console\Style\SymfonyStyle;

final class JsonOutputFormatter implements OutputFormatterInterface
{
    public function __construct(private readonly SymfonyStyle $io) {}

    public function format(array $dependencyChecks): void
    {
        $licensesData = [];
        foreach ($dependencyChecks as $check) {
            $dep = $check->dependency;
            $licensesData[$dep->getName()] = $dep->getLicense();
        }

        $this->io->writeln(json_encode($licensesData, JSON_PRETTY_PRINT));
    }
}
