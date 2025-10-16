<?php

declare(strict_types=1);

namespace LicenseChecker\Tests\Output;

use LicenseChecker\Output\JsonOutputFormatter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use LicenseChecker\Commands\Output\DependencyCheck;
use LicenseChecker\Dependency;

final class JsonOutputFormatterTest extends TestCase
{
    /**
     * @return array{0: \LicenseChecker\Output\JsonOutputFormatter, 1: \Symfony\Component\Console\Output\BufferedOutput}
     */
    private function createFormatter(): array
    {
        $output = new BufferedOutput();
        $io = new SymfonyStyle(new ArrayInput([]), $output);
        $formatter = new JsonOutputFormatter($io);

        return [$formatter, $output];
    }

    public function testFormatsLicensesAsJson(): void
    {
        [$formatter, $output] = $this->createFormatter();

        $formatter->format([
            new DependencyCheck(new Dependency('laravel/framework', 'MIT'), true),
            new DependencyCheck(new Dependency('phpunit/phpunit', 'BSD-3-Clause'), false),
        ]);

        $json = $output->fetch();
        $decoded = json_decode($json, true);

        $this->assertJson($json);
        $this->assertSame([
            'laravel/framework' => [
                'license' => 'MIT',
                'is_allowed' => true,
            ],
            'phpunit/phpunit' => [
                'license' => 'BSD-3-Clause',
                'is_allowed' => false,
            ],
        ], $decoded);
    }
}
