<?php

declare(strict_types=1);

namespace LicenseChecker\Tests\Output;

use LicenseChecker\Output\JsonOutputFormatter;
use PHPUnit\Framework\TestCase;

final class JsonOutputFormatterTest extends TestCase
{
    public function testFormatsLicensesAsJson(): void
    {
        $data = [
            'laravel/framework' => 'MIT',
            'phpunit/phpunit'   => 'BSD-3-Clause',
        ];

        $formatter = new JsonOutputFormatter();
        $json = $formatter->format($data);

        $this->assertJson($json, 'Output should be valid JSON');
        $decoded = json_decode($json, true);

        $this->assertSame($data, $decoded, 'Decoded JSON should match input array');
    }

    public function testThrowsExceptionWhenJsonEncodingFails(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to encode JSON');

        $formatter = new JsonOutputFormatter();
        $invalid = ['stream' => fopen('php://temp', 'r')];

        $formatter->format($invalid);
    }
}
