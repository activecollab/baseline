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

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->project_root = dirname(dirname(__DIR__));
    }

    /**
     * @dataProvider produceExpectedFilesForFinderInclusionTest
     * @param string $file_path_expected_to_find
     */
    public function testFinderInclusion(string $file_path_expected_to_find)
    {
        $config = $this->getFactory()->getConfig();

        $this->assertContains(
            $file_path_expected_to_find,
            $this->getFilesFoundByFinder($config)
        );
    }

    public function produceExpectedFilesForFinderInclusionTest()
    {
        return [
            [$this->project_root . '/src/CodeStyleFixer/ConfigFactoryInterface.php'],
            [$this->project_root . '/test/bootstrap.php'],
            [__FILE__],
        ];
    }

    /**
     * @dataProvider produceExpectedFilesForFinderExclusionTest
     * @param string $file_path_expected_to_find
     */
    public function testFinderExclusion(string $file_path_expected_to_find)
    {
        $config = $this->getFactory()->getConfig();

        $this->assertNotContains(
            $file_path_expected_to_find,
            $this->getFilesFoundByFinder($config)
        );
    }

    public function produceExpectedFilesForFinderExclusionTest()
    {
        return [
            [$this->project_root . '/.php_cs.cache'],
            [$this->project_root . '/.php_cs.php'],
            [$this->project_root . '/test/log/.gitignore'],
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
        return (new ConfigFactory(
            $project_root ?? $this->project_root,
            $dirs_to_scan,
            $project_name ?? 'Project',
            $project_author ?? 'Author',
            $project_contact_address ?? 'author@example.com'
        ));
    }

    private function getFilesFoundByFinder(ConfigInterface $config): array
    {
        return array_keys(iterator_to_array($config->getFinder()));
    }
}
