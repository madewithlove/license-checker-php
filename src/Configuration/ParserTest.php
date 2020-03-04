<?php

namespace LicenseChecker\Configuration;

use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /**
     * @test
     */
    public function canParseConfiguration(): void
    {
        $parser = new Parser();
        $allowedLicenses = $parser->getAllowedLicenses();
        $expected = [
            'MIT',
            'BSD-3-Clause',
            'Apache-2',
        ];

        $this->assertEquals($expected, $allowedLicenses);
    }
}
