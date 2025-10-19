<?php

declare(strict_types=1);

namespace LicenseChecker\Output;

use Symfony\Component\Console\Style\SymfonyStyle;
use RuntimeException;
use JsonException;
use LicenseChecker\Commands\Output\DependencyCheck;

final class JsonOutputFormatter implements OutputFormatterInterface
{
    public function __construct(private readonly SymfonyStyle $io)
    {
    }

    /**
     * @param DependencyCheck[] $dependencyChecks
     * @psalm-suppress PossiblyUnusedReturnValue
     */
    public function format(array $dependencyChecks): string
    {
        $licensesData = [];

        foreach ($dependencyChecks as $check) {
            $licensesData[$check->dependency->getName()] = [
                "license" => $check->dependency->getLicense(),
                "is_allowed" => $check->isAllowed,
            ];
        }

        try {
            $json = json_encode($licensesData, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Failed to encode JSON: ' . $e->getMessage(), 0, $e);
        }

        $this->io->writeln($json);
        return $json;
    }
}
