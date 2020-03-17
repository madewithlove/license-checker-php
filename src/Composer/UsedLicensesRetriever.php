<?php

namespace LicenseChecker\Composer;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class UsedLicensesRetriever
{
    public function getComposerLicenses(): string
    {
        $process = new Process(['composer', 'license', '-f', 'json']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }
}
