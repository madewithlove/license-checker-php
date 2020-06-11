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
     * @var UsedLicensesRetriever | MockObject
     */
    private $licenseRetriever;

    protected function setUp(): void
    {
        $this->licenseRetriever = $this->createMock(UsedLicensesRetriever::class);
        $this->usedLicensesParser = new UsedLicensesParser($this->licenseRetriever);
    }

    /**
     * @test
     */
    public function canParseLicensesFromJson(): void
    {
        $expected = [
            'BAR',
            'BAZ',
            'FOO',
        ];

        $this->licenseRetriever->method('getComposerLicenses')->willReturn($this->getJsonData());

        $this->assertEquals(
            $expected,
            $this->usedLicensesParser->parseLicenses()
        );

    }

    /**
     * @test
     */
    public function canCountDependenciesByLicense(): void
    {
        $expected = [
            'FOO' => 2,
            'BAR' => 2,
            'BAZ' => 1,
        ];

        $this->licenseRetriever->method('getComposerLicenses')->willReturn($this->getJsonData());

        $this->assertEquals(
            $expected,
            $this->usedLicensesParser->countPackagesByLicense()
        );
    }

    private function getJsonData(): string
    {
        return '
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
    }
}
