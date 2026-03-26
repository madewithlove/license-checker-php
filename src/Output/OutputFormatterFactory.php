<?php

declare(strict_types=1);

namespace LicenseChecker\Output;

use LicenseChecker\Commands\Output\TableRendererInterface;
use LicenseChecker\Composer\ComposerJsonLineMapper;
use Symfony\Component\Console\Style\SymfonyStyle;

final class OutputFormatterFactory
{
    public static function create(
        OutputFormat $format,
        SymfonyStyle $io,
        TableRendererInterface $tableRenderer,
        ComposerJsonLineMapper $lineMapper,
    ): OutputFormatterInterface {
        return match ($format) {
            OutputFormat::JSON => new JsonOutputFormatter($io),
            OutputFormat::SARIF => new SarifOutputFormatter($io, $lineMapper),
            OutputFormat::TEXT => new TextOutputFormatter($io, $tableRenderer),
        };
    }
}
