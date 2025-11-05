<?php

declare(strict_types=1);

namespace LicenseChecker\Commands\Output;

use Symfony\Component\Console\Style\SymfonyStyle;

interface TableRendererInterface
{
    /**
     * @param DependencyCheck[] $dependencyChecks
     */
    public function renderDependencyChecks(array $dependencyChecks, SymfonyStyle $io): void;

    /**
     * Render as plain text string (for file output)
     * @param DependencyCheck[] $dependencyChecks
     */
    public function renderAsText(array $dependencyChecks): string;
}
