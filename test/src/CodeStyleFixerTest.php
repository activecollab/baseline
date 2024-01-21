<?php

/*
 * This file is part of the ActiveCollab Baseline project.
 *
 * (c) ActiveCollab, Inc <support@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Baseline\Test;

use ActiveCollab\Baseline\CodeStyleFixer\ConfigFactory;
use PhpCsFixer\ConfigInterface;
use PHPUnit\Framework\TestCase;

class CodeStyleFixerTest extends TestCase
{
    private $project_root;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->project_root = dirname(__DIR__, 2);
    }

    /**
     * @dataProvider produceExpectedFilesForFinderInclusionTest
     */
    public function testFinderInclusion(string $file_path_expected_to_find): void
    {
        $config = $this->getFactory()->getConfig();

        $this->assertContains(
            $file_path_expected_to_find,
            $this->getFilesFoundByFinder($config)
        );
    }

    public static function produceExpectedFilesForFinderInclusionTest(): array
    {
        return [
            [dirname(__DIR__, 2) . '/src/CodeStyleFixer/ConfigFactoryInterface.php'],
            [dirname(__DIR__, 2) . '/test/bootstrap.php'],
            [__FILE__],
        ];
    }

    /**
     * @dataProvider produceExpectedFilesForFinderExclusionTest
     */
    public function testFinderExclusion(string $file_path_expected_to_find): void
    {
        $config = $this->getFactory()->getConfig();

        $this->assertNotContains(
            $file_path_expected_to_find,
            $this->getFilesFoundByFinder($config)
        );
    }

    public static function produceExpectedFilesForFinderExclusionTest(): array
    {
        return [
            [dirname(__DIR__, 2) . '/.php_cs.cache'],
            [dirname(__DIR__, 2) . '/.php_cs.php'],
            [dirname(__DIR__, 2) . '/test/log/.gitignore'],
        ];
    }

    public function testCustomLocation()
    {
        $config = $this->getFactory(
            null,
            [
                'test/fixtures/codestyle-fixer-scan-test',
            ]
        )->getConfig();

        $this->assertContains(
            $this->project_root . '/test/fixtures/codestyle-fixer-scan-test/file_to_find.php',
            $this->getFilesFoundByFinder($config)
        );
    }

    private function getFactory(
        string $project_root = null,
        array $dirs_to_scan = null,
        string $project_name = null,
        string $project_author = null,
        string $project_contact_address = null
    )
    {
        return new ConfigFactory(
            $project_root ?? $this->project_root,
            $dirs_to_scan,
            $project_name ?? 'Project',
            $project_author ?? 'Author',
            $project_contact_address ?? 'author@example.com'
        );
    }

    private function getFilesFoundByFinder(ConfigInterface $config): array
    {
        return array_keys(iterator_to_array($config->getFinder()));
    }
}
