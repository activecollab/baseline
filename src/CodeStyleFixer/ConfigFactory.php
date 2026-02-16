<?php

/*
 * This file is part of the ActiveCollab Baseline project.
 *
 * (c) ActiveCollab, Inc <support@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Baseline\CodeStyleFixer;

use PhpCsFixer\Config;
use PhpCsFixer\ConfigInterface;
use PhpCsFixer\Finder;
use Traversable;

class ConfigFactory implements ConfigFactoryInterface
{
    private $project_root;
    private $project_name;
    private $project_author;
    private $project_contact_address;
    /**
     * @var array
     */
    private $dirs_to_scan;

    public function __construct(
        string $project_root,
        ?array $dirs_to_scan,
        string $project_name,
        string $project_author,
        string $project_contact_address
    )
    {
        $this->project_name = $project_name;
        $this->dirs_to_scan = $dirs_to_scan ?? ['src', 'test'];
        $this->project_author = $project_author;
        $this->project_contact_address = $project_contact_address;
        $this->project_root = $project_root;
    }

    public function getConfig(): ConfigInterface
    {
        return (new Config())
            ->setFinder($this->getFinder())
            ->setRules(
                [
                    'header_comment' => [
                        'header' => $this->getHeader(),
                        'location' => 'after_open',
                    ],
                    '@PER-CS3x0' => true,
                ]
            );
    }

    private function getFinder(): Traversable
    {
        return (new Finder())
            ->in(
                array_map(
                    function (string $dir_to_scan) {
                        return $this->project_root . '/' . $dir_to_scan;
                    },
                    $this->dirs_to_scan
                )
            )
            ->exclude($this->project_root . '/vendor')
            ->name('*.php');
    }

    private function getHeader(): string
    {
        return implode(
            "\n",
            [
                sprintf(
                    'This file is part of the %s project.',
                    $this->project_name
                ),
                '',
                sprintf(
                    '(c) %s <%s>. All rights reserved.',
                    $this->project_author,
                    $this->project_contact_address
                ),
            ]
        );
    }
}
