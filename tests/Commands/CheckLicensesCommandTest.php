<?php

declare(strict_types=1);

namespace LicenseChecker\Tests\Commands;

use LicenseChecker\Commands\CheckLicensesCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Command\Command;

final class CheckLicensesCommandTest extends TestCase
{
    private CommandTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $app = new Application();
        $app->add(new CheckLicensesCommand());

        $command = $app->find('check');
        $this->tester = new CommandTester($command);
    }

    public function testTextFormatOutput(): void
    {
        $exit = $this->tester->execute(['--format' => 'text']);
        $output = trim($this->tester->getDisplay());

        $this->assertSame(Command::SUCCESS, $exit);
        $this->assertStringContainsString('laravel/framework: MIT', $output);
        $this->assertFalse($this->isJsonString($output));
    }

    public function testJsonFormatOutput(): void
    {
        $exit = $this->tester->execute(['--format' => 'json']);
        $output = trim($this->tester->getDisplay());

        $this->assertSame(Command::SUCCESS, $exit);
        $this->assertJson($output);

        $data = json_decode($output, true);
        $this->assertArrayHasKey('laravel/framework', $data);
        $this->assertArrayHasKey('phpunit/phpunit', $data);
    }

    public function testDefaultFormatWhenOptionOmitted(): void
    {
        $exit = $this->tester->execute([]);
        $output = trim($this->tester->getDisplay());

        $this->assertSame(Command::SUCCESS, $exit);
        $this->assertStringContainsString('laravel/framework: MIT', $output);
        $this->assertFalse($this->isJsonString($output));
    }

    public function testInvalidFormatDefaultsToText(): void
    {
        $exit = $this->tester->execute(['--format' => 'unsupported']);
        $output = trim($this->tester->getDisplay());

        $this->assertSame(Command::SUCCESS, $exit);
        $this->assertStringContainsString('laravel/framework: MIT', $output);
        $this->assertFalse($this->isJsonString($output));
    }

    private function isJsonString(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
