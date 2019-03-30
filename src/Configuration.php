<?php

/**
 * @author Pawel Maslak <pawel@maslak.it>
 */

namespace pmaslak\PhpObfuscator;


class Configuration
{
    /**
     * //option => description
     * @var array
     */
    private static $options = [
        'no-strip-indentation' => 'multi line output',
        'strip-indentation' => 'single line output',
        'no-shuffle-statements' => 'do not shuffle statements',
        'shuffle-statements' => 'shuffle statements',
        'no-obfuscate-string-literal' => 'do not obfuscate string literals',
        'obfuscate-string-literal' => 'obfuscate string literals',
        'no-obfuscate-loop-statement' => 'do not obfuscate loop statements',
        'obfuscate-loop-statement' => 'obfuscate loop statements',
        'no-obfuscate-if-statement' => 'do not obfuscate if statements',
        'obfuscate-if-statement' => 'obfuscate if statements',
        'no-obfuscate-constant-name' => 'do not obfuscate constant names',
        'obfuscate-constant-name' => 'obfuscate constant names',
        'no-obfuscate-variable-name' => 'do not obfuscate variable names',
        'obfuscate-variable-name' => 'obfuscate variable names',
        'no-obfuscate-function-name' => 'do not obfuscate function names',
        'obfuscate-function-name' => 'obfuscate function names',
        'no-obfuscate-class_constant-name' => 'do not obfuscate class constant names',
        'obfuscate-class_constant-name' => 'obfuscate class constant names',
        'no-obfuscate-class-name' => 'do not obfuscate class names',
        'obfuscate-class-name' => 'obfuscate class names',
        'no-obfuscate-interface-name' => 'do not obfuscate interface names',
        'obfuscate-interface-name' => 'obfuscate interface names',
        'no-obfuscate-trait-name' => 'do not obfuscate trait names',
        'obfuscate-trait-name' => 'obfuscate trait names',
        'no-obfuscate-property-name' => 'do not obfuscate property names',
        'obfuscate-property-name' => 'obfuscate property names',
        'no-obfuscate-method-name' => 'do not obfuscate method names',
        'obfuscate-method-name' => 'obfuscate method names',
        'no-obfuscate-namespace-name' => 'do not obfuscate namespace names',
        'obfuscate-namespace-name' => ' obfuscate namespace names',
        'no-obfuscate-label-name' => 'do not obfuscate label names',
        'obfuscate-label-name' => 'obfuscate label names',
        'scramble-mode' => 'force scramble mode',
        'scramble-length'
    ];

    public static function getAvailableOptions(): array
    {
        return self::$options;
    }

    public static function getFilteredOptions(array $options): array
    {
        $options = array_filter($options, 'strlen');
        $result = [];

        foreach ($options as $key) {
            if (isset(self::$options[$key])) {
                $result[] = $key;
            }
        }

        return $result;
    }

}
