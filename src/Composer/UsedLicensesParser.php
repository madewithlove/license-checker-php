<?php

namespace LicenseChecker\Composer;

class UsedLicensesParser
{
    /**
     * @var UsedLicensesRetriever
     */
    private $retriever;

    public function __construct(UsedLicensesRetriever $retriever)
    {
        $this->retriever = $retriever;
    }

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
     * @param string $license
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
}
