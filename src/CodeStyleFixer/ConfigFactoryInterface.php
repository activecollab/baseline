<?php

/*
 * This file is part of the ActiveCollab Baseline project.
 *
 * (c) ActiveCollab, Inc <support@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Baseline\CodeStyleFixer;

use PhpCsFixer\ConfigInterface;

interface ConfigFactoryInterface
{
    public function getConfig(): ConfigInterface;
}
