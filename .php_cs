<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->exclude('build')
    ->in(__DIR__);

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        'concat_space' => ['spacing' => 'one'],
        'phpdoc_align' => false,
        'phpdoc_to_comment' => false,
        'header_comment' => false,
        'no_unused_imports' => true,
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ],
        'single_blank_line_before_namespace' => true,
        'no_extra_consecutive_blank_lines' => [
            'break',
            'continue',
            'extra',
            'return',
            'throw',
            'use',
            'parenthesis_brace_block',
            'square_brace_block',
            'curly_brace_block'
        ],
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'cast_spaces' => ['space' => 'single'],
        'binary_operator_spaces' => ['default' => 'single_space'],
        'no_superfluous_elseif' => false,
        'no_superfluous_phpdoc_tags' => false,
        'yoda_style' => false,
        'single_line_throw' => false,
    ])
    ->setFinder($finder);
