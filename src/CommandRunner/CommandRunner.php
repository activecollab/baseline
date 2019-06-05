<?php

/*
 * This file is part of the ActiveCollab Baseline project.
 *
 * (c) ActiveCollab, Inc <support@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Baseline\CommandRunner;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CommandRunner implements CommandRunnerInterface
{
    private $default_working_directory;

    public function __construct(string $default_working_directory = null)
    {
        $this->default_working_directory = $default_working_directory ?? getcwd();
    }

    public function runCommand(string $command, string $working_directory = null): string
    {
        $process = new Process($command, $working_directory ?? $this->default_working_directory);
        $process->enableOutput();
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }
}
