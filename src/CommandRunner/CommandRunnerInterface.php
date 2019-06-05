<?php

/*
 * This file is part of the ActiveCollab Baseline project.
 *
 * (c) ActiveCollab, Inc <support@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Baseline\CommandRunner;

interface CommandRunnerInterface
{
    public function runCommand(string $command, string $working_directory = null): string;
}
