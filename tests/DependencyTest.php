<?php

declare(strict_types=1);

namespace LicenseChecker\Tests;

use LicenseChecker\Dependency;
use PHPUnit\Framework\TestCase;

class DependencyTest extends TestCase
{
    /**
     * @test
     */
    public function itKnowsItsName(): void
    {
        $dependency = new Dependency('foo');
        $this->assertEquals('foo', $dependency->getName());
    }

    /**
     * @test
     */
    public function itKnowsItsSubDependencies(): void
    {
        $expected = [
            'bar',
            'baz',
        ];

        $dependency = new Dependency('foo');
        $dependency->addDependency('bar');
        $dependency->addDependency('baz');

        $this->assertEquals($expected, $dependency->getDependencies());
        $this->assertFalse($dependency->hasDependency('foo'));
        $this->assertTrue($dependency->hasDependency('bar'));
        $this->assertTrue($dependency->hasDependency('baz'));
    }

    /**
     * @test
     */
    public function itIgnoresDuplicateSubDependencies(): void
    {
        $expected = [
            'bar',
        ];

        $dependency = new Dependency('foo');
        $dependency->addDependency('bar');
        $dependency->addDependency('bar');
        $dependency->addDependency('bar');

        $this->assertEquals($expected, $dependency->getDependencies());
        $this->assertTrue($dependency->hasDependency('bar'));
    }
}
