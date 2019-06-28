<?php

/*
 * This file is part of the ActiveCollab Baseline project.
 *
 * (c) ActiveCollab, Inc <support@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Baseline\CodeQualityChecker\QualityCheck;

use ActiveCollab\Baseline\CodeQualityChecker\FilePathMatcher\FilePathMatcherInterface;
use ActiveCollab\Baseline\CodeQualityChecker\FileSignatureResolver\FileSignatureResolverInterface;
use ActiveCollab\Baseline\CodeRepository\CodeRepositoryInterface;
use ActiveCollab\Baseline\CommandRunner\CommandRunnerInterface;

abstract class QualityCheck implements QualityCheckInterface
{
    protected $repository;
    private $command_runner;
    private $file_signature_resolver;
    private $output_callback;
    private $file_path_matchers;

    public function __construct(
        CodeRepositoryInterface $repository,
        CommandRunnerInterface $command_runner,
        FileSignatureResolverInterface $file_signature_resolver,
        callable $output_callback = null,
        FilePathMatcherInterface ...$file_path_matchers
    )
    {
        $this->repository = $repository;
        $this->command_runner = $command_runner;
        $this->file_signature_resolver = $file_signature_resolver;
        $this->output_callback = $output_callback;
        $this->file_path_matchers = $file_path_matchers;
    }

    protected function runCommand(string $command, string $working_directory = null)
    {
        $this->command_runner->runCommand(
            $command,
            $working_directory ?? $this->repository->getRepositoryPath()
        );
    }

    protected function getFileSignature(string $file_path): string
    {
        return $this->file_signature_resolver->getSignature($this->repository->getFilePath($file_path));
    }

    protected function printToOutput(string $message): void
    {
        if ($this->output_callback) {
            call_user_func($this->output_callback, "{$message}\n");
        }
    }

    protected function shouldFixFile(string $file_path): bool
    {
        if (!$this->repository->fileExists($file_path)) {
            return false;
        }

        foreach ($this->file_path_matchers as $file_path_matcher) {
            if ($file_path_matcher->shouldCheck($file_path)) {
                return true;
            }
        }

        return false;
    }
}
