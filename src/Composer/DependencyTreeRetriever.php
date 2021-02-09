<?php

namespace LicenseChecker\Composer;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DependencyTreeRetriever
{
    private static string $output = '';

    public function getDependencyTree(): string
    {
        if (!empty(self::$output)) {
            return self::$output;
        }

        $process = new Process(['composer', 'show', '-t', '-f', 'json']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        self::$output = $process->getOutput();

        return self::$output;
    }
}
