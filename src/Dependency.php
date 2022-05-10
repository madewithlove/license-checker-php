<?php

declare(strict_types=1);

namespace LicenseChecker;

final class Dependency
{
    /**
     * @var string[]
     */
    private array $subDependencies = [];

    public function __construct(
        public readonly string $name,
        public readonly string $license,
    ) {
    }

    public function addDependency(string $dependency): self
    {
        if (!$this->hasDependency($dependency)) {
            $this->subDependencies[] = $dependency;
        }

        return $this;
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return $this->subDependencies;
    }

    public function hasDependency(string $dependency): bool
    {
        return array_search($dependency, $this->getDependencies(), true) !== false;
    }
}
