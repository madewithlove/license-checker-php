<?php

declare(strict_types=1);

namespace LicenseChecker\Output;

use LicenseChecker\Commands\Output\TableRendererInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use InvalidArgumentException;
use LicenseChecker\Output\OutputFormatterInterface;

final class OutputFormatterFactory
{
    /**
    * Create formatter based on format string.
    */
    public static function create(
        OutputFormat $format,
        SymfonyStyle $io,
        TableRendererInterface $tableRenderer
    ): OutputFormatterInterface {
        return match ($format) {
            OutputFormat::JSON => new JsonOutputFormatter($io),
            OutputFormat::TEXT => new TextOutputFormatter($io, $tableRenderer),
        };
    }
}
