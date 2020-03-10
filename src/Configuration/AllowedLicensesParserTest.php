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
            'Apache-2',
            'BSD-3-Clause',
            'MIT',
            'New BSD License',
        ];

        $this->assertEquals($expected, $allowedLicenses);
    }
}
