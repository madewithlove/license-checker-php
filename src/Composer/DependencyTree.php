<?php

namespace LicenseChecker\Composer;

use LicenseChecker\Dependency;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DependencyTree
{
    private function getJson(): string
    {
        $process = new Process(['composer', 'show', '-t', '-f', 'json']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    /**
     * @return Dependency[]
     */
    public function getDependencies(): array
    {
        $dependencies = [];
        $decodedJson = json_decode($this->getJson(), true);
        foreach ($decodedJson['installed'] as $package) {
            $dependency = new Dependency($package['name']);
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
     * @param array $subTree
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
                $subDependencies = array_merge($subDependencies, $this->getSubDependencies($subTreeItem['requires']));
            }
        }

        return array_values(array_unique($subDependencies));
    }
}
