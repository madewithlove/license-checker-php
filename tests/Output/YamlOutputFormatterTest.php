<?php

declare(strict_types=1);

namespace Tests\Output;

use LicenseChecker\Output\YamlOutputFormatter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use LicenseChecker\Commands\Output\DependencyCheck;
use LicenseChecker\Dependency;

final class YamlOutputFormatterTest extends TestCase
{
    private function createFormatter(): YamlOutputFormatter
    {
        $io = new SymfonyStyle(new ArrayInput([]), new BufferedOutput());
        return new YamlOutputFormatter($io);
    }

    public function testFormatsLicensesAsYaml(): void
    {
        $formatter = $this->createFormatter();
        
        $yaml = $formatter->format([
            new DependencyCheck(new Dependency('laravel/framework', 'MIT'), true),
            new DependencyCheck(new Dependency('phpunit/phpunit', 'BSD-3-Clause'), false),
        ]);

        $this->assertNotEmpty($yaml);
        $this->assertStringContainsString('laravel/framework', $yaml);
        $this->assertStringContainsString('MIT', $yaml);
        $this->assertStringContainsString('phpunit/phpunit', $yaml);
        $this->assertStringContainsString('BSD-3-Clause', $yaml);
    }
}
