<?php

/*
 * This file is part of the ActiveCollab Baseline project.
 *
 * (c) ActiveCollab, Inc <support@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Baseline\CodeQualityChecker\FilePathMatcher;

class FilePathMatcher implements FilePathMatcherInterface
{
    private $relative_dir_path;
    private $file_extension;

    public function __construct(string $relative_dir_path, string $file_extension)
    {
        $this->relative_dir_path = rtrim(ltrim($relative_dir_path, '/'), '/') . '/';
        $this->file_extension = $file_extension;

        if (substr($file_extension, 0, 1) != '.') {
            $file_extension = '.' . $file_extension;
        }

        $this->file_extension = $file_extension;
    }

    public function shouldCheck(string $file_path): bool
    {
        return $this->stringStartsWith($file_path, $this->relative_dir_path)
            && $this->stringEndsWith($file_path, $this->file_extension);
    }

    private function stringStartsWith(string $string, string $prefix): bool
    {
        return strpos($string, $prefix) === 0;
    }

    private function stringEndsWith(string $string, string $suffix): bool
    {
        return substr($string, 0 - strlen($suffix)) == $suffix;
    }
}
