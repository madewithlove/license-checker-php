<?php

declare(strict_types=1);

namespace LicenseChecker\Tests\Composer;

use LicenseChecker\Composer\UsedLicensesParser;
use LicenseChecker\Composer\UsedLicensesRetriever;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UsedLicenseParserTest extends TestCase
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

    #[Test]
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
            $this->usedLicensesParser->parseLicenses(false)
        );
    }

    #[Test]
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
            $this->usedLicensesParser->countPackagesByLicense(false)
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

    #[Test]
    public function canParseDependenciesWithoutLicense(): void
    {
        $expected = [];

        $this->licenseRetriever->method('getComposerLicenses')->willReturn($this->getJsonDataWithoutLicenses());

        $this->assertEquals(
            $expected,
            $this->usedLicensesParser->parseLicenses(false)
        );
    }

    #[Test]
    public function canGetPackagesWithLicenseWhenThereAreDependenciesWithoutLicenses(): void
    {
        $expected = [];

        $this->licenseRetriever->method('getComposerLicenses')->willReturn($this->getJsonDataWithoutLicenses());

        $this->assertEquals(
            $expected,
            $this->usedLicensesParser->getPackagesWithLicense('FOO', false)
        );
    }

    /**
     * @return array{dependencies:array<string,array{version:string,license:list<string>}>}
     */
    private function getJsonDataWithoutLicenses(): array
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
            "license": []
        },
        "other/dependency": {
            "version": "v5.0.5",
            "license": []
        },
        "yet/another-dependency": {
            "version": "v1.14.0",
            "license": []
        },
        "repeated/license-for-dependency": {
            "version": "v1.14.0",
            "license": []
        },
        "another/repeated-license": {
            "version": "v5.0.5",
            "license": []
        }
    }
}', true);
        return $jsonDecoded;
    }
}
