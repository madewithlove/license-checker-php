<?php

declare(strict_types=1);

namespace LicenseChecker\Tests\Fakes;

final class FakeDependency
{
    public function __construct(
        private string $name,
        private string $license,
    ) {}

     /** @psalm-suppress PossiblyUnusedMethod */
    public function getName(): string
    {
        return $this->name;
    }

     /** @psalm-suppress PossiblyUnusedMethod */
    public function getLicense(): string
    {
        return $this->license;
    }
}
