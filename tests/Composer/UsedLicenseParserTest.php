<?php

namespace LicenseChecker\Tests\Composer;

use LicenseChecker\Composer\UsedLicensesParser;
use LicenseChecker\Composer\UsedLicensesRetriever;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UsedLicenseParserTest extends TestCase
{
    /**
     * @var MockObject & UsedLicensesRetriever
     */
    private MockObject $licenseRetriever;
    private UsedLicensesParser $usedLicensesParser;

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

        $this->licenseRetriever->method('getJsonDecodedComposerLicenses')->willReturn($this->getJsonData());

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

        $this->licenseRetriever->method('getJsonDecodedComposerLicenses')->willReturn($this->getJsonData());

        $this->assertEquals(
            $expected,
            $this->usedLicensesParser->countPackagesByLicense()
        );
    }

	/**
	 * @return array{dependencies:array<string,array{version:string,license:list<string>}>}
	 */
    private function getJsonData(): array
    {
		/** @var array{dependencies:array<string,array{version:string,license:list<string>}>} $jsonDecoded */
		$jsonDecoded = json_decode('
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
}', true);
		return $jsonDecoded;
    }
}
