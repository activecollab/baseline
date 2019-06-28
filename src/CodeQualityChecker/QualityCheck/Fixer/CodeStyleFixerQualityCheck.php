<?php

/*
 * This file is part of the ActiveCollab Baseline project.
 *
 * (c) ActiveCollab, Inc <support@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Baseline\CodeQualityChecker\QualityCheck\Fixer;

use ActiveCollab\Baseline\CodeQualityChecker\FilePathMatcher\FilePathMatcherInterface;
use ActiveCollab\Baseline\CodeQualityChecker\FileSignatureResolver\FileSignatureResolverInterface;
use ActiveCollab\Baseline\CodeQualityChecker\QualityCheck\CheckException;
use ActiveCollab\Baseline\CodeQualityChecker\QualityCheck\QualityCheck;
use ActiveCollab\Baseline\CodeRepository\CodeRepositoryInterface;
use ActiveCollab\Baseline\CommandRunner\CommandRunnerInterface;
use Exception;

class CodeStyleFixerQualityCheck extends QualityCheck
{
    private $php_cs_fixer_binary;
    private $php_cs_fixer_config_file;

    public function __construct(
        CodeRepositoryInterface $repository,
        CommandRunnerInterface $command_runner,
        FileSignatureResolverInterface $file_signature_resolver,
        string $php_cs_fixer_binary = 'php-cs-fixer',
        string $php_cs_fixer_config_file = '.php_cs.php',
        callable $output_callback = null,
        FilePathMatcherInterface ...$file_path_matchers
    )
    {
        parent::__construct($repository, $command_runner, $file_signature_resolver, $output_callback, ...$file_path_matchers);

        $this->php_cs_fixer_binary = $php_cs_fixer_binary;
        $this->php_cs_fixer_config_file = $php_cs_fixer_config_file;
    }

    public function check(string $project_path, array $changed_files): void
    {
        $this->printToOutput('Running PHP Code Style Fixer...');
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
        $this->printToOutput(sprintf('    Fixing file %s...', $file_path));
        $command = sprintf(
            '%s --config=%s --verbose fix %s',
            $this->php_cs_fixer_binary,
            escapeshellarg($this->php_cs_fixer_config_file),
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
            throw new CheckException(
                sprintf('Failed to fix file %s. Run php-cs-fixer on your code.', $file_path),
                0,
                $e
            );
        }
    }
}
