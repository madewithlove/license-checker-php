<?php

namespace LicenseChecker\Commands\Output;

final class CauseOfFailure
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $license;

    public function __construct(string $name, string $license)
    {
        $this->name = $name;
        $this->license = $license;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLicense(): string
    {
        return $this->license;
    }
}
