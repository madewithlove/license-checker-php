<?php

namespace LicenseChecker\Tests\Commands\Output;

use LicenseChecker\Commands\Output\DependencyCheck;
use LicenseChecker\Commands\Output\TableRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;

class TableRendererTest extends TestCase
{
    /**
     * @var MockObject & SymfonyStyle
     */
    private MockObject $io;
    private TableRenderer $tableRenderer;

    protected function setUp(): void
    {
        $this->io = $this->createMock(SymfonyStyle::class);
        $this->tableRenderer = new TableRenderer();
    }

    /**
     * @test
     */
    public function itWillRenderTwoColumnsOnSuccess(): void
    {
        $this->io->expects($this->once())->method('table')->with(
            ['','dependency'],
            [
                ['<info>✓</info>', 'foo'],
                ['<info>✓</info>', 'bar'],
            ]
        );

        $dependencyChecks = [
            new DependencyCheck('foo'),
            new DependencyCheck('bar'),
        ];

        $this->tableRenderer->renderDependencyChecks(
            $dependencyChecks,
            $this->io
        );
    }

    /**
     * @test
     */
    public function itWillListCausingPackagesOnFailure(): void
    {
        $this->io->expects($this->once())->method('table')->with(
            ['', 'dependency', 'caused by'],
            [
                ['<fg=red>✗</>', 'foo', 'baz [license]'],
                ['', '', 'baz2 [license]'],
                ['<fg=red>✗</>', 'bar', 'baz [license]'],
            ]
        );

        $dependencyChecks = [
            (new DependencyCheck('foo'))->addFailedDependency('baz', 'license')->addFailedDependency('baz2', 'license'),
            (new DependencyCheck('bar'))->addFailedDependency('baz', 'license'),
        ];

        $this->tableRenderer->renderDependencyChecks(
            $dependencyChecks,
            $this->io
        );
    }

    /**
     * @test
     */
    public function itWillRenderFailingDependenciesFirst(): void
    {
        $this->io->expects($this->once())->method('table')->with(
            ['', 'dependency', 'caused by'],
            [
                ['<fg=red>✗</>', 'foo', 'baz [license]'],
                ['<info>✓</info>', 'bar', ''],
            ]
        );

        $dependencyChecks = [
            new DependencyCheck('bar'),
            (new DependencyCheck('foo'))->addFailedDependency('baz', 'license'),
        ];

        $this->tableRenderer->renderDependencyChecks(
            $dependencyChecks,
            $this->io
            );
    }
}
