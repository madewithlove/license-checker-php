<?php

declare(strict_types=1);

namespace LicenseChecker\Composer;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class UsedLicensesRetriever
{
    /** @var ?array{dependencies:array<string,array{version:string,license:list<string>}>} */
    private static ?array $output = null;

    /**
     * @return array{dependencies:array<string,array{version:string,license:list<string>}>}
     */
    public function getComposerLicenses(bool $noDev): array
    {
        if (self::$output) {
            return self::$output;
        }

        $noDevArguments = $noDev ? ['--no-dev'] : [];

        $process = new Process(array_merge(['composer', 'license', '-f', 'json'], $noDevArguments));
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        /** @var array{dependencies:array<string,array{version:string,license:list<string>}>} */
        self::$output = json_decode($process->getOutput(), true);
        return self::$output;
    }
}
