<?php

/*
 * This file is part of the ActiveCollab Baseline project.
 *
 * (c) ActiveCollab, Inc <support@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Baseline\CodeRepository;

interface CodeRepositoryInterface
{
    public function getRepositoryPath(): string;
    public function getFilePath(string $file_path): string;
    public function fileExists(string $file_path): bool;
    public function getChangedFiles(): iterable;
    public function stageFile(string $file_path);
}
