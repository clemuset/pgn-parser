<?php

$rules = [
    '@PSR12' => true,
    '@Symfony' => true,
    'array_syntax' => ['syntax' => 'short'],
    'ordered_imports' => true,
    'no_unused_imports' => true,
    'single_quote' => true,
    'binary_operator_spaces' => ['default' => 'single_space'],
    'blank_line_before_statement' => [
        'statements' => ['return', 'throw', 'try', 'if'],
    ],
    'phpdoc_trim' => true,
    'phpdoc_align' => ['align' => 'left'],
    'no_trailing_whitespace' => true,
    'no_trailing_whitespace_in_comment' => true,
    'no_whitespace_in_blank_line' => true,
    'trailing_comma_in_multiline' => ['elements' => ['arrays']],
    'concat_space' => ['spacing' => 'one'],
    'yoda_style' => true,
];

return new PhpCsFixer\Config()
    ->setRiskyAllowed(true)
    ->setRules($rules)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__ . '/src')
            ->in(__DIR__ . '/tests')
            ->name('*.php')
            ->ignoreVCS(true)
            ->ignoreDotFiles(false)
    );
