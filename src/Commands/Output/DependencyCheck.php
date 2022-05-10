<?php

declare(strict_types=1);

namespace LicenseChecker\Commands\Output;

use LicenseChecker\Dependency;

final class DependencyCheck
{
    private bool $isAllowed = true;

    /**
     * @var CauseOfFailure[]
     */
    private array $causedBy = [];

    public function __construct(
        public readonly Dependency $dependency,
    ) {
    }

    public function addFailedDependency(string $dependency, string $license): self
    {
        $dependencyCheck = clone $this;
        $dependencyCheck->isAllowed = false;
        $dependencyCheck->causedBy[] = new CauseOfFailure($dependency, $license);

        return $dependencyCheck;
    }

    public function renderNameWithLicense(): string
    {
        if (empty($this->dependency->getLicense())) {
            return $this->dependency->getName();
        }

        return $this->dependency->getName() . ' [' . $this->dependency->getLicense() . ']';
    }

    public function isAllowed(): bool
    {
        return $this->isAllowed;
    }

    /**
     * @return CauseOfFailure[]
     */
    public function getCausedBy(): array
    {
        return $this->causedBy;
    }
}
