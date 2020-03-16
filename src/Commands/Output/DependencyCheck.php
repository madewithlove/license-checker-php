<?php

namespace LicenseChecker\Commands\Output;

final class DependencyCheck
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $isAllowed = true;

    /**
     * @var string[]
     */
    private $causedBy = [];

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function addFailedDependency($dependency, $license): self
    {
        $dependencyCheck = clone $this;
        $dependencyCheck->isAllowed = false;
        $dependencyCheck->causedBy[] = $dependency . ' [' . $license . ']';

        return $dependencyCheck;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isAllowed(): bool
    {
        return $this->isAllowed;
    }

    /**
     * @return string[]
     */
    public function getCausedBy(): array
    {
        return $this->causedBy;
    }
}
