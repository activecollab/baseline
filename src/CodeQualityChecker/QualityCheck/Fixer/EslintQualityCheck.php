<?php


namespace ActiveCollab\Baseline\CodeQualityChecker\QualityCheck\Fixer;


use ActiveCollab\Baseline\CodeQualityChecker\FilePathMatcher\FilePathMatcherInterface;
use ActiveCollab\Baseline\CodeQualityChecker\FileSignatureResolver\FileSignatureResolverInterface;
use ActiveCollab\Baseline\CodeQualityChecker\QualityCheck\CheckException;
use ActiveCollab\Baseline\CodeQualityChecker\QualityCheck\QualityCheck;
use ActiveCollab\Baseline\CodeRepository\CodeRepositoryInterface;
use ActiveCollab\Baseline\CommandRunner\CommandRunnerInterface;
use Exception;

class EslintQualityCheck extends QualityCheck
{

    private $eslint_binary;
    private $file_path_matchers;
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
        parent::__construct($repository, $command_runner, $file_signature_resolver, $output_callback);

        $this->eslint_binary = $eslint_binary;
        $this->eslint_config_file = $eslint_config_file;
        $this->file_path_matchers = $file_path_matchers;
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

    private function shouldFixFile(string $file_path): bool
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

    private function fixFile(string $file_path): void
    {
        $this->printToOutput(sprintf("\033[32m Eslint fix %s \033[0m", $file_path));

        try {
            $file_signature = $this->getFileSignature($file_path);
            $this->runCommand(
                $this->prepareEslintCommand(
                    $this->eslint_binary,
                    $this->eslint_config_file,
                    $file_path
                )
            );

            if ($file_signature != $this->getFileSignature($file_path)) {
                $this->printToOutput(sprintf('    File %s has been modified. Staging changes...', $file_path));
                $this->repository->stageFile($file_path);
            }
        } catch (Exception $e) {
            throw new CheckException(
                sprintf("\033[31m Failed to fix file %s. Run eslint on your code. \033[0m", $file_path),
                0,
                $e
            );
        }
    }

    private function prepareEslintCommand(
        string $eslint_binary,
        string $config,
        string $file_path
    ): string
    {
        return sprintf(
            '%s --config=%s --fix %s',
            $eslint_binary,
            escapeshellarg($config),
            escapeshellarg($file_path)
        );
    }
}
