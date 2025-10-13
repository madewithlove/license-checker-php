<?php

declare(strict_types=1);

namespace LicenseChecker\Output;

use Symfony\Component\Console\Style\SymfonyStyle;

final class OutputFormatterFactory
{
    /**
     * Create formatter based on format string.
     *
     * @param string $format Format type (text|json)
     * @param SymfonyStyle|null $io Symfony style (required for text format)
     * @param object|null $tableRenderer Table renderer (required for text format)
     * @return OutputFormatterInterface
     */
    public static function create(
        string $format,
        ?SymfonyStyle $io = null,
        ?object $tableRenderer = null
    ): OutputFormatterInterface {
        return match (strtolower($format)) {
            'json' => new JsonOutputFormatter(),
            default => new TextOutputFormatter($io, $tableRenderer),
        };
    }
}
