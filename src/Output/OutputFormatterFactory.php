<?php

declare(strict_types=1);

namespace LicenseChecker\Output;

use LicenseChecker\Commands\Output\TableRenderer;
use Symfony\Component\Console\Style\SymfonyStyle;
use InvalidArgumentException;

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
        OutputFormat $format,
        ?SymfonyStyle $io = null,
        ?TableRenderer $tableRenderer = null
    ): OutputFormatterInterface {
        return match ($format) {
            OutputFormat::JSON => new JsonOutputFormatter(),
            OutputFormat::TEXT => new TextOutputFormatter($io, $tableRenderer),
        };
    }

}
