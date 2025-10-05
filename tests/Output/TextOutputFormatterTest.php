<?php

declare(strict_types=1);

namespace LicenseChecker\Tests\Output;

use LicenseChecker\Output\TextOutputFormatter;
use PHPUnit\Framework\TestCase;

final class TextOutputFormatterTest extends TestCase
{
    public function testFormatsLicensesAsText(): void
    {
        $data = [
            'laravel/framework' => 'MIT',
            'phpunit/phpunit'   => 'BSD-3-Clause',
        ];

        $formatter = new TextOutputFormatter();
        $output = $formatter->format($data);

        $this->assertStringContainsString('laravel/framework: MIT', $output);
        $this->assertStringContainsString('phpunit/phpunit: BSD-3-Clause', $output);
        $this->assertFalse($this->isJsonString($output), 'Text output must not be JSON');
    }

    private function isJsonString(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
