<?php

namespace LicenseChecker\Composer;

use PHPUnit\Framework\TestCase;

class LicenseParserTest extends TestCase
{
    /**
     * @var LicenseParser
     */
    private $licenseParser;

    protected function setUp(): void
    {
        $this->licenseParser = new LicenseParser();
    }

    /**
     * @test
     */
    public function canParseLicensesFromJson(): void
    {
        $input = '
{
    "name": "madewithlove/licence-checker-php",
    "version": "dev-master",
    "license": [
        "MIT"
    ],
    "dependencies": {
        "some/dependency": {
            "version": "1.0.0",
            "license": [
                "FOO"
            ]
        },
        "other/dependency": {
            "version": "v5.0.5",
            "license": [
                "BAR"
            ]
        },
        "yet/another-dependency": {
            "version": "v1.14.0",
            "license": [
                "BAZ"
            ]
        },
        "repeated/license-for-dependency": {
            "version": "v1.14.0",
            "license": [
                "FOO"
            ]
        },
        "another/repeated-license": {
            "version": "v5.0.5",
            "license": [
                "BAR"
            ]
        }
    }
}';
        $expected = [
            'FOO',
            'BAR',
            'BAZ',
        ];

        $this->assertEquals(
            $expected,
            $this->licenseParser->parseLicenses($input)
        );

    }
}
