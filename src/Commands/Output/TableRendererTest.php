<?php

namespace LicenseChecker\Commands\Output;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;

class TableRendererTest extends TestCase
{
    /**
     * @var SymfonyStyle | MockObject
     */
    private $io;

    /**
     * @var TableRenderer
     */
    private $tableRenderer;

    protected function setUp(): void
    {
        $this->io = $this->createMock(SymfonyStyle::class);
        $this->tableRenderer = new TableRenderer();
    }

    /**
     * @test
     */
    public function itWillRenderTwoColumnsOnSuccess()
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
            $this->io,
        );
    }
}
