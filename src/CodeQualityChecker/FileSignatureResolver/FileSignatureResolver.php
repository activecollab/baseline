<?php

/*
 * This file is part of the ActiveCollab Baseline project.
 *
 * (c) ActiveCollab, Inc <support@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Baseline\CodeQualityChecker\FileSignatureResolver;

use RuntimeException;

class FileSignatureResolver implements FileSignatureResolverInterface
{
    public function getSignature(string $file_path): string
    {
        if (is_file($file_path)) {
            return md5_file($file_path);
        }

        throw new RuntimeException(sprintf('File %s not found.', $file_path));
    }
}
