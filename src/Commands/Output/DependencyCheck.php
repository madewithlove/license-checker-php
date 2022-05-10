<?php

declare(strict_types=1);

namespace LicenseChecker\Commands\Output;

use LicenseChecker\Dependency;

final class DependencyCheck
{
    private bool $isAllowed = true;

    /**
     * @var Dependency[]
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
        $dependencyCheck->causedBy[] = new Dependency($dependency, $license);

        return $dependencyCheck;
    }

    public function renderNameWithLicense(): string
    {
        if (empty($this->dependency->license)) {
            return $this->dependency->name;
        }

        return $this->dependency->name . ' [' . $this->dependency->license . ']';
    }

    public function isAllowed(): bool
    {
        return $this->isAllowed;
    }

    /**
     * @return Dependency[]
     */
    public function getCausedBy(): array
    {
        return $this->causedBy;
    }
}
