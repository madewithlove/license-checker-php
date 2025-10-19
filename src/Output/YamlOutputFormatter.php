<?php

declare(strict_types=1);

namespace LicenseChecker\Output;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use LicenseChecker\Commands\Output\DependencyCheck;

final class YamlOutputFormatter implements OutputFormatterInterface
{
    public function __construct(
        private readonly SymfonyStyle $io,
    ) {
    }

    /**
     * @param DependencyCheck[] $dependencyChecks
     */
    public function format(array $dependencyChecks): string
    {
        $data = [];

        foreach ($dependencyChecks as $check) {
            $data[$check->dependency->getName()] = [
                'license'     => $check->dependency->getLicense(),
                'is_allowed'  => $check->isAllowed,
                'violations'  => array_map(
                    static fn($dep) => [
                        'package' => $dep->getName(),
                        'license' => $dep->getLicense(),
                    ],
                    $check->causedBy
                ),
            ];
        }

        $yaml = Yaml::dump($data, 2, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
        $this->io->writeln($yaml);
        return $yaml;
    }
}
