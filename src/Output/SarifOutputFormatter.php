<?php

declare(strict_types=1);

namespace LicenseChecker\Output;

use JsonException;
use LicenseChecker\Commands\Output\DependencyCheck;
use LicenseChecker\Composer\ComposerJsonLineMapper;
use LicenseChecker\Dependency;
use RuntimeException;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SarifOutputFormatter implements OutputFormatterInterface
{
    public function __construct(
        private readonly SymfonyStyle $io,
        private readonly ComposerJsonLineMapper $lineMapper,
    ) {
    }

    public function format(array $dependencyChecks): void
    {
        $results = [];

        foreach ($dependencyChecks as $check) {
            if ($check->isAllowed) {
                continue;
            }

            foreach ($check->causedBy as $violatingDependency) {
                $results[] = $this->buildResult($check->dependency, $violatingDependency);
            }
        }

        $sarif = [
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

        try {
            $json = json_encode($sarif, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        } catch (JsonException $e) {
            throw new RuntimeException('Failed to encode SARIF JSON: ' . $e->getMessage(), 0, $e);
        }

        $this->io->writeln($json);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildResult(Dependency $rootDependency, Dependency $violatingDependency): array
    {
        $isDirectViolation = $rootDependency->is($violatingDependency->getName());

        $message = $isDirectViolation
            ? sprintf(
                '"%s" uses the disallowed license "%s"',
                $rootDependency->getName(),
                $violatingDependency->getLicense(),
            )
            : sprintf(
                '"%s" requires "%s" which uses the disallowed license "%s"',
                $rootDependency->getName(),
                $violatingDependency->getName(),
                $violatingDependency->getLicense(),
            );

        $physicalLocation = [
            'artifactLocation' => [
                'uri' => 'composer.json',
                'uriBaseId' => '%SRCROOT%',
            ],
        ];

        $lineNumber = $this->lineMapper->getLineNumber($rootDependency->getName());

        if ($lineNumber !== null) {
            $physicalLocation['region'] = [
                'startLine' => $lineNumber,
            ];
        }

        return [
            'ruleId' => 'license-not-allowed',
            'level' => 'error',
            'message' => [
                'text' => $message,
            ],
            'locations' => [
                [
                    'physicalLocation' => $physicalLocation,
                    'logicalLocations' => [
                        [
                            'name' => $rootDependency->getName(),
                            'kind' => 'package',
                        ],
                    ],
                ],
            ],
        ];
    }
}
