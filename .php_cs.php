<?php

/*
 * This file is part of the ActiveCollab Baseline project.
 *
 * (c) ActiveCollab, Inc <support@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

require_once 'vendor/autoload.php';

use ActiveCollab\Baseline\CodeStyleFixer\ConfigFactory;

$code_style = new ConfigFactory(
    __DIR__,
    [
        'src',
        'test',
    ],
    'ActiveCollab Baseline',
    'ActiveCollab, Inc',
    'support@activecollab.com'
);

return $code_style->getConfig();
