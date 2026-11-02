<?php

declare(strict_types=1);

namespace LicenseChecker\Configuration;

final readonly class LicenseConfiguration
{
    /**
     * @param list<string> $licenses
     * @param list<string> $exemptPackages
     */
    private function __construct(
        public LicenseConfigMode $mode,
        public array $licenses,
        public array $exemptPackages,
    ) {
    }

    /**
     * @param list<string> $licenses
     * @param list<string> $exemptPackages
     */
    public static function allowed(array $licenses, array $exemptPackages = []): self
    {
        sort($licenses);
        sort($exemptPackages);

        return new self(LicenseConfigMode::Allowed, $licenses, $exemptPackages);
    }

    /**
     * @param list<string> $licenses
     * @param list<string> $exemptPackages
     */
    public static function denied(array $licenses, array $exemptPackages = []): self
    {
        sort($licenses);
        sort($exemptPackages);

        return new self(LicenseConfigMode::Denied, $licenses, $exemptPackages);
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

    public function isExempt(string $package): bool
    {
        return in_array($package, $this->exemptPackages, true);
    }
}
