<?php

use PhpCsFixer\Config;

/**
 * Base PHP-CS-Fixer configuration
 * 
 * To use in your project, create a .php-cs-fixer.php file in your project root:
 * 
 * <?php
 * 
 * use PhpCsFixer\Finder;
 * 
 * $baseConfig = require __DIR__ . '/vendor/quboticlabs/php-cs-fixer/config/.php-cs-fixer.php';
 * 
 * $finder = Finder::create()
 *     ->in(__DIR__ . '/app')
 *     ->in(__DIR__ . '/domain')
 *     ->exclude('bootstrap')
 *     ->exclude('storage');
 * 
 * return $baseConfig->setFinder($finder);
 */

return (new Config())
    ->setRules([
        '@PSR2'                            => true,
        'blank_line_after_opening_tag'     => true,
        'blank_line_between_import_groups' => true,
        'braces'                           => ['allow_single_line_anonymous_class_with_empty_body' => true],
        'class_definition'                 => [
            'inline_constructor_arguments' => false,
            'space_before_parenthesis'     => true,
        ],
        'compact_nullable_typehint'          => true,
        'declare_equal_normalize'            => true,
        'lowercase_cast'                     => true,
        'lowercase_static_reference'         => true,
        'new_with_braces'                    => true,
        'no_blank_lines_after_class_opening' => true,
        'no_extra_blank_lines'               => true,
        'no_leading_import_slash'            => true,
        'no_unused_imports'                  => true,
        'no_whitespace_in_blank_line'        => true,
        'ordered_class_elements'             => [
            'order' => ['use_trait'],
        ],
        'ordered_imports' => [
            'imports_order'  => ['class', 'function', 'const'],
            'sort_algorithm' => 'length',
        ],
        'return_type_declaration'            => true,
        'short_scalar_cast'                  => true,
        'single_blank_line_before_namespace' => true,
        'single_import_per_statement'        => false,
        'single_quote'                       => true,
        'single_trait_insert_per_statement'  => true,
        'ternary_operator_spaces'            => true,
        'array_indentation'                  => true,
        'method_chaining_indentation'        => true,
        'class_attributes_separation'        => true,
        'concat_space'                       => ['spacing' => 'one'],
        'unary_operator_spaces'              => true,
        'binary_operator_spaces'             => [
            'operators' => [
                '=>' => 'align_single_space_minimal',
            ],
        ],
        'trailing_comma_in_multiline'       => true,
        'not_operator_with_space'           => false, // Prevents space before '!'
        'not_operator_with_successor_space' => true, // Ensures space after '!' (optional, common style)
        'no_spaces_inside_parenthesis'      => true, // Ensures no space after '(' or before ')'
        'single_line_comment_spacing'       => true,
        'blank_line_before_statement'       => ['statements' => ['return']],
        'phpdoc_indent'                     => true,
        'phpdoc_align'                      => [
            'tags'  => ['param', 'return', 'throws', 'var'], // Configure specific tags
            'align' => 'vertical', // This often causes the alignment issue you want to avoid
        ],
        'phpdoc_single_line_var_spacing' => true,
        'type_declaration_spaces'        => true,
    ])
    ->setIndent('    ')
    ->setLineEnding("\n");

