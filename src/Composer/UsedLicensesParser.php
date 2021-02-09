<?php

namespace LicenseChecker\Composer;

class UsedLicensesParser
{
    public function __construct(
        private UsedLicensesRetriever $retriever
    ) {}

    /**
     * @return string[]
     */
    public function parseLicenses(): array
    {
        $licenses = [];
        $decodedJson = json_decode($this->retriever->getComposerLicenses(), true);
        foreach ($decodedJson['dependencies'] as $dependency) {
            if (isset($dependency['license'][0])) {
                $licenses[] = $dependency['license'][0];
            }
        }

        sort($licenses);

        return array_values(array_unique($licenses));
    }

    /**
     * @return string[]
     */
    public function getPackagesWithLicense(string $license): array
    {
        $packages = [];
        $decodedJson = json_decode($this->retriever->getComposerLicenses(), true);

        foreach ($decodedJson['dependencies'] as $packageName => $licenseInfo) {
            if ($licenseInfo['license'][0] === $license) {
                $packages[] = $packageName;
            }
        }

        return $packages;
    }

    /**
     * @return array<array-key, int>
     */
    public function countPackagesByLicense(): array
    {
        $licenses = [];
        $decodedJson = json_decode($this->retriever->getComposerLicenses(), true);
        foreach ($decodedJson['dependencies'] as $dependency) {
            if (isset($dependency['license'][0])) {
                $licenseName = $dependency['license'][0];
                if (!isset($licenses[$licenseName])) {
                    $licenses[$licenseName] = 0;
                }
                $licenses[$licenseName]++;
            }
        }

        arsort($licenses, SORT_NUMERIC);

        return $licenses;
    }
}
