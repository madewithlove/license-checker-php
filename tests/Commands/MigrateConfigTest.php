<?php

declare(strict_types=1);

namespace LicenseChecker\Tests\Commands;

use LicenseChecker\Commands\MigrateConfig;
use LicenseChecker\Configuration\LicenseConfigMode;
use LicenseChecker\Configuration\LicenseConfigurationParser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class MigrateConfigTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/license-checker-migrate-' . uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        $files = glob($this->tempDir . '/{.,}*', GLOB_BRACE) ?: [];
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($this->tempDir);
    }

    private function createCommandTester(): CommandTester
    {
        $parser = new LicenseConfigurationParser($this->tempDir);
        $command = new MigrateConfig($parser, $this->tempDir);

        $application = new Application();
        $application->addCommand($command);

        return new CommandTester($application->find('migrate-config'));
    }

    #[Test]
    public function itMigratesOldFormatToNewAllowedFormat(): void
    {
        file_put_contents($this->tempDir . '/.allowed-licenses', "- MIT\n- BSD-3-Clause\n");

        $tester = $this->createCommandTester();
        $tester->execute([]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('migrated successfully', $tester->getDisplay());
        $this->assertFileExists($this->tempDir . '/.license-checker.yml');

        $parser = new LicenseConfigurationParser($this->tempDir);
        $config = $parser->parse('.license-checker.yml');
        $this->assertSame(LicenseConfigMode::Allowed, $config->mode);
        $this->assertEquals(['BSD-3-Clause', 'MIT'], $config->licenses);
    }

    #[Test]
    public function itRemovesOldFileWhenFlagIsSet(): void
    {
        file_put_contents($this->tempDir . '/.allowed-licenses', "- MIT\n");

        $tester = $this->createCommandTester();
        $tester->execute(['--remove-old' => true]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertFileDoesNotExist($this->tempDir . '/.allowed-licenses');
        $this->assertFileExists($this->tempDir . '/.license-checker.yml');
    }

    #[Test]
    public function itFailsWhenOldFileDoesNotExist(): void
    {
        $tester = $this->createCommandTester();
        $tester->execute([]);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('not found', $tester->getDisplay());
    }

    #[Test]
    public function itFailsWhenNewFileAlreadyExists(): void
    {
        file_put_contents($this->tempDir . '/.allowed-licenses', "- MIT\n");
        file_put_contents($this->tempDir . '/.license-checker.yml', "allowed:\n  - MIT\n");

        $tester = $this->createCommandTester();
        $tester->execute([]);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('already exists', $tester->getDisplay());
    }
}
