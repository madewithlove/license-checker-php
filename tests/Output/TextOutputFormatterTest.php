<?php

declare(strict_types=1);

namespace LicenseChecker\Tests\Output;

use LicenseChecker\Commands\Output\DependencyCheck;
use LicenseChecker\Dependency;
use LicenseChecker\Output\TextOutputFormatter;
use LicenseChecker\Tests\Fakes\FakeTableRenderer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

final class TextOutputFormatterTest extends TestCase
{
    /**
     * @return array{0: \LicenseChecker\Output\TextOutputFormatter, 1: \Symfony\Component\Console\Output\BufferedOutput}
     */
    private function createFormatter(): array
    {
        $output = new BufferedOutput();
        $io = new SymfonyStyle(new ArrayInput([]), $output);
        $tableRenderer = new FakeTableRenderer();

        $formatter = new TextOutputFormatter($io, $tableRenderer);
        return [$formatter, $output];
    }

    public function testFormatsLicensesAsText(): void
    {
        [$formatter, $output] = $this->createFormatter();

        $formatter->format([
            new DependencyCheck(new Dependency('laravel/framework', 'MIT'), true),
            new DependencyCheck(new Dependency('phpunit/phpunit', 'BSD-3-Clause'), false),
        ]);

        $text = $output->fetch();

        $this->assertStringContainsString('laravel/framework: MIT', $text);
        $this->assertStringContainsString('phpunit/phpunit: BSD-3-Clause', $text);
        $this->assertFalse($this->isJsonString($text), 'Output must not be JSON');
    }

    private function isJsonString(string $string): bool
    {
        return json_decode($string, true) !== null && json_last_error() === JSON_ERROR_NONE;
    }
}
