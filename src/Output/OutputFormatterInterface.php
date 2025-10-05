<?php

declare(strict_types=1);

namespace LicenseChecker\Output;

/**
 * Defines a contract for formatting license output.
 */
interface OutputFormatterInterface
{
    /**
     * Format the license list into a string for output.
     *
     * @param array<string, mixed> $licenses
     */
    public function format(array $licenses): string;
}
