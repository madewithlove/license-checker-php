<?php

declare(strict_types=1);

namespace LicenseChecker\Composer;

use LicenseChecker\Dependency;

final readonly class DependencyTree
{
    public function __construct(
        private DependencyTreeRetriever $retriever,
        private UsedLicensesParser $parser,
    ) {
    }

    /**
     * @return Dependency[]
     */
    public function getDependencies(bool $noDev): array
    {
        $dependencies = [];
        /** @var array{installed:list<array{name:string,requires?:list<array{name:string,requires?:list<mixed>}>}>} $decodedJson */
        $decodedJson = json_decode($this->retriever->getDependencyTree($noDev), true);
        foreach ($decodedJson['installed'] as $package) {
            $license = $this->parser->getLicenseForPackage($package['name'], $noDev) ?? '';
            $dependency = new Dependency($package['name'], $license);
            if (isset($package['requires'])) {
                foreach ($this->getSubDependencies($package['requires']) as $subDependency) {
                    $dependency->addDependency($subDependency);
                }
            }
            $dependencies[] = $dependency;
        }

        return $dependencies;
    }

    /**
     * @param list<array{name:string,requires?:list<mixed>}> $subTree
     * @return string[]
     */
    private function getSubDependencies(array $subTree): array
    {
        $subDependencies = [];

        if (empty($subTree)) {
            return $subDependencies;
        }

        foreach ($subTree as $subTreeItem) {
            $subDependencies[] = $subTreeItem['name'];
            if (isset($subTreeItem['requires'])) {
                /** @var list<array{name:string,requires?:list<mixed>}> $requires */
                $requires = $subTreeItem['requires'];
                $subDependencies = array_merge($subDependencies, $this->getSubDependencies($requires));
            }
        }

        return array_values(array_unique($subDependencies));
    }
}
