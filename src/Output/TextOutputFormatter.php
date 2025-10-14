<?php

declare(strict_types=1);

namespace LicenseChecker\Output;

use LicenseChecker\Commands\Output\TableRenderer;
use Symfony\Component\Console\Style\SymfonyStyle;

final class TextOutputFormatter implements OutputFormatterInterface
{
    public function __construct(
        private readonly ?SymfonyStyle $io = null,
        private readonly ?TableRenderer $tableRenderer = null
    ) {
    }

    public function format(array $licenses): string
    {
        if ($this->io && $this->tableRenderer) {
            $this->tableRenderer->renderDependencyChecks($licenses, $this->io);
            return '';
        }

        $lines = [];
        foreach ($licenses as $package => $license) {
            $lines[] = sprintf('%s: %s', $package, $license);
        }

        return implode(PHP_EOL, $lines);
    }
}
