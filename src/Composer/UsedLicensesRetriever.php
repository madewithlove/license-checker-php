<?php

namespace LicenseChecker\Composer;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class UsedLicensesRetriever
{
    private static $output;

    public function getComposerLicenses(): string
    {
        if (!is_null(self::$output)) {
            return self::$output;
        }

        $process = new Process(['composer', 'license', '-f', 'json']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        self::$output = $process->getOutput();

        return self::$output;
    }
}
