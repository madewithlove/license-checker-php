<?php

declare(strict_types=1);

namespace LicenseChecker\Output;

use Symfony\Component\Console\Style\SymfonyStyle;
use RuntimeException;
use JsonException;
use LicenseChecker\Commands\Output\DependencyCheck;

final class JsonOutputFormatter implements OutputFormatterInterface
{
    public function __construct(private readonly SymfonyStyle $io) {}

    /**
     * @param DependencyCheck[] $dependencyChecks
     */
    public function format(array $dependencyChecks): void
    {
        /** @var array<string, string|resource|null> $licensesData */
        $licensesData = [];

        foreach ($dependencyChecks as $check) {
            /** @var object{dependency?: object|null} $check */
            $dep = $check->dependency ?? null;

            if (is_object($dep) && method_exists($dep, 'getName') && method_exists($dep, 'getLicense')) {
                /** @var string $name */
                $name = $dep->getName();
                /** @var string $license */
                $license = $dep->getLicense();
                $licensesData[$name] = $license;
            } else {
                $licensesData[] = $dep;
            }
        }

        try {
            $json = json_encode($licensesData, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Failed to encode JSON: ' . $e->getMessage(), 0, $e);
        }

        $this->io->writeln($json);
    }
}
