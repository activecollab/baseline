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
        return (new Config('psr2'))
            ->setFinder($this->getFinder())
            ->setRules(
                [
                    'header_comment' => [
                        'header' => $this->getHeader(),
                        'location' => 'after_open',
                    ],
                    'function_typehint_space' => true,
                    'method_argument_space' => true,
                    'no_trailing_whitespace' => true,
                    'no_whitespace_before_comma_in_array' => true,
                    'whitespace_after_comma_in_array' => true,
                    'no_multiline_whitespace_around_double_arrow' => true,
                    'hash_to_slash_comment' => true,
                    'include' => true,
                    'trailing_comma_in_multiline_array' => true,
                    'no_leading_namespace_whitespace' => true,
                    'no_blank_lines_after_phpdoc' => true,
                    'phpdoc_scalar' => true,
                    'phpdoc_summary' => true,
                    'no_trailing_comma_in_singleline_array' => true,
                    'single_blank_line_before_namespace' => true,
                    'space_after_semicolon' => true,
                    'no_singleline_whitespace_before_semicolons' => true,
                    'cast_spaces' => true,
                    'self_accessor' => false,
                    'standardize_not_equals' => true,
                    'ternary_operator_spaces' => true,
                    'trim_array_spaces' => true,
                    'no_unused_imports' => true,
                    'no_whitespace_in_blank_line' => true,
                    'ordered_imports' => true,
                    'array_syntax' => [
                        'syntax' => 'short',
                    ],
                    'phpdoc_align' => true,
                    'return_type_declaration' => true,
                    'single_quote' => true,
                    'phpdoc_separation' => false,
                    'phpdoc_no_package' => false,
                    'no_mixed_echo_print' => false,
                    'concat_space' => false,
                    'simplified_null_return' => false,
                    'single_blank_line_at_eof' => true,
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
