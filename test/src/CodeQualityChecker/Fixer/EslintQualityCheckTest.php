<?php


namespace ActiveCollab\Baseline\Test\CodeQualityChecker\Fixer;


use ActiveCollab\Baseline\CodeQualityChecker\FilePathMatcher\FilePathMatcher;
use ActiveCollab\Baseline\CodeQualityChecker\FileSignatureResolver\FileSignatureResolverInterface;
use ActiveCollab\Baseline\CodeQualityChecker\QualityCheck\Fixer\EslintQualityCheck;
use ActiveCollab\Baseline\CodeRepository\CodeRepositoryInterface;
use ActiveCollab\Baseline\CommandRunner\CommandRunnerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;

class EslintQualityCheckTest extends TestCase
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

        $check = new EslintQualityCheck(
            $repository,
            $command_runner,
            $file_signature_resolver,
            'eslint',
            'test/fixtures/test-eslint/some-file.js',
            null,
            new FilePathMatcher('test/fixtures/test-eslint', 'jsx')
        );

        $check->check(
            __DIR__,
            [
                'test/fixtures/test-eslint/index.html',
                'test/fixtures/test-eslint/react.jsx',
                'test/fixtures/test-eslint/some-unknown.jsx',
                'test/fixtures/test-eslint/some-file.jsx',
                'test/fixtures/test-eslint/some-file.js',
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

        $check = new EslintQualityCheck(
            $repository,
            $command_runner,
            $file_signature_resolver,
            'eslint',
            'test/fixtures/test-eslint/some-file.js',
            null,
            new FilePathMatcher('test/fixtures/test-eslint', 'jsx')
        );


        $check->check(
            __DIR__,
            [
                'test/fixtures/test-eslint/index.html',
                'test/fixtures/test-eslint/react.jsx',
                'test/fixtures/test-eslint/some-unknown.jsx',
                'test/fixtures/test-eslint/some-file.jsx',
                'test/fixtures/test-eslint/some-file.js',
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
            ->with('src/index.js');

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

        $check = new EslintQualityCheck(
            $repository,
            $command_runner,
            $file_signature_resolver,
            'eslint',
            'test/fixtures/test-eslint/some-file.js',
            $output_callback,
            new FilePathMatcher('src', 'js')
        );

        $check->check(
            __DIR__,
            [
                'src/index.js',
            ]
        );
        $this->assertInternalType('array', $messages);
        $this->assertNotEmpty($messages);
    }

    /**
     * @expectedException \PHPUnit\Framework\Error\Warning
     * @expectedExceptionMessage src/index.js
     */
    public function testWillThrowWarningMessage()
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

        $output_callback = function (string $message) use (&$messages) {
            $messages[] = $message;
        };

        $check = new EslintQualityCheck(
            $repository,
            $command_runner,
            $file_signature_resolver,
            'eslint',
            'test/fixtures/test-eslint/some-file.js',
            $output_callback,
            new FilePathMatcher('src', 'js')
        );

        $check->check(
            __DIR__,
            [
                'src/index.js',
            ]
        );
    }
}
