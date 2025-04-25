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
        private readonly string $name,
        private readonly string $license,
    ) {
    }

    public function is(string $name): bool
    {
        return $this->name === $name;
    }

    public function renderNameWithLicense(): string
    {
        if (empty($this->license)) {
            return $this->name;
        }

        return $this->name . ' [' . $this->license . ']';
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
        return in_array($dependency, $this->getDependencies(), true);
    }
}
