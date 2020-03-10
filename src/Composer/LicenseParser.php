<?php

namespace LicenseChecker\Composer;

class LicenseParser
{
    /**
     * @param string $json
     * @return string[]
     */
    public function parseLicenses(string $json): array
    {
        $licenses = [];
        $decodedJson = json_decode($json, true);
        foreach ($decodedJson['dependencies'] as $dependency) {
            if (isset($dependency['license'][0])) {
                $licenses[] = $dependency['license'][0];
            }
        }

        return array_unique($licenses);
    }

    /**
     * @param string $json
     * @param string $license
     * @return string[]
     */
    public function getPackagesWithLicense(string $json, string $license): array
    {
        $packages = [];
        $decodedJson = json_decode($json, true);

        foreach ($decodedJson['dependencies'] as $packageName => $licenseInfo) {
            if ($licenseInfo['license'][0] === $license) {
                $packages[] = $packageName;
            }
        }

        return $packages;
    }
}
