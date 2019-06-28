<?php

/*
 * This file is part of the ActiveCollab Baseline project.
 *
 * (c) ActiveCollab, Inc <support@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Baseline\CodeQualityChecker\QualityCheck\Fixer;

use ActiveCollab\Baseline\CodeQualityChecker\FilePathMatcher\FilePathMatcherInterface;
use ActiveCollab\Baseline\CodeQualityChecker\FileSignatureResolver\FileSignatureResolverInterface;
use ActiveCollab\Baseline\CodeQualityChecker\QualityCheck\QualityCheck;
use ActiveCollab\Baseline\CodeRepository\CodeRepositoryInterface;
use ActiveCollab\Baseline\CommandRunner\CommandRunnerInterface;
use Exception;

class EslintQualityCheck extends QualityCheck
{
    private $eslint_binary;
    private $eslint_config_file;

    public function __construct(
        CodeRepositoryInterface $repository,
        CommandRunnerInterface $command_runner,
        FileSignatureResolverInterface $file_signature_resolver,
        string $eslint_binary = 'eslint',
        string $eslint_config_file = '.eslintrc',
        callable $output_callback = null,
        FilePathMatcherInterface ...$file_path_matchers
    )
    {
        parent::__construct($repository, $command_runner, $file_signature_resolver, $output_callback, ...$file_path_matchers);

        $this->eslint_binary = $eslint_binary;
        $this->eslint_config_file = $eslint_config_file;
    }

    public function check(string $project_path, array $changed_files): void
    {
        $this->printToOutput('Running Eslint Code Style Fixer...');
        $this->printToOutput('');

        foreach ($changed_files as $changed_file) {
            if ($this->shouldFixFile($changed_file)) {
                $this->fixFile($changed_file);
            }
        }

        $this->printToOutput('');
    }

    private function fixFile(string $file_path): void
    {
        $this->printToOutput(sprintf("\033[32m Eslint fix %s \033[0m", $file_path));
        $command = sprintf(
            '%s --config=%s --fix %s',
            $this->eslint_binary,
            escapeshellarg($this->eslint_config_file),
            escapeshellarg($file_path)
        );
        try {
            $file_signature = $this->getFileSignature($file_path);
            $this->runCommand($command);

            if ($file_signature != $this->getFileSignature($file_path)) {
                $this->printToOutput(sprintf('    File %s has been modified. Staging changes...', $file_path));
                $this->repository->stageFile($file_path);
            }
        } catch (Exception $e) {
            $this->repository->stageFile($file_path);
            trigger_error(
                sprintf(
                    "\033[31m Failed to fix file %s. Run eslint on your code. %s \033[0m",
                    $file_path,
                    $e->getMessage()
                ),
                E_USER_WARNING
            );
        }
    }
}
