<?php

declare(strict_types=1);

namespace LicenseChecker\Tests\Configuration;

use LicenseChecker\Configuration\LicenseConfigMode;
use LicenseChecker\Configuration\LicenseConfiguration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LicenseConfigurationTest extends TestCase
{
    #[Test]
    public function allowedModeFindViolationsReturnsUnlistedLicenses(): void
    {
        $config = LicenseConfiguration::allowed(['MIT', 'BSD-3-Clause']);

        $violations = $config->findViolations(['MIT', 'GPL-3.0', 'AGPL-3.0']);

        $this->assertEqualsCanonicalizing(['AGPL-3.0', 'GPL-3.0'], $violations);
    }

    #[Test]
    public function deniedModeFindViolationsReturnsListedLicenses(): void
    {
        $config = LicenseConfiguration::denied(['GPL-3.0', 'AGPL-3.0']);

        $violations = $config->findViolations(['MIT', 'GPL-3.0', 'BSD-3-Clause']);

        $this->assertEquals(['GPL-3.0'], $violations);
    }

    #[Test]
    public function allowedModeReturnsEmptyWhenNoViolations(): void
    {
        $config = LicenseConfiguration::allowed(['MIT', 'BSD-3-Clause']);

        $violations = $config->findViolations(['MIT', 'BSD-3-Clause']);

        $this->assertEmpty($violations);
    }

    #[Test]
    public function deniedModeReturnsEmptyWhenNoViolations(): void
    {
        $config = LicenseConfiguration::denied(['GPL-3.0']);

        $violations = $config->findViolations(['MIT', 'BSD-3-Clause']);

        $this->assertEmpty($violations);
    }

    #[Test]
    public function allowedModeWithEmptyListTreatsEverythingAsViolation(): void
    {
        $config = LicenseConfiguration::allowed([]);

        $violations = $config->findViolations(['MIT']);

        $this->assertEquals(['MIT'], $violations);
    }

    #[Test]
    public function deniedModeWithEmptyListTreatsEverythingAsAllowed(): void
    {
        $config = LicenseConfiguration::denied([]);

        $violations = $config->findViolations(['MIT']);

        $this->assertEmpty($violations);
    }

    #[Test]
    public function licensesAreSorted(): void
    {
        $config = LicenseConfiguration::allowed(['MIT', 'Apache-2', 'BSD-3-Clause']);

        $this->assertEquals(['Apache-2', 'BSD-3-Clause', 'MIT'], $config->licenses);
    }

    #[Test]
    public function modeIsSetCorrectly(): void
    {
        $allowed = LicenseConfiguration::allowed(['MIT']);
        $denied = LicenseConfiguration::denied(['GPL-3.0']);

        $this->assertSame(LicenseConfigMode::Allowed, $allowed->mode);
        $this->assertSame(LicenseConfigMode::Denied, $denied->mode);
    }
}
