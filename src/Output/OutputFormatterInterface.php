<?php

declare(strict_types=1);

namespace LicenseChecker\Output;

use LicenseChecker\Commands\Output\DependencyCheck;

/**
 * Defines a contract for formatting license output.
 */
interface OutputFormatterInterface
{
    /**
     * @param DependencyCheck[] $dependencyChecks
     */
    public function format(array $dependencyChecks): string;
}
