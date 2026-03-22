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
     * @param array<mixed> $results
     * @return array<mixed>
     */
    private function expectedSarif(array $results): array
    {
        return [
            '$schema' => 'https://raw.githubusercontent.com/oasis-tcs/sarif-spec/master/Schemata/sarif-schema-2.1.0.json',
            'version' => '2.1.0',
            'runs' => [
                [
                    'tool' => [
                        'driver' => [
                            'name' => 'license-checker',
                            'informationUri' => 'https://github.com/madewithlove/license-checker-php',
                            'rules' => [
                                [
                                    'id' => 'license-not-allowed',
                                    'name' => 'LicenseNotAllowed',
                                    'shortDescription' => [
                                        'text' => 'Dependency uses a license that is not allowed',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'results' => $results,
                ],
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    private function expectedResult(string $message, string $rootPackage): array
    {
        return [
            'ruleId' => 'license-not-allowed',
            'level' => 'error',
            'message' => ['text' => $message],
            'locations' => [
                [
                    'physicalLocation' => [
                        'artifactLocation' => [
                            'uri' => 'composer.json',
                            'uriBaseId' => '%SRCROOT%',
                        ],
                    ],
                    'logicalLocations' => [
                        [
                            'name' => $rootPackage,
                            'kind' => 'package',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testProducesNoResultsWhenAllAllowed(): void
    {
        [$formatter, $output] = $this->createFormatter();

        $formatter->format([
            new DependencyCheck(new Dependency('laravel/framework', 'MIT'), true),
            new DependencyCheck(new Dependency('symfony/console', 'MIT'), true),
        ]);

        $this->assertSame($this->expectedSarif([]), json_decode($output->fetch(), true));
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

        $this->assertSame(
            $this->expectedSarif([
                $this->expectedResult('"bad/package" uses the disallowed license "GPL-3.0"', 'bad/package'),
            ]),
            json_decode($output->fetch(), true),
        );
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

        $this->assertSame(
            $this->expectedSarif([
                $this->expectedResult('"my/package" requires "bad/subdep" which uses the disallowed license "GPL-3.0"', 'my/package'),
            ]),
            json_decode($output->fetch(), true),
        );
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

        $this->assertSame(
            $this->expectedSarif([
                $this->expectedResult('"my/package" requires "bad/subdep1" which uses the disallowed license "GPL-3.0"', 'my/package'),
                $this->expectedResult('"my/package" requires "bad/subdep2" which uses the disallowed license "AGPL-3.0"', 'my/package'),
            ]),
            json_decode($output->fetch(), true),
        );
    }
}
