<?php

declare(strict_types=1);

namespace LicenseChecker\Tests;

use LicenseChecker\Dependency;
use PHPUnit\Framework\TestCase;

final class DependencyTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderNameWithLicense(): void
    {
        $dependency = new Dependency('foo', 'license');
        $this->assertEquals('foo [license]', $dependency->renderNameWithLicense());
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

        $dependency = new Dependency('foo', 'license');
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

        $dependency = new Dependency('foo', 'license');
        $dependency->addDependency('bar');
        $dependency->addDependency('bar');
        $dependency->addDependency('bar');

        $this->assertEquals($expected, $dependency->getDependencies());
        $this->assertTrue($dependency->hasDependency('bar'));
    }
}
