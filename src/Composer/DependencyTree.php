<?php

declare(strict_types=1);

namespace LicenseChecker\Composer;

use LicenseChecker\Dependency;

class DependencyTree
{
    public function __construct(
        private DependencyTreeRetriever $retriever
    ) {
    }

    /**
     * @return Dependency[]
     */
    public function getDependencies(bool $noDev): array
    {
        $dependencies = [];
        /** @var array{installed:list<array{name:string,requires?:array}>} $decodedJson */
        $decodedJson = json_decode($this->retriever->getDependencyTree($noDev), true);
        foreach ($decodedJson['installed'] as $package) {
            $dependency = new Dependency($package['name']);
            if (isset($package['requires'])) {
                /** @psalm-suppress MixedArgumentTypeCoercion */
                foreach ($this->getSubDependencies($package['requires']) as $subDependency) {
                    $dependency->addDependency($subDependency);
                }
            }
            $dependencies[] = $dependency;
        }

        return $dependencies;
    }

    /**
     * @param list<array{name:string,requires?:array}> $subTree
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
                /** @psalm-suppress MixedArgumentTypeCoercion */
                $subDependencies = array_merge($subDependencies, $this->getSubDependencies($subTreeItem['requires']));
            }
        }

        return array_values(array_unique($subDependencies));
    }
}
