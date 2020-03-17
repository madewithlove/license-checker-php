<?php

namespace LicenseChecker\Composer;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UsedLicenseParserTest extends TestCase
{
    /**
     * @var UsedLicensesParser
     */
    private $usedLicensesParser;

    /**
     * @var LicenseRetriever | MockObject
     */
    private $licenseRetriever;

    protected function setUp(): void
    {
        $this->licenseRetriever = $this->createMock(LicenseRetriever::class);
        $this->usedLicensesParser = new UsedLicensesParser($this->licenseRetriever);
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
            'BAR',
            'BAZ',
            'FOO',
        ];

        $this->licenseRetriever->method('getComposerLicenses')->willReturn($input);

        $this->assertEquals(
            $expected,
            $this->usedLicensesParser->parseLicenses()
        );

    }
}
