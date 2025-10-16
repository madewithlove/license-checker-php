<?php

declare(strict_types=1);

namespace LicenseChecker\Tests\Fakes;

use LicenseChecker\Commands\Output\DependencyCheck;
use LicenseChecker\Commands\Output\TableRendererInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class FakeTableRenderer implements TableRendererInterface
{
    /**
     * @param DependencyCheck[] $dependencyChecks
     */
    public function renderDependencyChecks(array $dependencyChecks, SymfonyStyle $io): void
    {
        foreach ($dependencyChecks as $check) {
            $dep = $check->dependency;
            $name = $dep->getName();
            $license = $dep->getLicense();

            $io->writeln(sprintf('%s: %s', $name, $license));
        }
    }
}
