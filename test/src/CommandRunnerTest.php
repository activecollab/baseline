<?php

/*
 * This file is part of the ActiveCollab Baseline project.
 *
 * (c) ActiveCollab, Inc <support@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Baseline\Test;

use ActiveCollab\Baseline\CommandRunner\CommandRunner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CommandRunnerTest extends TestCase
{
    public function testWillThrowExceptionOnUnsucessfulRun()
    {
        $this->expectExceptionMessage('Command not found');
        $this->expectException(ProcessFailedException::class);

        (new CommandRunner(__DIR__))->runCommand('exit 255');
    }

    public function testWillRunCommand()
    {
        $this->assertStringContainsString(
            basename(__FILE__),
            (new CommandRunner(__DIR__))->runCommand('ls')
        );
    }
}
