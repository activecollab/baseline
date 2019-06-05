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

class CommandRunnerTest extends TestCase
{
    /**
     * @expectedException \Symfony\Component\Process\Exception\ProcessFailedException
     * @expectedExceptionMessage The command "exit 255" failed
     */
    public function testWillThrowExceptionOnUnsucessfulRun()
    {
        (new CommandRunner(__DIR__))->runCommand('exit 255');
    }

    public function testWillRunCommand()
    {
        $this->assertContains(
            basename(__FILE__),
            (new CommandRunner(__DIR__))->runCommand('ls')
        );
    }
}
