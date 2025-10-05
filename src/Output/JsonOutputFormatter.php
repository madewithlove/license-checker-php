<?php

declare(strict_types=1);

namespace LicenseChecker\Output;

use RuntimeException;

/**
 * JSON output formatter for programmatic consumption.
 */
final class JsonOutputFormatter implements OutputFormatterInterface
{
    public function format(array $licenses): string
    {
        $json = json_encode($licenses, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new RuntimeException('Failed to encode JSON: ' . json_last_error_msg());
        }

        return $json;
    }
}
