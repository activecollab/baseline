<?php

/*
 * This file is part of the ActiveCollab Baseline project.
 *
 * (c) ActiveCollab, Inc <support@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Baseline\CodeQualityChecker\FileSignatureResolver;

interface FileSignatureResolverInterface
{
    public function getSignature(string $file_path): string;
}
