<?php

namespace LicenseChecker\Configuration;

use PHPUnit\Framework\TestCase;

class AllowedLicensesParserTest extends TestCase
{
    /**
     * @test
     */
    public function canParseConfiguration(): void
    {
        $parser = new AllowedLicensesParser();
        $allowedLicenses = $parser->getAllowedLicenses(__DIR__ );
        $expected = [
            'MIT',
            'BSD-3-Clause',
            'Apache-2',
            'New BSD License',
        ];

        $this->assertEquals($expected, $allowedLicenses);
    }
}
