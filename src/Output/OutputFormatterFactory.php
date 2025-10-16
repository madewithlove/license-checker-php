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
    */
    public static function create(OutputFormat $format, SymfonyStyle $io, TableRenderer $tableRenderer): object
    {
        return match ($format) {
            OutputFormat::JSON => new JsonOutputFormatter($io),
            OutputFormat::TEXT => new TextOutputFormatter($io, $tableRenderer),
        };
    }
}
