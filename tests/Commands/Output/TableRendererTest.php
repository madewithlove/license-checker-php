<?php

declare(strict_types=1);

namespace LicenseChecker\Tests\Commands\Output;

use LicenseChecker\Commands\Output\DependencyCheck;
use LicenseChecker\Commands\Output\TableRenderer;
use LicenseChecker\Dependency;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;

final class TableRendererTest extends TestCase
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

    #[Test]
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
            new DependencyCheck(new Dependency('foo', 'license')),
            new DependencyCheck(new Dependency('bar', 'license')),
        ];

        $this->tableRenderer->renderDependencyChecks(
            $dependencyChecks,
            $this->io
        );
    }

    #[Test]
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
            (new DependencyCheck(new Dependency('foo', 'license')))->addFailedDependency('baz', 'license')->addFailedDependency('baz2', 'license'),
            (new DependencyCheck(new Dependency('bar', 'license')))->addFailedDependency('baz', 'license'),
        ];

        $this->tableRenderer->renderDependencyChecks(
            $dependencyChecks,
            $this->io
        );
    }

    #[Test]
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
            new DependencyCheck(new Dependency('bar', 'license')),
            (new DependencyCheck(new Dependency('foo', 'license')))->addFailedDependency('baz', 'license'),
        ];

        $this->tableRenderer->renderDependencyChecks(
            $dependencyChecks,
            $this->io
        );
    }
}
