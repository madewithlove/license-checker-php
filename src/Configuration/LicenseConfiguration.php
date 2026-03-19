<?php

declare(strict_types=1);

namespace LicenseChecker\Configuration;

final readonly class LicenseConfiguration
{
    /**
     * @param list<string> $licenses
     */
    private function __construct(
        public LicenseConfigMode $mode,
        public array $licenses,
    ) {
    }

    /**
     * @param list<string> $licenses
     */
    public static function allowed(array $licenses): self
    {
        sort($licenses);

        return new self(LicenseConfigMode::Allowed, $licenses);
    }

    /**
     * @param list<string> $licenses
     */
    public static function denied(array $licenses): self
    {
        sort($licenses);

        return new self(LicenseConfigMode::Denied, $licenses);
    }

    /**
     * @param list<string> $usedLicenses
     * @return list<string>
     */
    public function findViolations(array $usedLicenses): array
    {
        return array_values(match ($this->mode) {
            LicenseConfigMode::Allowed => array_diff($usedLicenses, $this->licenses),
            LicenseConfigMode::Denied => array_intersect($usedLicenses, $this->licenses),
        });
    }
}
