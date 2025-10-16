<?php

declare(strict_types=1);

namespace LicenseChecker\Tests\Output;

use LicenseChecker\Output\JsonOutputFormatter;
use LicenseChecker\Tests\Fakes\FakeDependency;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use LicenseChecker\Commands\Output\DependencyCheck;

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

        $depA = new FakeDependency('laravel/framework', 'MIT');
        $depB = new FakeDependency('phpunit/phpunit', 'BSD-3-Clause');

        /** @psalm-suppress ArgumentTypeCoercion */
        $formatter->format([
            (object)['dependency' => $depA],
            (object)['dependency' => $depB],
        ]);

        $json = $output->fetch();
        $decoded = json_decode($json, true);

        $this->assertJson($json);
        $this->assertSame([
            'laravel/framework' => 'MIT',
            'phpunit/phpunit' => 'BSD-3-Clause',
        ], $decoded);
    }

    public function testThrowsExceptionWhenJsonEncodingFails(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Failed to encode JSON/');

        [$formatter] = $this->createFormatter();

        /** @psalm-suppress ArgumentTypeCoercion */
        $invalid = [(object)['dependency' => fopen('php://temp', 'r')]];
        /** @psalm-suppress ArgumentTypeCoercion */
        $formatter->format($invalid);
    }
}
