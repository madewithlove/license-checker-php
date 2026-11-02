<?php

declare(strict_types=1);

namespace LicenseChecker\Tests\Commands;

use LicenseChecker\Commands\CheckLicenses;
use LicenseChecker\Commands\Output\TableRenderer;
use LicenseChecker\Composer\ComposerJsonLineMapper;
use LicenseChecker\Composer\DependencyTree;
use LicenseChecker\Composer\DependencyTreeRetriever;
use LicenseChecker\Composer\UsedLicensesParser;
use LicenseChecker\Composer\UsedLicensesRetriever;
use LicenseChecker\Configuration\LicenseConfigurationParser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class CheckLicensesTest extends TestCase
{
    /**
     * @param array<string, string> $licenses
     */
    private function commandTester(array $licenses, string $dependencyTree): CommandTester
    {
        $licensesRetriever = $this->createStub(UsedLicensesRetriever::class);
        $licensesRetriever->method('getComposerLicenses')->willReturn([
            'dependencies' => array_map(
                fn (string $license): array => ['version' => '1.0.0', 'license' => [$license]],
                $licenses,
            ),
        ]);

        $treeRetriever = $this->createStub(DependencyTreeRetriever::class);
        $treeRetriever->method('getDependencyTree')->willReturn($dependencyTree);

        $usedLicensesParser = new UsedLicensesParser($licensesRetriever);

        return new CommandTester(new CheckLicenses(
            $usedLicensesParser,
            new LicenseConfigurationParser(__DIR__ . '/data'),
            new DependencyTree($treeRetriever, $usedLicensesParser),
            new TableRenderer(),
            new ComposerJsonLineMapper(__DIR__ . '/data'),
        ));
    }

    #[Test]
    public function itPassesWhenTheOnlyViolatingPackageIsExempt(): void
    {
        $tester = $this->commandTester(
            ['livewire/flux-pro' => 'proprietary', 'livewire/livewire' => 'MIT'],
            (string) json_encode(['installed' => [
                ['name' => 'livewire/flux-pro', 'requires' => [['name' => 'livewire/livewire']]],
            ]]),
        );

        $this->assertSame(Command::SUCCESS, $tester->execute([]));
    }

    #[Test]
    public function itFailsWithoutTheExemption(): void
    {
        $tester = $this->commandTester(
            ['livewire/flux-pro' => 'proprietary'],
            (string) json_encode(['installed' => [['name' => 'livewire/flux-pro']]]),
        );

        $this->assertSame(Command::FAILURE, $tester->execute(['--filename' => '.license-checker-no-exempt.yml']));
    }

    #[Test]
    public function itStillFailsForOtherPackagesWithTheSameLicense(): void
    {
        $tester = $this->commandTester(
            ['livewire/flux-pro' => 'proprietary', 'acme/closed' => 'proprietary'],
            (string) json_encode(['installed' => [
                ['name' => 'livewire/flux-pro'],
                ['name' => 'acme/closed'],
            ]]),
        );

        $this->assertSame(Command::FAILURE, $tester->execute([]));
        $this->assertStringContainsString('acme/closed', $tester->getDisplay());
    }
}
