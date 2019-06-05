<?php

/*
 * This file is part of the ActiveCollab Baseline project.
 *
 * (c) ActiveCollab, Inc <support@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Baseline\Test\CodeRepository;

use ActiveCollab\Baseline\CodeRepository\CodeRepository;
use ActiveCollab\Baseline\CommandRunner\CommandRunnerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CommittedFilesResolverTest extends TestCase
{
    /**
     * @dataProvider provideDataForRepositoryPathTest
     * @param string $repository_path
     * @param string $expected_repository_path
     */
    public function testWillTrimTrailingSlashFromRepositoryPath(
        string $repository_path,
        string $expected_repository_path
    )
    {
        /** @var CommandRunnerInterface|MockObject $command_runner */
        $command_runner = $this->createMock(CommandRunnerInterface::class);

        $repo = new CodeRepository($repository_path, $command_runner);

        $this->assertSame($expected_repository_path, $repo->getRepositoryPath());
    }

    public function provideDataForRepositoryPathTest(): array
    {
        return [
            ['/tmp', '/tmp'],
            ['/tmp/', '/tmp'],
        ];
    }

    /**
     * @dataProvider provideDataForFilePathTest
     * @param string $repository_path
     * @param string $file_path
     * @param string $expected_file_path
     */
    public function testWillReturnFilePath(string $repository_path, string $file_path, string $expected_file_path)
    {
        /** @var CommandRunnerInterface|MockObject $command_runner */
        $command_runner = $this->createMock(CommandRunnerInterface::class);

        $repo = new CodeRepository($repository_path, $command_runner);

        $this->assertSame($expected_file_path, $repo->getFilePath($file_path));
    }

    public function provideDataForFilePathTest(): array
    {
        return [
            ['/tmp', 'info.php', '/tmp/info.php'],
            ['/tmp/', 'info.php', '/tmp/info.php'],
            ['/tmp/', '/info.php', '/tmp/info.php'],
        ];
    }

    /**
     * @dataProvider provideDataForFileExistsTest
     * @param string $file_path
     * @param bool   $expected_exists
     */
    public function testWillProperlyCheckIfFileExists(string $file_path, bool $expected_exists)
    {
        /** @var CommandRunnerInterface|MockObject $command_runner */
        $command_runner = $this->createMock(CommandRunnerInterface::class);

        $repo = new CodeRepository(
            dirname(dirname(__DIR__)) . '/fixtures/test-repo',
            $command_runner
        );

        $this->assertSame($expected_exists, $repo->fileExists($file_path));
    }

    public function provideDataForFileExistsTest(): array
    {
        return [
            ['existing-file.php', true],
            ['not-found.xyz', false],
        ];
    }

    public function testWillReturnEmtpyArrayOnEmptyList()
    {
        $command_output = implode("\n", ['']);

        /** @var CommandRunnerInterface|MockObject $command_runner */
        $command_runner = $this->createMock(CommandRunnerInterface::class);

        $command_runner
            ->expects($this->once())
            ->method('runCommand')
            ->willReturn($command_output);

        $repo = new CodeRepository(__DIR__, $command_runner);

        $committed_files = $repo->getChangedFiles();

        $this->assertInternalType('array', $committed_files);
        $this->assertEmpty($committed_files);
    }

    public function testWillGetCommittedFilesFromDiffCommandOutput()
    {
        $command_output = implode(
            "\n",
            [
                'A       src/CodeQualityChecker/CodeQualityChecker.php',
                'A       src/CodeRepository/GitRepository.php',
                'A       src/CommandRunner/CommandRunner.php',
                'A       src/CommandRunner/CommandRunnerInterface.php',
                'A       src/CommittedFilesResolver/CommittedFilesResolverInterface.php',
                'A       test/src/CommandRunnerTest.php',
                'A       test/src/GitCommittedFilesResolverTest.php',
            ]
        );

        /** @var CommandRunnerInterface|MockObject $command_runner */
        $command_runner = $this->createMock(CommandRunnerInterface::class);

        $command_runner
            ->expects($this->once())
            ->method('runCommand')
            ->willReturn($command_output);

        $repo = new CodeRepository(__DIR__, $command_runner);

        $committed_files = $repo->getChangedFiles();

        $this->assertContains('test/src/CommandRunnerTest.php', $committed_files);
        $this->assertNotContains(dirname(__DIR__) . '/bootstrap.php', $committed_files);
    }

    public function testStageFileWillCallAddCommand()
    {
        $file_to_add = 'file/to/add.php';

        /** @var CommandRunnerInterface|MockObject $command_runner */
        $command_runner = $this->createMock(CommandRunnerInterface::class);

        $command_runner
            ->expects($this->once())
            ->method('runCommand')
            ->with(sprintf('git add %s', escapeshellarg($file_to_add)));

        (new CodeRepository(__DIR__, $command_runner))
            ->stageFile($file_to_add);
    }
}
