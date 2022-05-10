<?php

declare(strict_types=1);

namespace LicenseChecker\Commands\Output;

final class DependencyCheck
{
    private bool $isAllowed = true;

    /**
     * @var CauseOfFailure[]
     */
    private array $causedBy = [];

    public function __construct(
        private string $name,
        private string $license,
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
        if (empty($this->license)) {
            return $this->name;
        }

        return $this->name . ' [' . $this->license . ']';
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
