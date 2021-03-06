<?php

/*
 * This file is part of the ActiveCollab Baseline project.
 *
 * (c) ActiveCollab, Inc <support@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Baseline\Test;

use ActiveCollab\Baseline\CodeQualityChecker\FileSignatureResolver\FileSignatureResolver;
use PHPUnit\Framework\TestCase;

class FileSignatureResolverTest extends TestCase
{
    public function testWillReturnMd5Hash()
    {
        $file_to_check = dirname(__DIR__) . '/fixtures/codestyle-fixer-scan-test/file_to_find.php';

        $this->assertSame(md5_file($file_to_check), (new FileSignatureResolver())->getSignature($file_to_check));
    }

    public function testWillThrowExceptionOnMissingFile()
    {
        $this->expectExceptionMessage('not found');
        $this->expectException(\RuntimeException::class);
        $file_to_check = dirname(__DIR__) . '/fixtures/not-found.php';

        $this->assertFileDoesNotExist($file_to_check);

        (new FileSignatureResolver())->getSignature($file_to_check);
    }
}
