<?php

namespace LicenseChecker\Composer;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class UsedLicensesRetriever
{
    private static string $output = '';

	public function getComposerLicenses(): string
    {
        if (!empty(self::$output)) {
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

	/**
	 * @return array{dependencies:array<string,array{version:string,license:list<string>}>}
	 */
    public function getJsonDecodedComposerLicenses(): array
	{
		/** @var array{dependencies:array<string,array{version:string,license:list<string>}>} $jsonDecoded */
		$jsonDecoded = json_decode($this->getComposerLicenses(), true);
		return $jsonDecoded;
	}
}
