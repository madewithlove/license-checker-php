<?php

namespace LicenseChecker\Commands\Output;

final class CauseOfFailure
{
    public function __construct(
        private string $name,
        private string $license
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getLicense(): string
    {
        return $this->license;
    }
}
