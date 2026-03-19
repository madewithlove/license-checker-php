<?php

declare(strict_types=1);

namespace LicenseChecker\Tests\Configuration;

use LicenseChecker\Configuration\ConfigurationExists;
use LicenseChecker\Configuration\InvalidConfiguration;
use LicenseChecker\Configuration\LicenseConfigMode;
use LicenseChecker\Configuration\LicenseConfiguration;
use LicenseChecker\Configuration\LicenseConfigurationParser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LicenseConfigurationParserTest extends TestCase
{
    #[Test]
    public function itParsesAllowedConfiguration(): void
    {
        $parser = new LicenseConfigurationParser(__DIR__ . '/data');

        $config = $parser->parse('.license-checker-allowed.yml');

        $this->assertSame(LicenseConfigMode::Allowed, $config->mode);
        $this->assertEquals(['Apache-2', 'BSD-3-Clause', 'MIT', 'New BSD License'], $config->licenses);
    }

    #[Test]
    public function itParsesDeniedConfiguration(): void
    {
        $parser = new LicenseConfigurationParser(__DIR__ . '/data');

        $config = $parser->parse('.license-checker-denied.yml');

        $this->assertSame(LicenseConfigMode::Denied, $config->mode);
        $this->assertEquals(['AGPL-3.0', 'GPL-3.0'], $config->licenses);
    }

    #[Test]
    public function itThrowsWhenBothKeysArePresent(): void
    {
        $parser = new LicenseConfigurationParser(__DIR__ . '/data');

        $this->expectException(InvalidConfiguration::class);
        $this->expectExceptionMessage('either "allowed" or "denied", not both');

        $parser->parse('.license-checker-both.yml');
    }

    #[Test]
    public function itThrowsWhenNeitherKeyIsPresent(): void
    {
        $parser = new LicenseConfigurationParser(__DIR__ . '/data');

        $this->expectException(InvalidConfiguration::class);
        $this->expectExceptionMessage('must have an "allowed" or "denied" key');

        $parser->parse('.license-checker-neither.yml');
    }

    #[Test]
    public function itThrowsWhenOldFormatIsDetected(): void
    {
        $parser = new LicenseConfigurationParser(__DIR__ . '/data');

        $this->expectException(InvalidConfiguration::class);
        $this->expectExceptionMessage('old configuration format');

        $parser->parse('.allowed-licenses');
    }

    #[Test]
    public function itWritesAllowedConfiguration(): void
    {
        $dir = sys_get_temp_dir() . '/license-checker-test-' . uniqid();
        mkdir($dir);

        try {
            $parser = new LicenseConfigurationParser($dir);
            $config = LicenseConfiguration::allowed(['MIT', 'BSD-3-Clause']);

            $parser->writeConfiguration($config, 'test-config.yml');

            $written = (string) file_get_contents($dir . '/test-config.yml');
            $this->assertStringContainsString('allowed:', $written);
            $this->assertStringContainsString('- BSD-3-Clause', $written);
            $this->assertStringContainsString('- MIT', $written);

            $parsedBack = $parser->parse('test-config.yml');
            $this->assertSame(LicenseConfigMode::Allowed, $parsedBack->mode);
            $this->assertEquals(['BSD-3-Clause', 'MIT'], $parsedBack->licenses);
        } finally {
            array_map('unlink', glob($dir . '/*') ?: []);
            rmdir($dir);
        }
    }

    #[Test]
    public function itWritesDeniedConfiguration(): void
    {
        $dir = sys_get_temp_dir() . '/license-checker-test-' . uniqid();
        mkdir($dir);

        try {
            $parser = new LicenseConfigurationParser($dir);
            $config = LicenseConfiguration::denied(['GPL-3.0']);

            $parser->writeConfiguration($config, 'test-config.yml');

            $parsedBack = $parser->parse('test-config.yml');
            $this->assertSame(LicenseConfigMode::Denied, $parsedBack->mode);
            $this->assertEquals(['GPL-3.0'], $parsedBack->licenses);
        } finally {
            array_map('unlink', glob($dir . '/*') ?: []);
            rmdir($dir);
        }
    }

    #[Test]
    public function itThrowsWhenWritingToExistingFile(): void
    {
        $parser = new LicenseConfigurationParser(__DIR__ . '/data');

        $this->expectException(ConfigurationExists::class);

        $parser->writeConfiguration(
            LicenseConfiguration::allowed(['MIT']),
            '.license-checker-allowed.yml'
        );
    }
}
