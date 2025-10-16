<?php

declare(strict_types=1);

namespace LicenseChecker\Output;

use LicenseChecker\Commands\Output\TableRendererInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use LicenseChecker\Commands\Output\DependencyCheck;

final class TextOutputFormatter implements OutputFormatterInterface
{
    public function __construct(
        private readonly SymfonyStyle $io,
        private readonly TableRendererInterface $tableRenderer,
    ) {
    }

    /**
     * @param DependencyCheck[] $dependencyChecks
     */
    public function format(array $dependencyChecks): void
    {
        $this->tableRenderer->renderDependencyChecks($dependencyChecks, $this->io);
    }
}
