<?php

namespace LicenseChecker\Configuration;

use Symfony\Component\Yaml\Yaml;

class AllowedLicensesParser
{
    private const ALLOWED_LICENSES_FILE = '.allowed-licenses';

    /**
     * @var string
     */
    private $path;

    public function __construct($path = __DIR__)
    {
        $this->path = $path;
    }

    /**
     * @return string[]
     */
    public function getAllowedLicenses(): array
    {
        $value = Yaml::parseFile($this->path . '/' . self::ALLOWED_LICENSES_FILE);
        return explode(' ', $value);
    }
}
