<?php

declare(strict_types=1);

namespace LicenseChecker\Composer;

final class ComposerJsonLineMapper
{
    /** @var array<string, int> */
    private array $packageLines = [];

    public function __construct(string $workingDirectory)
    {
        $path = $workingDirectory . '/composer.json';

        if (!file_exists($path)) {
            return;
        }

        $lines = file($path);

        if ($lines === false) {
            return;
        }

        foreach ($lines as $index => $line) {
            if (preg_match('/^\s*"([^"]+\/[^"]+)"\s*:/', $line, $matches)) {
                $this->packageLines[$matches[1]] = $index + 1;
            }
        }
    }

    public function getLineNumber(string $packageName): ?int
    {
        return $this->packageLines[$packageName] ?? null;
    }
}
