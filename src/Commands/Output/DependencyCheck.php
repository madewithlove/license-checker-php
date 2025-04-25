<?php

declare(strict_types=1);

namespace LicenseChecker\Commands\Output;

use LicenseChecker\Dependency;

final readonly class DependencyCheck
{
    /**
     * @param Dependency[] $causedBy
     */
    public function __construct(
        public Dependency $dependency,
        public bool $isAllowed = true,
        public array $causedBy = [],
    ) {
    }

    public function addFailedDependency(string $dependency, string $license): self
    {
        return new self(
            $this->dependency,
            false,
            array_merge(
                $this->causedBy,
                [new Dependency($dependency, $license)],
            ),
        );
    }

    public function renderNameWithLicense(): string
    {
        return $this->dependency->renderNameWithLicense();
    }
}
