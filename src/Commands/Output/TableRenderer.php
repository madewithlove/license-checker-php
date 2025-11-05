<?php

declare(strict_types=1);

namespace LicenseChecker\Commands\Output;

use LicenseChecker\Dependency;
use Symfony\Component\Console\Style\SymfonyStyle;

final class TableRenderer implements TableRendererInterface
{
    /**
     * @param DependencyCheck[] $dependencyChecks
     */
    public function renderDependencyChecks(array $dependencyChecks, SymfonyStyle $io): void
    {
        usort($dependencyChecks, function (DependencyCheck $dependencyCheck, DependencyCheck $other): int {
            return $dependencyCheck->isAllowed <=> $other->isAllowed;
        });

        $io->table(
            $this->getHeaders($dependencyChecks),
            $this->getBody($dependencyChecks)
        );
    }

    /**
     * @param DependencyCheck[] $dependencyChecks
     */ 
    public function renderAsText(array $dependencyChecks): string
    {
        usort($dependencyChecks, static function (DependencyCheck $a, DependencyCheck $b): int {
            return $a->isAllowed <=> $b->isAllowed;
        });

        $lines = [];
        $headers = $this->getHeaders($dependencyChecks);
        $lines[] = implode(' | ', array_map('strtoupper', $headers));
        $lines[] = str_repeat('-', strlen($lines[0]));

        foreach ($dependencyChecks as $dependencyCheck) {
            $symbol = $dependencyCheck->isAllowed ? '✓' : '✗';
            $row = [
                $symbol,
                $dependencyCheck->renderNameWithLicense(),
            ];

            if (!$dependencyCheck->isAllowed && !empty($dependencyCheck->causedBy)) {
                $row[] = implode(', ', array_map(
                    static fn ($d) => $d->renderNameWithLicense(),
                    $dependencyCheck->causedBy
                ));
            }

            $lines[] = implode(' | ', $row);
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }


    /**
     * @param DependencyCheck[] $dependencyChecks
     */
    private function hasFailures(array $dependencyChecks): bool
    {
        foreach ($dependencyChecks as $dependencyCheck) {
            if (!$dependencyCheck->isAllowed) {
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
     * @return array[]
     */
    private function getBody(array $dependencyChecks): array
    {
        if (!$this->hasFailures($dependencyChecks)) {
            return $this->renderAllOkay($dependencyChecks);
        }

        $body = [];
        foreach ($dependencyChecks as $dependencyCheck) {
            if ($dependencyCheck->isAllowed) {
                $body[] = $this->renderAllowedLineWithEmptyFailureCause($dependencyCheck);
            } else {
                $firstLine = true;
                foreach ($dependencyCheck->causedBy as $causeOfFailure) {
                    if ($firstLine) {
                        $body[] = $this->renderFailedLineWithCauseOfFailure($dependencyCheck, $causeOfFailure);
                        $firstLine = false;
                    } else {
                        $body[] = $this->renderAdditionalCauseOfFailure($causeOfFailure);
                    }
                }
            }
        }
        return $body;
    }

    /**
     * @param DependencyCheck[] $dependencyChecks
     * @return array[]
     */
    private function renderAllOkay(array $dependencyChecks): array
    {
        return array_map(function (DependencyCheck $dependencyCheck) {
            return [
                $this->renderBoolean($dependencyCheck->isAllowed),
                $dependencyCheck->renderNameWithLicense() ,
            ];
        }, $dependencyChecks);
    }

    /**
     * @return string[]
     */
    private function renderAllowedLineWithEmptyFailureCause(DependencyCheck $dependencyCheck): array
    {
        return [
            $this->renderBoolean($dependencyCheck->isAllowed),
            $dependencyCheck->renderNameWithLicense(),
            '',
        ];
    }

    /**
     * @return string[]
     */
    private function renderFailedLineWithCauseOfFailure(
        DependencyCheck $dependencyCheck,
        Dependency $causeOfFailure
    ): array {
        return [
            $this->renderBoolean($dependencyCheck->isAllowed),
            $dependencyCheck->renderNameWithLicense(),
            $causeOfFailure->renderNameWithLicense(),
        ];
    }

    /**
     * @return string[]
     */
    private function renderAdditionalCauseOfFailure(Dependency $causeOfFailure): array
    {
        return [
            '',
            '',
            $causeOfFailure->renderNameWithLicense(),
        ];
    }
}
