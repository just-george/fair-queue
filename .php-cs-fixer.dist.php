<?php

$finder = new PhpCsFixer\Finder();
$finder
    ->in(__DIR__)
    ->exclude('_generated')
;

$cacheFilename = sprintf(
    '%s%s%s',
    sys_get_temp_dir(),
    DIRECTORY_SEPARATOR,
    '.php-cs-fixer.cache',
);
$config = new PhpCsFixer\Config();
$config
    ->setCacheFile($cacheFilename)
    ->setRiskyAllowed(true)
    ->setRules([
        //Symfony rules are the base
        '@Symfony' => true,
        'yoda_style' => false,
        'nullable_type_declaration' => [
            'syntax' => 'union',
        ],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => null,
            'import_functions' => null,
        ],
        'declare_strict_types' => true,
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters']
        ],
        'concat_space' => [
            'spacing' => 'one'
        ],
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'new_line_for_chained_calls',
        ],
        'method_chaining_indentation' => true,
        'void_return' => true,
        'fully_qualified_strict_types' => [
            'import_symbols' => true,
        ],
    ])
    ->setFinder($finder)
;

return $config;
