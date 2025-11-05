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
     * @psalm-suppress PossiblyUnusedReturnValue
     */
    public function format(array $dependencyChecks): string
    {
        $this->tableRenderer->renderDependencyChecks($dependencyChecks, $this->io);

        return $this->tableRenderer->renderAsText($dependencyChecks);
    }

}
