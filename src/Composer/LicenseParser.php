<?php

namespace LicenseChecker\Composer;

use phpDocumentor\Reflection\Types\Array_;

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
            $licenses[] = $dependency['license'][0];
        }

        return array_unique($licenses);
    }
}
