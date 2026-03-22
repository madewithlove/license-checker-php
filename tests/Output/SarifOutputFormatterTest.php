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

    /**
     * @return array<mixed>
     */
    private function decodeOutput(BufferedOutput $output): array
    {
        $json = $output->fetch();
        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);

        return $decoded;
    }

    /**
     * @param array<mixed> $sarif
     * @return array<mixed>
     */
    private function getResults(array $sarif): array
    {
        $runs = $sarif['runs'];
        $this->assertIsArray($runs);
        $run = $runs[0];
        $this->assertIsArray($run);
        $results = $run['results'];
        $this->assertIsArray($results);

        return $results;
    }

    public function testOutputsValidSarifStructure(): void
    {
        [$formatter, $output] = $this->createFormatter();

        $formatter->format([
            new DependencyCheck(new Dependency('laravel/framework', 'MIT'), true),
        ]);

        $sarif = $this->decodeOutput($output);
        $this->assertSame('2.1.0', $sarif['version']);

        $runs = $sarif['runs'];
        $this->assertIsArray($runs);
        $this->assertCount(1, $runs);

        $run = $runs[0];
        $this->assertIsArray($run);
        $tool = $run['tool'];
        $this->assertIsArray($tool);
        $driver = $tool['driver'];
        $this->assertIsArray($driver);
        $this->assertSame('license-checker', $driver['name']);
    }

    public function testProducesNoResultsWhenAllAllowed(): void
    {
        [$formatter, $output] = $this->createFormatter();

        $formatter->format([
            new DependencyCheck(new Dependency('laravel/framework', 'MIT'), true),
            new DependencyCheck(new Dependency('symfony/console', 'MIT'), true),
        ]);

        $this->assertSame([], $this->getResults($this->decodeOutput($output)));
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

        $results = $this->getResults($this->decodeOutput($output));
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertIsArray($result);
        $this->assertSame('license-not-allowed', $result['ruleId']);
        $this->assertSame('error', $result['level']);

        $message = $result['message'];
        $this->assertIsArray($message);
        $text = $message['text'];
        $this->assertIsString($text);
        $this->assertStringContainsString('"bad/package"', $text);
        $this->assertStringContainsString('GPL-3.0', $text);
        $this->assertStringNotContainsString('requires', $text);
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

        $results = $this->getResults($this->decodeOutput($output));
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertIsArray($result);

        $message = $result['message'];
        $this->assertIsArray($message);
        $text = $message['text'];
        $this->assertIsString($text);
        $this->assertStringContainsString('"my/package"', $text);
        $this->assertStringContainsString('"bad/subdep"', $text);
        $this->assertStringContainsString('GPL-3.0', $text);
        $this->assertStringContainsString('requires', $text);

        $locations = $result['locations'];
        $this->assertIsArray($locations);
        $location = $locations[0];
        $this->assertIsArray($location);

        $physicalLocation = $location['physicalLocation'];
        $this->assertIsArray($physicalLocation);
        $artifactLocation = $physicalLocation['artifactLocation'];
        $this->assertIsArray($artifactLocation);
        $this->assertSame('composer.json', $artifactLocation['uri']);
        $this->assertSame('%SRCROOT%', $artifactLocation['uriBaseId']);

        $logicalLocations = $location['logicalLocations'];
        $this->assertIsArray($logicalLocations);
        $logicalLocation = $logicalLocations[0];
        $this->assertIsArray($logicalLocation);
        $this->assertSame('my/package', $logicalLocation['name']);
        $this->assertSame('package', $logicalLocation['kind']);
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

        $this->assertCount(2, $this->getResults($this->decodeOutput($output)));
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

        $results = $this->getResults($this->decodeOutput($output));
        $result = $results[0];
        $this->assertIsArray($result);

        $locations = $result['locations'];
        $this->assertIsArray($locations);
        $location = $locations[0];
        $this->assertIsArray($location);

        $logicalLocations = $location['logicalLocations'];
        $this->assertIsArray($logicalLocations);
        $logicalLocation = $logicalLocations[0];
        $this->assertIsArray($logicalLocation);
        $this->assertSame('my/root', $logicalLocation['name']);

        $physicalLocation = $location['physicalLocation'];
        $this->assertIsArray($physicalLocation);
        $artifactLocation = $physicalLocation['artifactLocation'];
        $this->assertIsArray($artifactLocation);
        $this->assertSame('composer.json', $artifactLocation['uri']);
    }
}
