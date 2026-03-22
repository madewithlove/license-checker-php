<?php

declare(strict_types=1);

namespace LicenseChecker\Tests\Output;

use LicenseChecker\Commands\Output\DependencyCheck;
use LicenseChecker\Dependency;
use LicenseChecker\Output\SarifOutputFormatter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SarifOutputFormatterTest extends TestCase
{
    /**
     * @return array{0: SarifOutputFormatter, 1: BufferedOutput}
     */
    private function createFormatter(): array
    {
        $output = new BufferedOutput();
        $io = new SymfonyStyle(new ArrayInput([]), $output);
        $formatter = new SarifOutputFormatter($io);

        return [$formatter, $output];
    }

    public function testOutputsValidSarifStructure(): void
    {
        [$formatter, $output] = $this->createFormatter();

        $formatter->format([
            new DependencyCheck(new Dependency('laravel/framework', 'MIT'), true),
        ]);

        $json = $output->fetch();
        $decoded = json_decode($json, true);

        $this->assertJson($json);
        $this->assertSame('2.1.0', $decoded['version']);
        $this->assertArrayHasKey('runs', $decoded);
        $this->assertCount(1, $decoded['runs']);
        $this->assertSame('license-checker', $decoded['runs'][0]['tool']['driver']['name']);
    }

    public function testProducesNoResultsWhenAllAllowed(): void
    {
        [$formatter, $output] = $this->createFormatter();

        $formatter->format([
            new DependencyCheck(new Dependency('laravel/framework', 'MIT'), true),
            new DependencyCheck(new Dependency('symfony/console', 'MIT'), true),
        ]);

        $json = $output->fetch();
        $decoded = json_decode($json, true);

        $this->assertSame([], $decoded['runs'][0]['results']);
    }

    public function testProducesResultForDirectLicenseViolation(): void
    {
        [$formatter, $output] = $this->createFormatter();

        $formatter->format([
            new DependencyCheck(
                new Dependency('bad/package', 'GPL-3.0'),
                false,
                [new Dependency('bad/package', 'GPL-3.0')],
            ),
        ]);

        $json = $output->fetch();
        $decoded = json_decode($json, true);
        $results = $decoded['runs'][0]['results'];

        $this->assertCount(1, $results);
        $this->assertSame('license-not-allowed', $results[0]['ruleId']);
        $this->assertSame('error', $results[0]['level']);
        $this->assertStringContainsString('"bad/package"', $results[0]['message']['text']);
        $this->assertStringContainsString('GPL-3.0', $results[0]['message']['text']);
        $this->assertStringNotContainsString('requires', $results[0]['message']['text']);
    }

    public function testProducesResultForSubDependencyViolation(): void
    {
        [$formatter, $output] = $this->createFormatter();

        $formatter->format([
            new DependencyCheck(
                new Dependency('my/package', 'MIT'),
                false,
                [new Dependency('bad/subdep', 'GPL-3.0')],
            ),
        ]);

        $json = $output->fetch();
        $decoded = json_decode($json, true);
        $results = $decoded['runs'][0]['results'];

        $this->assertCount(1, $results);
        $this->assertStringContainsString('"my/package"', $results[0]['message']['text']);
        $this->assertStringContainsString('"bad/subdep"', $results[0]['message']['text']);
        $this->assertStringContainsString('GPL-3.0', $results[0]['message']['text']);
        $this->assertStringContainsString('requires', $results[0]['message']['text']);
        $this->assertSame('composer.json', $results[0]['locations'][0]['physicalLocation']['artifactLocation']['uri']);
        $this->assertSame('%SRCROOT%', $results[0]['locations'][0]['physicalLocation']['artifactLocation']['uriBaseId']);
        $this->assertSame('my/package', $results[0]['locations'][0]['logicalLocations'][0]['name']);
        $this->assertSame('package', $results[0]['locations'][0]['logicalLocations'][0]['kind']);
    }

    public function testProducesOneResultPerViolatingDependency(): void
    {
        [$formatter, $output] = $this->createFormatter();

        $formatter->format([
            new DependencyCheck(
                new Dependency('my/package', 'MIT'),
                false,
                [
                    new Dependency('bad/subdep1', 'GPL-3.0'),
                    new Dependency('bad/subdep2', 'AGPL-3.0'),
                ],
            ),
        ]);

        $json = $output->fetch();
        $decoded = json_decode($json, true);

        $this->assertCount(2, $decoded['runs'][0]['results']);
    }

    public function testLocationsAlwaysPointToRootDependency(): void
    {
        [$formatter, $output] = $this->createFormatter();

        $formatter->format([
            new DependencyCheck(
                new Dependency('my/root', 'MIT'),
                false,
                [new Dependency('transitive/dep', 'GPL-3.0')],
            ),
        ]);

        $json = $output->fetch();
        $decoded = json_decode($json, true);
        $result = $decoded['runs'][0]['results'][0];

        $this->assertSame('my/root', $result['locations'][0]['logicalLocations'][0]['name']);
        $this->assertSame('composer.json', $result['locations'][0]['physicalLocation']['artifactLocation']['uri']);
    }
}
