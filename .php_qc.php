<?php

/*
 * This file is part of the ActiveCollab Baseline project.
 *
 * (c) ActiveCollab, Inc <support@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

require_once 'vendor/autoload.php';

use ActiveCollab\Baseline\CodeQualityChecker\CodeQualityChecker;
use ActiveCollab\Baseline\CodeQualityChecker\FilePathMatcher\FilePathMatcher;
use ActiveCollab\Baseline\CodeQualityChecker\FileSignatureResolver\FileSignatureResolver;
use ActiveCollab\Baseline\CodeQualityChecker\QualityCheck\Fixer\CodeStyleFixerQualityCheck;
use ActiveCollab\Baseline\CodeRepository\CodeRepository;
use ActiveCollab\Baseline\CommandRunner\CommandRunner;

$command_runner = new CommandRunner(__DIR__);
$code_repository = new CodeRepository(__DIR__, $command_runner);
$file_signature_resolver = new FileSignatureResolver();
$output_callback = function (string $message) {
    print $message;
};

return new CodeQualityChecker(
    $code_repository,
    $output_callback,
    new CodeStyleFixerQualityCheck(
        $code_repository,
        $command_runner,
        $file_signature_resolver,
        'php vendor/bin/php-cs-fixer',
        '.php_cs.php',
        $output_callback,
        new FilePathMatcher('src', 'php'),
        new FilePathMatcher('test/src', 'php')
    )
);
