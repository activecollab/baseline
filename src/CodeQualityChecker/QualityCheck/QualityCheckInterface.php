<?php

/*
 * This file is part of the ActiveCollab Baseline project.
 *
 * (c) ActiveCollab, Inc <support@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Baseline\CodeQualityChecker\QualityCheck;

interface QualityCheckInterface
{
    public function check(string $project_path, array $changed_files): void;
}
