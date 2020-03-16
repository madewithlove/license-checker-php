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
        if (!$this->hasFailures($dependencyChecks)) {
            return $this->renderAllOkay($dependencyChecks);
        }

        $body = [];
        foreach ($dependencyChecks as $dependencyCheck) {
            if ($dependencyCheck->isAllowed()) {
                $body[] = $this->renderAllowedLineWithEmptyFailureCause($dependencyCheck);
            } else {
                $firstLine = true;
                foreach ($dependencyCheck->getCausedBy() as $causeOfFailure) {
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
     * @return string[]
     */
    private function renderAllOkay(array $dependencyChecks): array
    {
        return array_map(function (DependencyCheck $dependencyCheck) {
            return [
                $this->renderBoolean($dependencyCheck->isAllowed()),
                $dependencyCheck->getName(),
            ];
        }, $dependencyChecks);
    }

    /**
     * @param DependencyCheck $dependencyCheck
     * @return string[]
     */
    private function renderAllowedLineWithEmptyFailureCause(DependencyCheck $dependencyCheck): array
    {
        return [
            $this->renderBoolean($dependencyCheck->isAllowed()),
            $dependencyCheck->getName(),
            '',
        ];
    }

    /**
     * @param DependencyCheck $dependencyCheck
     * @param CauseOfFailure $causeOfFailure
     * @return string[]
     */
    private function renderFailedLineWithCauseOfFailure(
        DependencyCheck $dependencyCheck,
        CauseOfFailure $causeOfFailure
    ): array {
        return [
            $this->renderBoolean($dependencyCheck->isAllowed()),
            $dependencyCheck->getName(),
            $causeOfFailure->getName() . ' [' . $causeOfFailure->getLicense() . ']',
        ];
    }

    /**
     * @param CauseOfFailure $causeOfFailure
     * @return string[]
     */
    private function renderAdditionalCauseOfFailure(CauseOfFailure $causeOfFailure): array
    {
        return [
            '',
            '',
            $causeOfFailure->getName() . ' [' . $causeOfFailure->getLicense() . ']',
        ];
    }
}
