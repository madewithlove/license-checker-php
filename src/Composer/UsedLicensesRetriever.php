<?php

namespace LicenseChecker\Composer;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class UsedLicensesRetriever
{
    private static string $output = '';

	public function getComposerLicenses(bool $noDev = false): string
    {
        if (!empty(self::$output)) {
            return self::$output;
        }

        $noDevArguments = $noDev ? ['--no-dev'] : [];

        $process = new Process(array_merge(['composer', 'license', '-f', 'json'], $noDevArguments));
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        self::$output = $process->getOutput();

        return self::$output;
    }

	/**
	 * @return array{dependencies:array<string,array{version:string,license:list<string>}>}
	 */
    public function getJsonDecodedComposerLicenses(bool $noDev = false): array
	{
		/** @var array{dependencies:array<string,array{version:string,license:list<string>}>} $jsonDecoded */
		$jsonDecoded = json_decode($this->getComposerLicenses($noDev), true);
		return $jsonDecoded;
	}
}
