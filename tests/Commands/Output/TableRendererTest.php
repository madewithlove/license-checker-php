<?php

declare(strict_types=1);

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
                ['<info>✓</info>', 'foo [license]'],
                ['<info>✓</info>', 'bar [license]'],
            ]
        );

        $dependencyChecks = [
            new DependencyCheck('foo', 'license'),
            new DependencyCheck('bar', 'license'),
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
                ['<fg=red>✗</>', 'foo [license]', 'baz [license]'],
                ['', '', 'baz2 [license]'],
                ['<fg=red>✗</>', 'bar [license]', 'baz [license]'],
            ]
        );

        $dependencyChecks = [
            (new DependencyCheck('foo', 'license'))->addFailedDependency('baz', 'license')->addFailedDependency('baz2', 'license'),
            (new DependencyCheck('bar', 'license'))->addFailedDependency('baz', 'license'),
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
                ['<fg=red>✗</>', 'foo [license]', 'baz [license]'],
                ['<info>✓</info>', 'bar [license]', ''],
            ]
        );

        $dependencyChecks = [
            new DependencyCheck('bar', 'license'),
            (new DependencyCheck('foo', 'license'))->addFailedDependency('baz', 'license'),
        ];

        $this->tableRenderer->renderDependencyChecks(
            $dependencyChecks,
            $this->io
        );
    }
}
