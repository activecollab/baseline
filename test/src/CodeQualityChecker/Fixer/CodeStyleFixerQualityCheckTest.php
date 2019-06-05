<?php

/*
 * This file is part of the ActiveCollab Baseline project.
 *
 * (c) ActiveCollab, Inc <support@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Baseline\Test\CodeQualityChecker\Fixer;

use ActiveCollab\Baseline\CodeQualityChecker\FilePathMatcher\FilePathMatcher;
use ActiveCollab\Baseline\CodeQualityChecker\FileSignatureResolver\FileSignatureResolverInterface;
use ActiveCollab\Baseline\CodeQualityChecker\QualityCheck\Fixer\CodeStyleFixerQualityCheck;
use ActiveCollab\Baseline\CodeRepository\CodeRepositoryInterface;
use ActiveCollab\Baseline\CommandRunner\CommandRunnerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CodeStyleFixerQualityCheckTest extends TestCase
{
    public function testWillRunCommandForEachMatchingFile()
    {
        /** @var MockObject|CommandRunnerInterface $command_runner */
        $command_runner = $this->createMock(CommandRunnerInterface::class);
        $command_runner
            ->expects($this->exactly(3))
            ->method('runCommand')
            ->willReturn('');

        /** @var MockObject|CodeRepositoryInterface $repository */
        $repository = $this->createMock(CodeRepositoryInterface::class);
        $repository
            ->expects($this->atLeast(3))
            ->method('fileExists')
            ->willReturn(true);

        /** @var MockObject|FileSignatureResolverInterface $file_signature_resolver */
        $file_signature_resolver = $this->createMock(FileSignatureResolverInterface::class);
        $file_signature_resolver
            ->expects($this->atLeast(3))
            ->method('getSignature')
            ->willReturn('file-sig');

        $check = new CodeStyleFixerQualityCheck(
            $repository,
            $command_runner,
            $file_signature_resolver,
            'php-cs-fixer',
            '.php_cs.php',
            null,
            new FilePathMatcher('src', 'php'),
            new FilePathMatcher('test/src', 'php')
        );

        $check->check(
            __DIR__,
            [
                'src/index.html',
                'src/stdClass.php',
                'src/Authentication.php',
                'test/bootstrap.php',
                'test/src/Authentication.php',
            ]
        );
    }

    public function testWillSkipMissingFiles()
    {
        /** @var MockObject|CommandRunnerInterface $command_runner */
        $command_runner = $this->createMock(CommandRunnerInterface::class);
        $command_runner
            ->expects($this->never())
            ->method('runCommand');

        /** @var MockObject|CodeRepositoryInterface $repository */
        $repository = $this->createMock(CodeRepositoryInterface::class);
        $repository
            ->expects($this->atLeast(3))
            ->method('fileExists')
            ->willReturn(false);

        /** @var MockObject|FileSignatureResolverInterface $file_signature_resolver */
        $file_signature_resolver = $this->createMock(FileSignatureResolverInterface::class);
        $file_signature_resolver
            ->expects($this->never())
            ->method('getSignature');

        $check = new CodeStyleFixerQualityCheck(
            $repository,
            $command_runner,
            $file_signature_resolver,
            'php-cs-fixer',
            '.php_cs.php',
            null,
            new FilePathMatcher('src', 'php'),
            new FilePathMatcher('test/src', 'php')
        );

        $check->check(
            __DIR__,
            [
                'src/index.html',
                'src/stdClass.php',
                'src/Authentication.php',
                'test/bootstrap.php',
                'test/src/Authentication.php',
            ]
        );
    }

    public function testWillStageFileWhenModified()
    {
        /** @var MockObject|CommandRunnerInterface $command_runner */
        $command_runner = $this->createMock(CommandRunnerInterface::class);
        $command_runner
            ->expects($this->once())
            ->method('runCommand')
            ->willReturn('');

        /** @var MockObject|CodeRepositoryInterface $repository */
        $repository = $this->createMock(CodeRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('fileExists')
            ->willReturn(true);

        $repository
            ->expects($this->once())
            ->method('stageFile')
            ->with('src/stdClass.php');

        /** @var MockObject|FileSignatureResolverInterface $file_signature_resolver */
        $file_signature_resolver = $this->createMock(FileSignatureResolverInterface::class);
        $file_signature_resolver
            ->expects($this->exactly(2))
            ->method('getSignature')
            ->willReturnOnConsecutiveCalls('first', 'second');

        $messages = [];

        $output_callback = function (string $message) use (&$messages) {
            $messages[] = $message;
        };

        $check = new CodeStyleFixerQualityCheck(
            $repository,
            $command_runner,
            $file_signature_resolver,
            'php-cs-fixer',
            '.php_cs.php',
            $output_callback,
            new FilePathMatcher('src', 'php')
        );

        $check->check(
            __DIR__,
            [
                'src/stdClass.php',
            ]
        );

        $this->assertInternalType('array', $messages);
        $this->assertNotEmpty($messages);
    }

    /**
     * @expectedException \ActiveCollab\Baseline\CodeQualityChecker\QualityCheck\CheckException
     * @expectedExceptionMessage src/stdClass.php
     */
    public function testWillPropagateFailureException()
    {
        /** @var MockObject|ProcessFailedException $process_failed_exception */
        $process_failed_exception = $this->createMock(ProcessFailedException::class);

        /** @var MockObject|CommandRunnerInterface $command_runner */
        $command_runner = $this->createMock(CommandRunnerInterface::class);
        $command_runner
            ->expects($this->once())
            ->method('runCommand')
            ->willThrowException($process_failed_exception);

        /** @var MockObject|CodeRepositoryInterface $repository */
        $repository = $this->createMock(CodeRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('fileExists')
            ->willReturn(true);

        /** @var MockObject|FileSignatureResolverInterface $file_signature_resolver */
        $file_signature_resolver = $this->createMock(FileSignatureResolverInterface::class);
        $file_signature_resolver
            ->expects($this->once())
            ->method('getSignature')
            ->willReturn('file-sig');

        $check = new CodeStyleFixerQualityCheck(
            $repository,
            $command_runner,
            $file_signature_resolver,
            'php-cs-fixer',
            '.php_cs.php',
            null,
            new FilePathMatcher('src', 'php'),
            new FilePathMatcher('test/src', 'php')
        );

        $check->check(
            __DIR__,
            [
                'src/stdClass.php',
            ]
        );
    }
}
