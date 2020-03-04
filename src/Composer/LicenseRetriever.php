<?php

namespace LicenseChecker\Composer;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class LicenseRetriever
{
    public function getComposerLicenses(string $composerJsonPath): string
    {
        $process = new Process(['composer', 'license', '-f', 'json'], $composerJsonPath);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }
}
