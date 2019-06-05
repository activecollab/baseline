<?php

/*
 * This file is part of the ActiveCollab Baseline project.
 *
 * (c) ActiveCollab, Inc <support@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Baseline\CodeQualityChecker\FilePathMatcher;

interface FilePathMatcherInterface
{
    public function shouldCheck(string $file_path): bool;
}
