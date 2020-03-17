<?php

namespace LicenseChecker;

class Dependency
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $subDependencies = [];

    public function __construct(string $name)
    {
        $this->name = $name;
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
        return array_search($dependency, $this->getDependencies()) !== false;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
