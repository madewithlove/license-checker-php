<?php

declare(strict_types=1);

namespace LicenseChecker\Tests\Configuration;

use LicenseChecker\Configuration\AllowedLicensesParser;
use PHPUnit\Framework\TestCase;

class AllowedLicensesParserTest extends TestCase
{
    /**
     * @test
     */
    public function canParseConfiguration(): void
    {
        $parser = new AllowedLicensesParser(__DIR__.'/data');
        $allowedLicenses = $parser->getAllowedLicenses(__DIR__.'/data');
        $expected = [
            'Apache-2',
            'BSD-3-Clause',
            'MIT',
            'New BSD License',
        ];

        $this->assertEquals($expected, $allowedLicenses);
    }
}
