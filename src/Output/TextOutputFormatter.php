<?php

declare(strict_types=1);

namespace LicenseChecker\Output;

/**
 * Default human-readable output formatter.
 */
final class TextOutputFormatter implements OutputFormatterInterface
{
    public function format(array $licenses): string
    {
        $lines = [];

        foreach ($licenses as $package => $license) {
            $lines[] = sprintf('%s: %s', $package, $license);
        }

        return implode(PHP_EOL, $lines);
    }
}
