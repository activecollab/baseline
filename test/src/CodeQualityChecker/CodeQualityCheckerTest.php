<?php

/*
 * This file is part of the ActiveCollab Baseline project.
 *
 * (c) ActiveCollab, Inc <support@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Baseline\Test\CodeQualityChecker;

use ActiveCollab\Baseline\CodeQualityChecker\CodeQualityChecker;
use ActiveCollab\Baseline\CodeQualityChecker\QualityCheck\QualityCheckInterface;
use ActiveCollab\Baseline\CodeRepository\CodeRepositoryInterface;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CodeQualityCheckerTest extends TestCase
{
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Code quality can't be checked without quality checks
     */
    public function testWillThrowExceptionOnMissingCheckers()
    {
        /** @var CodeRepositoryInterface $code_repository */
        $code_repository = $this->createMock(CodeRepositoryInterface::class);

        new CodeQualityChecker($code_repository, null);
    }

    public function testWillCallEachQualityCheck()
    {
        /** @var MockObject|CodeRepositoryInterface $code_repository */
        $code_repository = $this->createMock(CodeRepositoryInterface::class);
        $code_repository
            ->expects($this->once())
            ->method('getChangedFiles')
            ->willReturn([]);

        /** @var MockObject|QualityCheckInterface $first_quality_check */
        $first_quality_check = $this->createMock(QualityCheckInterface::class);
        $first_quality_check
            ->expects($this->once())
            ->method('check');

        /** @var MockObject|QualityCheckInterface $second_quality_check */
        $second_quality_check = $this->createMock(QualityCheckInterface::class);
        $second_quality_check
            ->expects($this->once())
            ->method('check');

        (new CodeQualityChecker($code_repository, null, $first_quality_check, $second_quality_check))
            ->check();
    }

    public function testWillCommunicateFailure()
    {
        /** @var CodeRepositoryInterface $code_repository */
        $code_repository = $this->createMock(CodeRepositoryInterface::class);

        /** @var MockObject|QualityCheckInterface $quality_check */
        $quality_check = $this->createMock(QualityCheckInterface::class);

        $messages = [];

        $output_callback = function (string $message) use (&$messages) {
            $messages[] = $message;
        };

        (new CodeQualityChecker($code_repository, $output_callback, $quality_check))
            ->communicateFailure(
                new Exception('First message', 0, new Exception('Second message'))
            );

        $this->assertInternalType('array', $messages);
        $this->assertNotEmpty($messages);

        $first_message_found = false;
        $second_message_found = false;

        foreach ($messages as $message) {
            if (strpos($message, 'First message') !== false) {
                $first_message_found = true;
            } elseif (strpos($message, 'Second message') !== false) {
                $second_message_found = true;
            }
        }

        $this->assertTrue($first_message_found);
        $this->assertTrue($second_message_found);
    }
}
