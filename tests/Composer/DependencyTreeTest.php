<?php

declare(strict_types=1);

namespace LicenseChecker\Tests\Composer;

use LicenseChecker\Composer\DependencyTree;
use LicenseChecker\Composer\DependencyTreeRetriever;
use LicenseChecker\Composer\UsedLicensesParser;
use LicenseChecker\Dependency;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class DependencyTreeTest extends TestCase
{
    private Stub&DependencyTreeRetriever $retriever;
    private Stub&UsedLicensesParser $parser;
    private DependencyTree $dependencyTree;

    protected function setUp(): void
    {
        $this->retriever = $this->createStub(DependencyTreeRetriever::class);
        $this->parser = $this->createStub(UsedLicensesParser::class);
        $this->dependencyTree = new DependencyTree($this->retriever, $this->parser);
    }

    #[Test]
    public function itCanParseJsonFromComposer(): void
    {
        $this->retriever->method('getDependencyTree')->willReturn($this->getDependencyTree());

        $dependencies = $this->dependencyTree->getDependencies(false);
        $expected = [
            (new Dependency('direct/dependency', ''))
                ->addDependency('subdependency/one')
                ->addDependency('subdependency/two')
                ->addDependency('subdependency/three'),
        ];

        $this->assertEquals($expected, $dependencies);
    }

    private function getDependencyTree(): string
    {
        return '{
            "installed": [
                {
                    "name": "direct/dependency",
                    "version": "v0.1",
                    "description": "Some direct dependency",
                    "requires": [
                        {
                            "name": "subdependency/one",
                            "version": "^1.0"
                        },
                        {
                            "name": "subdependency/two",
                            "version": "^2.0",
                            "requires": [
                                {
                                    "name": "subdependency/three",
                                    "version": "^3.0"
                                }
                            ]
                        }
                    ]
                }
            ]
        }';
    }
}
