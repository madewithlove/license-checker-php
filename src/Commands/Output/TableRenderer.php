<?php

namespace LicenseChecker\Commands\Output;

use Symfony\Component\Console\Style\SymfonyStyle;

class TableRenderer
{
    /**
     * @param DependencyCheck[] $dependencyChecks
     * @param SymfonyStyle $io
     */
    public function renderDependencyChecks(array $dependencyChecks, SymfonyStyle $io)
    {
        $io->table(
            $this->getHeaders($dependencyChecks),
            $this->getBody($dependencyChecks)
        );
    }

    /**
     * @param DependencyCheck[] $dependencyChecks
     * @return bool
     */
    private function hasFailures(array $dependencyChecks): bool
    {
        foreach ($dependencyChecks as $dependencyCheck) {
            if (!$dependencyCheck->isAllowed()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param DependencyCheck[] $dependencyChecks
     * @return string[]
     */
    private function getHeaders(array $dependencyChecks): array
    {
        if ($this->hasFailures($dependencyChecks)) {
            return ['', 'dependency', 'caused by'];
        }

        return ['', 'dependency'];
    }

    private function renderBoolean(bool $boolean): string
    {
        if ($boolean) {
            return '<info>✓</info>';
        }

        return '<fg=red>✗</>';
    }

    /**
     * @param DependencyCheck[] $dependencyChecks
     * @return string[]
     */
    private function getBody(array $dependencyChecks): array
    {
        if ($this->hasFailures($dependencyChecks)) {
            return array_map(function (DependencyCheck $dependencyCheck) {
                return [
                    $this->renderBoolean($dependencyCheck->isAllowed()),
                    $dependencyCheck->getName(),
                    implode(', ', $dependencyCheck->getCausedBy()),
                ];
            }, $dependencyChecks);
        }

        return array_map(function (DependencyCheck $dependencyCheck) {
            return [
                $this->renderBoolean($dependencyCheck->isAllowed()),
                $dependencyCheck->getName(),
            ];
        }, $dependencyChecks);
    }
}
